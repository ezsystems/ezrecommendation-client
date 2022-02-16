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
use Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentQueryType extends OptionsResolverBasedQueryType
{
    /** @var \Ibexa\Personalization\Config\Repository\RepositoryConfigResolverInterface */
    private $repositoryConfigResolver;

    public function __construct(RepositoryConfigResolverInterface $repositoryConfigResolver)
    {
        $this->repositoryConfigResolver = $repositoryConfigResolver;
    }

    public static function getName(): string
    {
        return 'Ibexa:Personalization:ContentQueryType';
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setDefaults(
                [
                    'contentId' => null,
                    'language' => null,
                ]
            )
            ->setAllowedTypes('contentId', ['int', 'string'])
            ->setAllowedTypes('language', ['null', 'string']);
    }

    /**
     * @phpstan-param array{
     *  'contentId': int|string,
     *  'language': ?string,
     * } $parameters
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     */
    protected function doGetQuery(array $parameters): Query
    {
        $query = new Query();

        $contentId = $parameters['contentId'];
        $language = $parameters['language'];

        $query->filter = new Criterion\LogicalAnd($this->buildCriteria($contentId, $language));

        return $query;
    }

    /**
     * @param int|string $contentId
     *
     * @return array<\eZ\Publish\API\Repository\Values\Content\Query\Criterion>
     */
    private function buildCriteria($contentId, ?string $language = null): array
    {
        $criteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);

        if ($this->repositoryConfigResolver->useRemoteId()) {
            $criteria[] = new Criterion\RemoteId((string) $contentId);
        } else {
            $criteria[] = new Criterion\ContentId((int) $contentId);
        }

        if (!empty($language)) {
            $criteria[] = new Criterion\LanguageCode($language);
        }

        return $criteria;
    }
}
