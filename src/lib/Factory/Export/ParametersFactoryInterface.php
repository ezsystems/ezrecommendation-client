<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Factory\Export;

use Ibexa\Personalization\Value\Export\Parameters;

interface ParametersFactoryInterface
{
    public const COMMAND_TYPE = 'command';

    /**
     * @phpstan-param array{
     *  item_type_identifier_list: string,
     *  languages: string,
     *  page_size: string,
     *  customer_id: ?string,
     *  license_key: ?string,
     *  siteaccess: ?string,
     *  web_hook: ?string,
     *  host: ?string,
     * } $options
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\MissingExportParameterException
     */
    public function create(array $options, string $type): Parameters;
}
