<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\SearchBundle\Form\Data;

class SearchUsersData
{
    /** @var string */
    private $query;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content[] */
    private $possibleUsers;

    public function __construct(array $possibleUsers = [], ?string $query = null)
    {
        $this->query = $query;
        $this->possibleUsers = $possibleUsers;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function getPossibleUsers(): array
    {
        return $this->possibleUsers;
    }

    public function setPossibleUsers(array $possibleUsers): void
    {
        $this->possibleUsers = $possibleUsers;
    }
}
