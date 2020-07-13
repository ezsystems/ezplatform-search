<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Search\QueryType;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\QueryType\OptionsResolverBasedQueryType;
use Ibexa\Platform\Bundle\SearchBundle\Form\Data\SearchData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchQueryType extends OptionsResolverBasedQueryType
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    protected function doGetQuery(array $parameters): Query
    {
        /** @var \Ibexa\Platform\Bundle\SearchBundle\Form\Data\SearchData $searchData */
        $searchData = $parameters['search_data'];

        $query = new Query();
        if (null !== $searchData->getQuery()) {
            $query->query = new Criterion\FullText($searchData->getQuery());
        }

        $criteria = $this->buildCriteria($searchData);
        if (!empty($criteria)) {
            $query->filter = new Criterion\LogicalAnd($criteria);
        }

        if (!$this->searchService->supports(SearchService::CAPABILITY_SCORING)) {
            $query->sortClauses[] = new SortClause\DateModified(Query::SORT_ASC);
        }

        return $query;
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults([
            'search_data' => new SearchData(),
        ]);

        $optionsResolver->setAllowedTypes('search_data', SearchData::class);
    }

    public static function getName(): string
    {
        return 'PlatformSearch:SearchQuery';
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion[]
     */
    protected function buildCriteria(SearchData $searchData): array
    {
        $criteria = [];

        if (null !== $searchData->getSection()) {
            $criteria[] = new Criterion\SectionId($searchData->getSection()->id);
        }

        if (!empty($searchData->getContentTypes())) {
            $criteria[] = new Criterion\ContentTypeId(array_column($searchData->getContentTypes(), 'id'));
        }

        if (!empty($searchData->getLastModified())) {
            $modified = $searchData->getLastModified();

            $criteria[] = new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::BETWEEN,
                [
                    $modified['start_date'],
                    $modified['end_date'],
                ]
            );
        }

        if (!empty($searchData->getCreated())) {
            $created = $searchData->getCreated();

            $criteria[] = new Criterion\DateMetadata(
                Criterion\DateMetadata::CREATED,
                Criterion\Operator::BETWEEN,
                [
                    $created['start_date'],
                    $created['end_date'],
                ]
            );
        }

        if ($searchData->getCreator() instanceof User) {
            $criteria[] = new Criterion\UserMetadata(
                Criterion\UserMetadata::OWNER,
                Criterion\Operator::EQ,
                $searchData->getCreator()->id
            );
        }

        if (null !== $searchData->getSearchUsersData()) {
            if (!empty($searchData->getSearchUsersData()->getPossibleUsers())) {
                $criteria[] = new Criterion\UserMetadata(
                    Criterion\UserMetadata::OWNER,
                    Criterion\Operator::IN,
                    array_column($searchData->getSearchUsersData()->getPossibleUsers(), 'id')
                );
            } else {
                // If no users matched user query, do not return any content.
                $criteria[] = new Criterion\MatchNone();
            }
        }

        if (null !== $searchData->getSubtree()) {
            $criteria[] = new Criterion\Subtree($searchData->getSubtree());
        }

        return $criteria;
    }
}
