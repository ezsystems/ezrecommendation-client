<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\Export\Input;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

interface CommandInputResolverInterface
{
    /**
     * @phpstan-return array{
     *  item_type_identifier_list: string,
     *  languages: string,
     *  page_size: string,
     *  customer_id: ?string,
     *  license_key: ?string,
     *  siteaccess: ?string,
     *  web_hook: ?string,
     *  host: ?string,
     * }
     */
    public function resolve(InputInterface $input, ?Application $application = null): array;
}
