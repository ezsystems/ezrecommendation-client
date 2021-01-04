<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Templating\Twig;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Helper\ContentHelper;
use EzSystems\EzRecommendationClient\Service\UserServiceInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RecommendationExtension extends AbstractExtension
{
    /** @var \EzSystems\EzRecommendationClient\Service\UserServiceInterface */
    private $userService;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentHelper */
    private $contentHelper;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface */
    private $credentialsChecker;

    /**
     * @param \EzSystems\EzRecommendationClient\Service\UserServiceInterface $userService
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     * @param \EzSystems\EzRecommendationClient\Helper\ContentHelper $contentHelper
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface $credentialsChecker
     */
    public function __construct(
        UserServiceInterface $userService,
        LocaleConverterInterface $localeConverter,
        ContentHelper $contentHelper,
        RequestStack $requestStack,
        ConfigResolverInterface $configResolver,
        CredentialsCheckerInterface $credentialsChecker
    ) {
        $this->userService = $userService;
        $this->localeConverter = $localeConverter;
        $this->contentHelper = $contentHelper;
        $this->requestStack = $requestStack;
        $this->configResolver = $configResolver;
        $this->credentialsChecker = $credentialsChecker;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'ez_recommendation_extension';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ez_recommendation_enabled', [$this, 'isRecommendationsEnabled']),
            new TwigFunction('ez_recommendation_track_user', [$this, 'trackUser'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    /**
     * @return bool
     */
    public function isRecommendationsEnabled(): bool
    {
        return $this->credentialsChecker->hasCredentials();
    }

    /**
     * Renders simple tracking snippet code.
     *
     * @param \Twig\Environment $twigEnvironment
     * @param int $contentId
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function trackUser(Environment $twigEnvironment, int $contentId): string
    {
        $includedContentTypes = $this->configResolver->getParameter('included_content_types', Parameters::NAMESPACE);
        $customerId = $this->configResolver->getParameter('authentication.customer_id', Parameters::NAMESPACE);

        if (!in_array($this->contentHelper->getContentIdentifier($contentId), $includedContentTypes)) {
            return '';
        }

        return $twigEnvironment->render(
            'EzRecommendationClientBundle::track_user.html.twig',
            [
                'contentId' => $contentId,
                'contentTypeId' => $this->contentHelper->getContentTypeId($this->contentHelper->getContentIdentifier($contentId)),
                'language' => $this->localeConverter->convertToEz($this->requestStack->getCurrentRequest()->get('_locale')),
                'userId' => $this->userService->getUserIdentifier(),
                'customerId' => $customerId,
                'consumeTimeout' => $this->getConsumeTimeout(),
                'trackingScriptUrl' => $this->configResolver->getParameter('event_tracking.script_url', Parameters::NAMESPACE, Parameters::API_SCOPE),
            ]
        );
    }

    /**
     * @return int
     */
    private function getConsumeTimeout(): int
    {
        $consumeTimout = (int) $this->configResolver->getParameter('recommendation.consume_timeout', Parameters::NAMESPACE, Parameters::API_SCOPE);

        return $consumeTimout * 1000;
    }
}
