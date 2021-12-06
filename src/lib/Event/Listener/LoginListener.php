<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Listener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;
use EzSystems\EzRecommendationClient\Value\Session as RecommendationSession;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Sends notification to Recommendation servers when user is logged in.
 */
final class LoginListener
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService */
    private $siteAccessService;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        EzRecommendationClientInterface $client,
        ConfigResolverInterface $configResolver,
        LoggerInterface $logger,
        SiteAccessService $siteAccessService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->session = $session;
        $this->client = $client;
        $this->configResolver = $configResolver;
        $this->logger = $logger;
        $this->siteAccessService = $siteAccessService;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($event->getRequest()->get('is_rest_request')) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') // user has just logged in
            || !$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') // user has logged in using remember_me cookie
        ) {
            return;
        }

        $siteAccessName = $this->siteAccessService->getCurrent()->name;
        $endpoint = $this->getEndpoint($siteAccessName);
        $customerId = $this->getCustomerId($siteAccessName);

        if (empty($customerId) || empty($endpoint)) {
            return;
        }

        if (!$event->getRequest()->cookies->has(RecommendationSession::RECOMMENDATION_SESSION_KEY)) {
            if (!$this->session->isStarted()) {
                $this->session->start();
            }
            $event->getRequest()->cookies->set(RecommendationSession::RECOMMENDATION_SESSION_KEY, $this->session->getId());
        }

        $notificationUri = $this->getNotificationUri($endpoint, $customerId, $event);

        $this->logger->debug(sprintf('Send login event notification to Recommendation: %s', $notificationUri));

        try {
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $this->client->getHttpClient()->get($notificationUri);

            $this->logger->debug(sprintf('Got %s from Recommendation login event notification', $response->getStatusCode()));
        } catch (RequestException $e) {
            $this->logger->error(sprintf('Recommendation login event notification error: %s', $e->getMessage()));
        }
    }

    private function getCustomerId(string $siteAccessName): ?int
    {
        return $this->configResolver->getParameter(
            'authentication.customer_id',
            Parameters::NAMESPACE,
            $siteAccessName
        );
    }

    private function getEndpoint(string $siteAccessName): ?string
    {
        return $this->configResolver->getParameter(
            Parameters::API_SCOPE . '.event_tracking.endpoint',
            Parameters::NAMESPACE,
            $siteAccessName
        );
    }

    /**
     * Returns notification API end-point.
     */
    private function getNotificationUri(string $endpoint, int $customerId, InteractiveLoginEvent $event): string
    {
        return sprintf('%s/api/%d/%s/%s/%s',
            $endpoint,
            $customerId,
            'login',
            $event->getRequest()->cookies->get(RecommendationSession::RECOMMENDATION_SESSION_KEY),
            $this->getUser($event->getAuthenticationToken())
        );
    }

    /**
     * Returns current username or ApiUser id.
     */
    private function getUser(TokenInterface $authenticationToken): string
    {
        $user = $authenticationToken->getUser();
        if ($user instanceof UserInterface) {
            return (string) $user->getAPIUser()->id;
        }

        return $authenticationToken->getUserIdentifier();
    }
}
