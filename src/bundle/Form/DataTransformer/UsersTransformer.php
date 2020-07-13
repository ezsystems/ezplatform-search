<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\SearchBundle\Form\DataTransformer;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Ibexa\Platform\Bundle\SearchBundle\Form\Data\SearchUsersData;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms inputed name/login/email to collection of matched Users.
 */
class UsersTransformer implements DataTransformerInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var string */
    private $userContentTypeIdentifier;

    public function __construct(
        Repository $repository,
        SearchService $searchService,
        string $userContentTypeIdentifier
    ) {
        $this->repository = $repository;
        $this->searchService = $searchService;
        $this->userContentTypeIdentifier = $userContentTypeIdentifier;
    }

    /**
     * Transforms a domain specific User object into a Users's ID.
     *
     * @param \Ibexa\Platform\Bundle\SearchBundle\Form\Data\SearchUsersData|null $value
     *
     * @return mixed|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof SearchUsersData) {
            throw new TransformationFailedException('Expected a ' . SearchUsersData::class . ' object.');
        }

        return $value->getQuery();
    }

    /**
     * @param string|null $value
     */
    public function reverseTransform($value): SearchUsersData
    {
        if ($value === null) {
            return new SearchUsersData();
        }

        $filter = new LogicalAnd([
            new Query\Criterion\ContentTypeIdentifier([$this->userContentTypeIdentifier]),
            new Query\Criterion\FullText($value),
        ]);

        $searchService = $this->searchService;

        $result = $this->repository->sudo(function () use ($searchService, $filter) {
            return $searchService->findContent(new Query([
                'filter' => $filter,
            ]));
        });

        return new SearchUsersData(
            array_map(function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            }, $result->searchHits),
            $value
        );
    }
}
