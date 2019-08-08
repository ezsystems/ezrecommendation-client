<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use EzSystems\EzRecommendationClient\Value\Output\UserCollection;
use Symfony\Component\EventDispatcher\Event;
use Webmozart\Assert\Assert;

final class GenerateUserCollectionDataEvent extends Event
{
    const NAME = 'recommendation.user_collection_data';

    /** @var \EzSystems\EzRecommendationClient\Value\Output\UserCollection */
    private $userCollection;

    /** @var string */
    private $userCollectionName = '';

    /**
     * @param \EzSystems\EzRecommendationClient\Value\Output\UserCollection $userCollection
     */
    public function setUserCollection(UserCollection $userCollection): void
    {
        Assert::isInstanceOf($userCollection, UserCollection::class);
        $this->userCollection = $userCollection;
    }

    /**
     * @return \EzSystems\EzRecommendationClient\Value\Output\UserCollection
     */
    public function getUserCollection(): UserCollection
    {
        return $this->userCollection;
    }

    /**
     * @return string
     */
    public function getUserCollectionName(): string
    {
        return $this->userCollectionName;
    }

    /**
     * @param string $userCollectionName
     */
    public function setUserCollectionName(string $userCollectionName): void
    {
        $this->userCollectionName = $userCollectionName;
    }

    /**
     * @return bool
     */
    public function hasUserCollectionName(): bool
    {
        return strlen($this->userCollectionName) > 0;
    }
}
