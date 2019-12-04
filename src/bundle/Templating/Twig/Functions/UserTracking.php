<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Templating\Twig\Functions;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use EzSystems\EzRecommendationClient\Helper\ContentTypeHelper;
use EzSystems\EzRecommendationClient\Service\UserServiceInterface;
use EzSystems\EzRecommendationClient\Value\Parameters;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\RuntimeExtensionInterface;

class UserTracking extends AbstractFunction implements RuntimeExtensionInterface
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    /** @var \EzSystems\EzRecommendationClient\Service\UserServiceInterface */
    private $userService;

    /** @var \EzSystems\EzRecommendationClient\Helper\ContentTypeHelper */
    private $contentTypeHelper;

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    public function __construct(
        ConfigResolverInterface $configResolver,
        LocaleConverterInterface $localeConverter,
        UserServiceInterface $userService,
        ContentTypeHelper $contentTypeHelper,
        RequestStack $requestStack,
        TwigEnvironment $twig
    ) {
        parent::__construct($twig);

        $this->configResolver = $configResolver;
        $this->contentTypeHelper = $contentTypeHelper;
        $this->localeConverter = $localeConverter;
        $this->requestStack = $requestStack;
        $this->userService = $userService;
    }

    /**
     * Renders simple tracking snippet code.
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function trackUser(int $contentId): string
    {
        $includedContentTypes = $this->configResolver->getParameter('included_content_types', Parameters::NAMESPACE);
        $customerId = $this->configResolver->getParameter('authentication.customer_id', Parameters::NAMESPACE);

        if (!in_array($this->contentTypeHelper->getContentTypeIdentifier($contentId), $includedContentTypes)) {
            return '';
        }

        return $this->twig->render(
            'EzRecommendationClientBundle::track_user.html.twig',
            [
                'contentId' => $contentId,
                'contentTypeId' => $this->contentTypeHelper->getContentTypeId(
                    $this->contentTypeHelper->getContentTypeIdentifier($contentId)
                ),
                'language' => $this->localeConverter->convertToEz($this->requestStack->getCurrentRequest()->get('_locale')),
                'userId' => $this->userService->getUserIdentifier(),
                'customerId' => $customerId,
                'consumeTimeout' => $this->getConsumeTimeout(),
                'trackingScriptUrl' => $this->configResolver->getParameter(
                    'event_tracking.script_url',
                    Parameters::NAMESPACE,
                    Parameters::API_SCOPE
                ),
            ]
        );
    }

    private function getConsumeTimeout(): int
    {
        $consumeTimout = (int)$this->configResolver->getParameter(
            'recommendation.consume_timeout',
            Parameters::NAMESPACE,
            Parameters::API_SCOPE
        );

        return $consumeTimout * 1000;
    }
}
