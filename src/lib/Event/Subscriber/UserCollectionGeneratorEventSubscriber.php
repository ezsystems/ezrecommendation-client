<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event\Subscriber;

use EzSystems\EzRecommendationClient\Event\GenerateUserCollectionDataEvent;
use EzSystems\EzRecommendationClient\Event\UserAPIEvent;
use EzSystems\EzRecommendationClient\Value\Output\UserCollection;
use EzSystems\EzRecommendationClientBundle\Serializer\Normalizer\AttributeNormalizer;
use EzSystems\EzRecommendationClientBundle\Serializer\Normalizer\UserCollectionNormalizer;
use EzSystems\EzRecommendationClientBundle\Serializer\Normalizer\UserNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;

final class UserCollectionGeneratorEventSubscriber implements EventSubscriberInterface
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcher */
    private $eventDispatcher;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserAPIEvent::UPDATE => ['onRecommendationUpdateUserCollection', 128],
        ];
    }

    /**
     * @param \EzSystems\EzRecommendationClient\Event\UserAPIEvent $userAPIEvent
     */
    public function onRecommendationUpdateUserCollection(UserAPIEvent $userAPIEvent): void
    {
        $event = new GenerateUserCollectionDataEvent();
        $this->eventDispatcher->dispatch(GenerateUserCollectionDataEvent::NAME, $event);

        $userCollection = $event->getUserCollection();

        if ($userCollection->isEmpty()) {
            return;
        }

        $userApiRequest = $userAPIEvent->getUserAPIRequest();

        if ($event->hasUserCollectionName()) {
            $userApiRequest->source = $event->getUserCollectionName();
        }

        $userApiRequest->xmlBody = $this->generateXml($userCollection);
    }

    /**
     * Generates xml string based on UserCollection object.
     *
     * @param \EzSystems\EzRecommendationClient\Value\Output\UserCollection $userCollection
     *
     * @return string
     */
    private function generateXml(UserCollection $userCollection): string
    {
        $encoders = [new XmlEncoder()];
        $normalizers = [
            new UserCollectionNormalizer(),
            new UserNormalizer(),
            new AttributeNormalizer(),
            new ArrayDenormalizer(),
        ];

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize(
            $userCollection,
            'xml', [
            'xml_root_node_name' => 'users',
            ]
        );
    }
}
