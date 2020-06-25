<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\SearchBundle\Form\DataTransformer;

use DateTime;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Translates timestamp and DataInterval to domain specific timestamp date range.
 */
class DateIntervalTransformer implements DataTransformerInterface
{
    /**
     * @param array|null $value
     *
     * @return array|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value)
    {
        return null;
    }

    /**
     * @param array|null $value
     *
     * @return array|null
     *
     * @throws \Exception
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function reverseTransform($value)
    {
        if (null === $value || !\is_array($value)) {
            return [];
        }

        $startDate = $value['start_date'] ?? (new DateTime())->setTimestamp(0);
        $endDate = $value['end_date'] ?? new DateTime();
        $endDate->setTime(23, 59, 59);

        return [
            'start_date' => $startDate->getTimestamp(),
            'end_date' => $endDate->getTimestamp(),
        ];
    }
}
