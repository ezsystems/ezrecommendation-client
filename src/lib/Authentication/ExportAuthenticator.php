<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Authentication;

use EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface;
use EzSystems\EzRecommendationClient\File\FileManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Authenticator for export feature, mainly used for basic auth based authentication.
 */
final class ExportAuthenticator implements FileAuthenticatorInterface
{
    private const PHP_AUTH_USER = 'PHP_AUTH_USER';
    private const PHP_AUTH_PW = 'PHP_AUTH_PW';

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsResolverInterface */
    private $credentialsResolver;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \EzSystems\EzRecommendationClient\File\FileManagerInterface */
    private $fileManager;

    public function __construct(
        CredentialsResolverInterface $credentialsResolver,
        RequestStack $requestStack,
        FileManagerInterface $fileManager
    ) {
        $this->credentialsResolver = $credentialsResolver;
        $this->requestStack = $requestStack;
        $this->fileManager = $fileManager;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(): bool
    {
        /** @var \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $credentials */
        $credentials = $this->credentialsResolver->getCredentials();
        $server = $this->requestStack->getCurrentRequest()->server;

        if ($credentials->getMethod() === 'none') {
            return true;
        }

        return !empty($credentials->getLogin())
            && !empty($credentials->getPassword())
            && $server->get(self::PHP_AUTH_USER) === $credentials->getLogin()
            && $server->get(self::PHP_AUTH_PW) === $credentials->getPassword();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateByFile(string $filePath): bool
    {
        $server = $this->requestStack->getCurrentRequest()->server;

        $user = $server->get(self::PHP_AUTH_USER);
        $pass = crypt($server->get(self::PHP_AUTH_PW), md5($server->get(self::PHP_AUTH_PW)));

        if (false !== strpos($filePath, '.')) {
            return false;
        }

        $length = strrpos($filePath, '/') ?: 0;
        $passFile = substr($filePath, 0, $length) . '/.htpasswd';

        $fileContent = $this->fileManager->load($passFile);

        list($auth['user'], $auth['pass']) = explode(':', trim($fileContent));

        return $user == $auth['user'] && $pass == $auth['pass'];
    }
}
