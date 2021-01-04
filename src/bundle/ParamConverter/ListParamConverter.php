<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\ParamConverter;

use eZ\Publish\Core\REST\Server\Exceptions\BadRequestException;
use EzSystems\EzRecommendationClient\Exception\InvalidArgumentException;
use EzSystems\EzRecommendationClient\Helper\ParamsConverterHelper;
use EzSystems\EzRecommendationClient\Value\IdList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ListParamConverter implements ParamConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $paramName = $configuration->getName();

        if (!$request->attributes->has($paramName)) {
            return false;
        }

        try {
            $idListAsString = $request->attributes->get($paramName);
            $idList = new IdList();
            $idList->list = ParamsConverterHelper::getIdListFromString($idListAsString);
            $request->attributes->set($paramName, $idList);

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
        return IdList::class === $configuration->getClass();
    }
}
