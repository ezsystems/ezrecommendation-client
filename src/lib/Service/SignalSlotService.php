<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Service;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface;
use EzSystems\EzRecommendationClient\Request\EventNotifierRequest;
use EzSystems\EzRecommendationClient\Value\Config\ExportCredentials;
use EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials;
use EzSystems\EzRecommendationClient\Value\Notification;
use EzSystems\EzRecommendationClient\Value\Parameters;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

class SignalSlotService implements SignalSlotServiceInterface
{
    private const ACTION_UPDATE = 'UPDATE';
    private const ACTION_DELETE = 'DELETE';

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface */
    private $clientCredentialsChecker;

    /** @var \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface */
    private $exportCredentialsChecker;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface $clientCredentialsChecker
     * @param \EzSystems\EzRecommendationClient\Config\CredentialsCheckerInterface $exportCredentialsChecker
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EzRecommendationClientInterface $client,
        RepositoryInterface $repository,
        ContentServiceInterface $contentService,
        LocationServiceInterface $locationService,
        ContentTypeServiceInterface $contentTypeService,
        CredentialsCheckerInterface $clientCredentialsChecker,
        CredentialsCheckerInterface $exportCredentialsChecker,
        ConfigResolverInterface $configResolver,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->clientCredentialsChecker = $clientCredentialsChecker;
        $this->exportCredentialsChecker = $exportCredentialsChecker;
        $this->configResolver = $configResolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent(int $contentId, ?int $versionNo = null): void
    {
        $content = $this->getContent($contentId, null, $versionNo);
        $this->processSending(__METHOD__, self::ACTION_UPDATE, $content, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContent(int $contentId): void
    {
        $content = $this->getContent($contentId);
        $this->processSending(__METHOD__, self::ACTION_DELETE, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function hideContent(int $contentId): void
    {
        $content = $this->getContent($contentId);
        $this->processSending(__METHOD__, self::ACTION_DELETE, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function revealContent(int $contentId): void
    {
        $content = $this->getContent($contentId);
        $this->processSending(__METHOD__, self::ACTION_UPDATE, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function hideLocation(int $locationId, bool $isChild = false): void
    {
        $location = $this->locationService->loadLocation($locationId);
        $children = $this->locationService->loadLocationChildren($location)->locations;

        foreach ($children as $child) {
            $this->hideLocation($child->id, true);
        }

        $content = $this->getContent($location->contentId);

        if (!$content instanceof Content
            && !$isChild
            && $this->areLocationsVisible($content->contentInfo)
        ) {
            return;
        }

        $this->processSending(__METHOD__, self::ACTION_DELETE, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function unhideLocation(int $locationId): void
    {
        $this->updateLocationWithChildren($locationId, __METHOD__, self::ACTION_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function swapLocation(int $locationId): void
    {
        $this->updateLocationWithChildren($locationId, __METHOD__, self::ACTION_UPDATE);
    }

    /**
     * @param int $locationId
     * @param string $method
     * @param string $action
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function updateLocationWithChildren(int $locationId, string $method, string $action): void
    {
        $location = $this->locationService->loadLocation($locationId);
        $children = $this->locationService->loadLocationChildren($location)->locations;

        foreach ($children as $child) {
            $this->updateLocationWithChildren($child->id, $method, $action);
        }

        $content = $this->getContent($location->contentId);

        if (!$content instanceof Content) {
            return;
        }

        $this->processSending($method, $action, $content);
    }

    /**
     * @param int $contentId
     * @param array|null $languages
     * @param int|null $versionNo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getContent(int $contentId, ?array $languages = null, ?int $versionNo = null): ?Content
    {
        try {
            $content = $this->contentService->loadContent($contentId, $languages, $versionNo);

            if ($this->isContentTypeExcluded($content)) {
                return null;
            }

            return $content;
        } catch (NotFoundException $exception) {
            $this->logger->error(sprintf('Error while loading Content: %d, message: %s', $contentId, $exception->getMessage()));
            // this is most likely a internal draft, or otherwise invalid, ignoring
            return null;
        }
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return bool
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    private function areLocationsVisible(ContentInfo $contentInfo): bool
    {
        // do not send the notification if one of the locations is still visible, to prevent deleting content
        $contentLocations = $this->locationService->loadLocations($contentInfo);

        foreach ($contentLocations as $contentLocation) {
            if (!$contentLocation->hidden) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $method
     * @param string $action
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param int|null $versionNo
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function processSending(string $method, string $action, ?Content $content, ?int $versionNo = null): void
    {
        if ($content) {
            $this->logger->debug(sprintf('RecommendationNotifier: Generating notification for %s(%s)', $method, $content->id));

            /** @var EzRecommendationClientCredentials $clientCredentials */
            $clientCredentials = $this->clientCredentialsChecker->getCredentials();

            if (!$clientCredentials) {
                return;
            }
            $notificationEvents = $this->generateNotificationEvents($action, $content, $versionNo, $this->exportCredentialsChecker->getCredentials());
            $notification = $this->getNotification($notificationEvents, $clientCredentials);

            try {
                $response = $this->client->notifier()->notify($notification);
            } catch (RequestException $e) {
                $this->logger->error(sprintf('RecommendationNotifier: notification error for %s: %s', $method, $e->getMessage()));
            }
        }
    }

    /**
     * @param array $notificationEvents
     * @param \EzSystems\EzRecommendationClient\Value\Config\EzRecommendationClientCredentials $clientCredentials
     *
     * @return \EzSystems\EzRecommendationClient\Value\Notification
     */
    private function getNotification(
        array $notificationEvents,
        EzRecommendationClientCredentials $clientCredentials
    ): Notification {
        $notification = new Notification();
        $notification->events = $notificationEvents;
        $notification->customerId = $clientCredentials->getCustomerId();
        $notification->licenseKey = $clientCredentials->getLicenseKey();

        return $notification;
    }

    /**
     * @param string $action
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param int|null $versionNo
     * @param \EzSystems\EzRecommendationClient\Value\Config\ExportCredentials $exportCredentials
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function generateNotificationEvents(
        string $action,
        Content $content,
        ?int $versionNo,
        ExportCredentials $exportCredentials
    ): array {
        $events = [];

        foreach ($this->getLanguageCodes($content, $versionNo) as $lang) {
            $event = new EventNotifierRequest([
                EventNotifierRequest::ACTION_KEY => $action,
                EventNotifierRequest::FORMAT_KEY => 'EZ',
                EventNotifierRequest::URI_KEY => $this->getContentUri($content, $lang),
                EventNotifierRequest::ITEM_ID_KEY => $content->id,
                EventNotifierRequest::CONTENT_TYPE_ID_KEY => $content->contentInfo->contentTypeId,
                EventNotifierRequest::LANG_KEY => $lang ?? null,
                EventNotifierRequest::CREDENTIALS_KEY => [
                    'login' => $exportCredentials->getLogin(),
                    'password' => $exportCredentials->getPassword(),
                ],
            ]);

            $events[] = $event->getRequestAttributes();
        }

        return $events;
    }

    /**
     * Gets languageCodes based on $content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param int|null $versionNo
     *
     * @return string[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function getLanguageCodes(Content $content, ?int $versionNo): array
    {
        $version = $this->contentService->loadVersionInfo($content->contentInfo, $versionNo);

        return $version->languageCodes;
    }

    /**
     * Generates the REST URI of content $contentId.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string|null $lang
     *
     * @return string
     */
    private function getContentUri(Content $content, ?string $lang): string
    {
        return sprintf(
            '%s/api/ezp/v2/ez_recommendation/v1/content/%s%s',
            $this->configResolver->getParameter('host_uri', Parameters::NAMESPACE),
            $content->id,
            isset($lang) ? '?lang=' . $lang : ''
        );
    }

    /**
     * Checks if content is excluded from supported content types.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function isContentTypeExcluded(Content $content): bool
    {
        $contentType = $this->repository->sudo(function () use ($content) {
            return $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        });

        return !in_array(
            $contentType->identifier,
            $this->configResolver->getParameter('included_content_types', Parameters::NAMESPACE)
        );
    }
}
