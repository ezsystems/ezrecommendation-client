<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\ParamConverter;

use EzSystems\EzPlatformRest\Server\Exceptions\BadRequestException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Mapper\ExportRequestMapper;
use EzSystems\EzRecommendationClient\Value\ExportRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ExportRequestParamConverter implements ParamConverterInterface
{
    /** @var \EzSystems\EzRecommendationClient\Mapper\ExportRequestMapper */
    private $exportRequestMapper;

    /**
     * @param \EzSystems\EzRecommendationClient\Mapper\ExportRequestMapper $exportRequestMapper
     */
    public function __construct(ExportRequestMapper $exportRequestMapper)
    {
        $this->exportRequestMapper = $exportRequestMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $exportRequest = $this->exportRequestMapper->getExportRequest($request);
            $paramName = $configuration->getName();

            $request->attributes->set($paramName, $exportRequest);

            return true;
        } catch (InvalidArgumentException $e) {
            throw new BadRequestException('Bad Request', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return ExportRequest::class === $configuration->getClass();
    }
}
