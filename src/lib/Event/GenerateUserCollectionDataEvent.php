<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Event;

use EzSystems\EzRecommendationClient\Value\Output\UserCollection;
use Symfony\Contracts\EventDispatcher\Event;
use Webmozart\Assert\Assert;

final class GenerateUserCollectionDataEvent extends Event
{
    /** @var \EzSystems\EzRecommendationClient\Value\Output\UserCollection */
    private $userCollection;

    /** @var string */
    private $userCollectionName = '';

    public function setUserCollection(UserCollection $userCollection): void
    {
        Assert::isInstanceOf($userCollection, UserCollection::class);
        $this->userCollection = $userCollection;
    }

    public function getUserCollection(): UserCollection
    {
        return $this->userCollection;
    }

    public function getUserCollectionName(): string
    {
        return $this->userCollectionName;
    }

    public function setUserCollectionName(string $userCollectionName): void
    {
        $this->userCollectionName = $userCollectionName;
    }

    public function hasUserCollectionName(): bool
    {
        return \strlen($this->userCollectionName) > 0;
    }
}
