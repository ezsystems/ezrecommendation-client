<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Ibexa\Contracts\Personalization\Criteria\CriteriaInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentDataSourceQueryType extends OptionsResolverBasedQueryType
{
    /**
     * @phpstan-param array{
     *  'criteria': CriteriaInterface
     * } $parameters
     */
    protected function doGetQuery(array $parameters): Query
    {
        $query = new Query();

        $query->filter = new Query\Criterion\LogicalAnd(
            $this->buildCriteria($parameters['criteria'])
        );

        return $query;
    }

    public static function getName(): string
    {
        return 'Ibexa:Personalization:ContentDataSourceQueryType';
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setDefaults(['criteria' => CriteriaInterface::class])
            ->addAllowedTypes('criteria', CriteriaInterface::class);
    }

    /**
     * @return array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion>
     */
    public function buildCriteria(CriteriaInterface $criteria): array
    {
        return [
            new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE),
            new Query\Criterion\ContentTypeIdentifier($criteria->getItemTypeIdentifiers()),
        ];
    }
}
