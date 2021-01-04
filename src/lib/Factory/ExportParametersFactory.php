<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Factory;

use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use EzSystems\EzRecommendationClient\Helper\SiteAccessHelper;
use EzSystems\EzRecommendationClient\Value\ExportParameters;

final class ExportParametersFactory implements ExportParametersFactoryInterface
{
    /** @var \EzSystems\EzRecommendationClient\Helper\SiteAccessHelper */
    private $siteAccessHelper;

    public function __construct(SiteAccessHelper $siteAccessHelper)
    {
        $this->siteAccessHelper = $siteAccessHelper;
    }

    public function create(array $properties = []): ExportParameters
    {
        $properties['contentTypeIdList'] = ParamsConverterHelper::getIdListFromString(
            $properties['contentTypeIdList']
        );
        $properties['languages'] = $this->siteAccessHelper->getLanguages(
            (int)$properties['customerId'],
            $properties['siteaccess']
        );

        isset($properties['fields']) ? ParamsConverterHelper::getArrayFromString($properties['fields']) : null;

        return new ExportParameters($properties);
    }
}
