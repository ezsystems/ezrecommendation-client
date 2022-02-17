<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Personalization\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentQueryType extends OptionsResolverBasedQueryType
{
    public static function getName(): string
    {
        return 'Ibexa:Personalization:ContentQueryType';
    }

    public function getQueryByContentId(int $contentId, ?string $language = null): Query
    {
        return $this->getQuery(
            [
                'criteria' => [new Criterion\ContentId($contentId)],
                'language' => $language,
            ]
        );
    }

    public function getQueryByContentRemoteId(string $remoteId, ?string $language = null): Query
    {
        return $this->getQuery(
            [
                'criteria' => [new Criterion\RemoteId($remoteId)],
                'language' => $language,
            ]
        );
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setDefaults(
                [
                    'criteria' => [],
                    'language' => null,
                ]
            )
            ->setAllowedTypes('criteria', ['array'])
            ->setAllowedTypes('language', ['null', 'string']);
    }

    /**
     * @phpstan-param array{
     *  'criteria': array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion>,
     *  'language': ?string,
     * } $parameters
     */
    protected function doGetQuery(array $parameters): Query
    {
        $query = new Query();

        $query->filter = new Criterion\LogicalAnd(
            $this->buildCriteria(
                $parameters['criteria'],
                $parameters['language']
            )
        );

        return $query;
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion> $criteria
     *
     * @return array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion>
     */
    private function buildCriteria(array $criteria, ?string $language = null): array
    {
        $additionalCriteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);

        if (!empty($language)) {
            $additionalCriteria[] = new Criterion\LanguageCode($language);
        }

        return array_merge(
            $additionalCriteria,
            $criteria,
        );
    }
}
