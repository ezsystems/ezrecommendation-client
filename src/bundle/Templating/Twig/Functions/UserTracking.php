<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Templating\Twig\Functions;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Helper\ContentTypeHelper;
use EzSystems\EzRecommendationClient\Service\UserServiceInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;
use Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\RuntimeExtensionInterface;

final class UserTracking implements RuntimeExtensionInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper */
    private $contentTypeHelper;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface */
    private $repositoryConfigResolver;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \Twig\Environment */
    private $twig;

    /** @var \EzSystems\EzRecommendationClient\Service\UserServiceInterface */
    private $userService;

    public function __construct(
        ConfigResolverInterface $configResolver,
        ContentTypeHelper $contentTypeHelper,
        LocaleConverterInterface $localeConverter,
        RepositoryConfigResolverInterface $repositoryConfigResolver,
        RequestStack $requestStack,
        TwigEnvironment $twig,
        UserServiceInterface $userService
    ) {
        $this->configResolver = $configResolver;
        $this->contentTypeHelper = $contentTypeHelper;
        $this->localeConverter = $localeConverter;
        $this->repositoryConfigResolver = $repositoryConfigResolver;
        $this->requestStack = $requestStack;
        $this->twig = $twig;
        $this->userService = $userService;
    }

    /**
     * @throws \Exception
     */
    public function trackUser(Content $content): ?string
    {
        $contentInfo = $content->contentInfo;
        if ($this->contentTypeHelper->isContentTypeExcluded($contentInfo)) {
            return null;
        }

        $contentId = $this->repositoryConfigResolver->useRemoteId() ? $contentInfo->remoteId : $content->id;

        return $this->render(
            [
                'contentId' => $contentId,
                'contentTypeId' => $content->getContentType()->id,
            ]
        );
    }

    /**
     * @phpstan-param array{
     *  'contentId': string|int,
     *  'contentTypeId': int,
     * } $context
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function render(array $context): string
    {
        $customerId = $this->configResolver->getParameter('authentication.customer_id', Parameters::NAMESPACE);

        return $this->twig->render(
            '@EzRecommendationClient/track_user.html.twig',
            array_merge(
                [
                    'language' => $this->localeConverter->convertToEz($this->requestStack->getCurrentRequest()->get('_locale')),
                    'userId' => $this->userService->getUserIdentifier(),
                    'customerId' => $customerId,
                    'consumeTimeout' => $this->getConsumeTimeout(),
                    'trackingScriptUrl' => $this->configResolver->getParameter(
                        Parameters::API_SCOPE . '.event_tracking.script_url',
                        Parameters::NAMESPACE
                    ),
                ],
                $context
            )
        );
    }

    private function getConsumeTimeout(): int
    {
        $consumeTimout = (int)$this->configResolver->getParameter(
            Parameters::API_SCOPE . '.recommendation.consume_timeout',
            Parameters::NAMESPACE
        );

        return $consumeTimout * 1000;
    }
}
