<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Exception\ExportInProgressException;
use EzSystems\EzRecommendationClient\Helper\ExportProcessRunnerHelper;
use EzSystems\EzRecommendationClient\Helper\FileSystemHelper;
use EzSystems\EzRecommendationClient\Value\ExportRequest;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param \EzSystems\EzRecommendationClient\Value\ExportRequest $request
     *
     * @ParamConverter("export_request_converter")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ExportInProgressException
     */
    public function exportAction(ExportRequest $request): JsonResponse
    {
        $response = new JsonResponse();

        if (!$this->authenticator->authenticate()) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        if ($this->fileSystem->isLocked()) {
            $this->logger->warning('Export is running.');
            throw new ExportInProgressException('Export is running');
        }

        $this->exportProcessRunner->run($request->getExportRequestParameters());

        return $response->setData([sprintf(
            'Export started at %s',
            date('Y-m-d H:i:s')
        )]);
    }
}
