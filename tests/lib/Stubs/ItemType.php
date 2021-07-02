<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Tests\Stubs;

use Ibexa\Contracts\Personalization\Value\ItemTypeInterface;

final class ItemType implements ItemTypeInterface
{
    public const ARTICLE_IDENTIFIER = 'article';
    public const BLOG_IDENTIFIER = 'blog';
    public const PRODUCT_IDENTIFIER = 'product';

    public const ARTICLE_NAME = 'Article';
    public const BLOG_NAME = 'Blog';
    public const PRODUCT_NAME = 'Product';

    private string $identifier;

    private string $name;

    public function __construct(string $identifier, string $name)
    {
        $this->identifier = $identifier;
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
