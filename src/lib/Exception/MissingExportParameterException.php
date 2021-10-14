<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Exception;

use Ibexa\PersonalizationClient\Factory\Export\ParametersFactoryInterface;
use Throwable;

final class MissingExportParameterException extends ExportException
{
    /**
     * @param array<int, string> $missingParameters
     */
    public function __construct(array $missingParameters, string $type, int $code = 0, Throwable $previous = null)
    {
        $parameters = [];

        if ($type === ParametersFactoryInterface::COMMAND_TYPE) {
            foreach ($missingParameters as $parameter) {
                $parameters[] = str_replace('_', '-', $parameter);
            }
        } else {
            $parameters = $missingParameters;
        }

        parent::__construct(
            sprintf(
                'Required parameters: %s are missing',
                implode(', ', $parameters)
            ),
            $code,
            $previous
        );
    }
}
