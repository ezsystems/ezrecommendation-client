<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use EzSystems\EzPlatformRest\Server\Controller;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class ExportController extends Controller
{
    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\File\FileManagerInterface */
    private $fileManager;

    public function __construct(
        AuthenticatorInterface $authenticator,
        FileManagerInterface $fileManager
    ) {
        $this->authenticator = $authenticator;
        $this->fileManager = $fileManager;
    }

    /**
     * @throws \Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException
     */
    public function downloadAction(string $filePath): Response
    {
        $response = new Response();

        if (!$this->authenticator->authenticateByFile($filePath) || $this->authenticator->authenticate()) {
            return $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
        }

        $content = $this->fileManager->load($filePath);

        $response->headers->set('Content-Type', 'mime/type');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filePath);
        $response->headers->set('Content-Length', filesize($this->fileManager->getDir() . $filePath));

        $response->setContent($content);

        return $response;
    }
}
