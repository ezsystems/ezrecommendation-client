<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Strategy\Credentials;

use Ibexa\PersonalizationClient\Exception\UnsupportedExportCredentialsMethodStrategy;
use Ibexa\PersonalizationClient\Value\Export\Credentials;
use Traversable;

final class ExportCredentialsStrategyDispatcher implements ExportCredentialsStrategyDispatcherInterface
{
    /** @var iterable<\Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface> */
    private iterable $credentialMethodStrategies;

    /**
     * @param iterable<\Ibexa\PersonalizationClient\Strategy\Credentials\ExportCredentialsStrategyInterface> $credentialMethodStrategies
     */
    public function __construct(iterable $credentialMethodStrategies)
    {
        $this->credentialMethodStrategies = $credentialMethodStrategies;
    }

    public function getCredentials(string $method, ?string $siteAccess = null): Credentials
    {
        $strategies = $this->credentialMethodStrategies instanceof Traversable
            ? iterator_to_array($this->credentialMethodStrategies)
            : $this->credentialMethodStrategies;

        if (!isset($strategies[$method])) {
            throw new UnsupportedExportCredentialsMethodStrategy(
                $method,
                array_keys($strategies)
            );
        }

        return $strategies[$method]->getCredentials($siteAccess);
    }
}
