<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Export;

use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

final class FileSettings
{
    private ItemListInterface $itemList;

    private string $identifier;

    private string $language;

    private int $page;

    private string $chunkPath;

    public function __construct(
        ItemListInterface $itemList,
        string $identifier,
        string $language,
        int $page,
        string $chunkPath
    ) {
        $this->itemList = $itemList;
        $this->identifier = $identifier;
        $this->language = $language;
        $this->page = $page;
        $this->chunkPath = $chunkPath;
    }

    public function getItemList(): ItemListInterface
    {
        return $this->itemList;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getChunkPath(): string
    {
        return $this->chunkPath;
    }
}
