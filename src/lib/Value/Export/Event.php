<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Value\Export;

final class Event
{
    private const ACTION_FULL_IMPORT = 'FULL';

    private int $itemTypeId;

    private string $itemTypeName;

    private string $language;

    /** @var array<string> */
    private array $uriList;

    private Credentials $credentials;

    private string $format;

    /**
     * @param array<string> $uriList
     */
    public function __construct(
        int $itemTypeId,
        string $itemTypeName,
        string $language,
        array $uriList,
        Credentials $credentials,
        string $format = SupportedFormat::IBEXA
    ) {
        $this->itemTypeId = $itemTypeId;
        $this->itemTypeName = $itemTypeName;
        $this->language = $language;
        $this->uriList = $uriList;
        $this->credentials = $credentials;
        $this->format = $format;
    }

    public function getItemTypeId(): int
    {
        return $this->itemTypeId;
    }

    public function getItemTypeName(): string
    {
        return $this->itemTypeName;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return array<string>
     */
    public function getUriList(): array
    {
        return $this->uriList;
    }

    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getAction(): string
    {
        return self::ACTION_FULL_IMPORT;
    }
}
