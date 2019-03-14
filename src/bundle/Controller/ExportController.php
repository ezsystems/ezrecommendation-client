<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Exception\ExportInProgressException;
use EzSystems\EzRecommendationClient\Helper\ExportProcessRunnerHelper;
use EzSystems\EzRecommendationClient\Helper\FileSystemHelper;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Helper\FileSystemHelper */
    private $fileSystem;

    /** @var \EzSystems\EzRecommendationClient\Helper\ExportProcessRunnerHelper */
    private $exportProcessRunner;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface $authenticator
     * @param \EzSystems\EzRecommendationClient\Helper\FileSystemHelper $fileSystem
     * @param \EzSystems\EzRecommendationClient\Helper\ExportProcessRunnerHelper $exportProcessRunner
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        AuthenticatorInterface $authenticator,
        FileSystemHelper $fileSystem,
        ExportProcessRunnerHelper $exportProcessRunner,
        LoggerInterface $logger
    ) {
        $this->authenticator = $authenticator;
        $this->fileSystem = $fileSystem;
        $this->exportProcessRunner = $exportProcessRunner;
        $this->logger = $logger;
    }

    /**
     * @param string $filePath
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function downloadAction(string $filePath): Response
    {
        $response = new Response();

        if (!$this->authenticator->authenticateByFile($filePath) || $this->authenticator->authenticate()) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        $content = $this->fileSystem->load($filePath);

        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filePath);
        $response->headers->set('Content-Length', filesize($this->fileSystem->getDir() . $filePath));

        $response->setContent($content);

        return $response;
    }

    /**
     * @param string $contentTypeIdList
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ExportInProgressException
     */
    public function exportAction(string $contentTypeIdList, Request $request): JsonResponse
    {
        $response = new JsonResponse();

        if (!$this->authenticator->authenticate()) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        if ($this->fileSystem->isLocked()) {
            $this->logger->warning('Export is running.');
            throw new ExportInProgressException('Export is running');
        }

        $options = $this->parseRequest($request);
        $options['contentTypeIdList'] = $contentTypeIdList;

        $this->exportProcessRunner->run($options);

        return $response->setData([sprintf(
            'Export started at %s',
            date('Y-m-d H:i:s')
        )]);
    }

    /**
     * Parses the request values.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    private function parseRequest(Request $request): array
    {
        $query = $request->query;

        $path = $query->get('path');
        $hidden = (int)$query->get('hidden', 0);
        $image = $query->get('image');
        $siteAccess = $query->get('siteaccess');
        $webHook = $query->get('webHook');
        $transaction = $query->get('transaction');
        $fields = $query->get('fields');
        $customerId = $query->get('customerId');
        $licenseKey = $query->get('licenseKey');

        if (preg_match('/^\/\d+(?:\/\d+)*\/$/', $path) !== 1) {
            $path = null;
        }

        if (preg_match('/^[a-zA-Z0-9\-\_]+$/', $image) !== 1) {
            $image = null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $siteAccess) !== 1) {
            $siteAccess = null;
        }

        if (preg_match('/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\&\.\/\?\:@\-_=#])*/', $webHook) !== 1) {
            $webHook = null;
        }

        if (preg_match('/^[0-9]+$/', $transaction) !== 1) {
            $transaction = (new \DateTime())->format('YmdHisv');
        }

        if (preg_match('/^[a-zA-Z0-9\-\_\,]+$/', $fields) !== 1) {
            $fields = null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $customerId) !== 1) {
            $customerId = null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]+$/', $licenseKey) !== 1) {
            $licenseKey = null;
        }

        return [
            'pageSize' => (int) $query->get('pageSize', null),
            'page' => (int) $query->get('page', 1),
            'path' => $path,
            'hidden' => $hidden,
            'image' => $image,
            'siteaccess' => $siteAccess,
            'documentRoot' => $request->server->get('DOCUMENT_ROOT'),
            'host' => $request->getSchemeAndHttpHost(),
            'webHook' => $webHook,
            'transaction' => $transaction,
            'lang' => preg_replace('/[^a-zA-Z0-9_-]+/', '', $query->get('lang')),
            'fields' => $fields,
            'mandatorId' => (int) $query->get('mandatorId', 0),
            'customerId' => $customerId,
            'licenseKey' => $licenseKey,
        ];
    }
}
