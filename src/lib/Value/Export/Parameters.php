<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Value\Export;

use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;

final class Parameters
{
    public string $customerId;

    public string $licenseKey;

    /** @var array<string> */
    public array $itemTypeIdentifierList;

    /** @var array<string> */
    public array $languages;

    public string $siteaccess;

    public string $webHook;

    public string $host;

    public int $pageSize;

    /**
     * @param array<string> $itemTypeIdentifierList
     * @param array<string> $languages
     */
    private function __construct(
        string $customerId,
        string $licenseKey,
        array $itemTypeIdentifierList,
        array $languages,
        string $siteaccess,
        string $webHook,
        string $host,
        int $pageSize
    ) {
        $this->customerId = $customerId;
        $this->licenseKey = $licenseKey;
        $this->itemTypeIdentifierList = $itemTypeIdentifierList;
        $this->languages = $languages;
        $this->siteaccess = $siteaccess;
        $this->webHook = $webHook;
        $this->host = $host;
        $this->pageSize = $pageSize;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getLicenseKey(): string
    {
        return $this->licenseKey;
    }

    /**
     * @return array<string>
     */
    public function getItemTypeIdentifierList(): array
    {
        return $this->itemTypeIdentifierList;
    }

    /**
     * @return array<string>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function getSiteaccess(): string
    {
        return $this->siteaccess;
    }

    public function getWebHook(): string
    {
        return $this->webHook;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @phpstan-param array{
     *  customer_id: string,
     *  license_key: string,
     *  item_type_identifier_list: string,
     *  languages: string,
     *  siteaccess: string,
     *  web_hook: string,
     *  host: string,
     *  page_size: string,
     * } $properties
     */
    public static function fromArray(array $properties): self
    {
        return new self(
            $properties['customer_id'],
            $properties['license_key'],
            ParamsConverterHelper::getArrayFromString($properties['item_type_identifier_list']),
            ParamsConverterHelper::getArrayFromString($properties['languages']),
            $properties['siteaccess'],
            $properties['web_hook'],
            $properties['host'],
            (int)$properties['page_size'],
        );
    }
}
