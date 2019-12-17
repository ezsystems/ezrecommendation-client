<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value\Output;

use Webmozart\Assert\Assert;

class UserCollection
{
    /** @var \EzSystems\EzRecommendationClient\Value\Output\User[] */
    private $users = [];

    /**
     * @param \EzSystems\EzRecommendationClient\Value\Output\User[] $users
     */
    public function __construct(array $users = [])
    {
        Assert::nullOrAllIsInstanceOf($users, User::class);
        $this->users = $users;
    }

    /**
     * @return \EzSystems\EzRecommendationClient\Value\Output\User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->users);
    }
}
