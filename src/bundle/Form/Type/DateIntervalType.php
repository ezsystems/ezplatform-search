<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\SearchBundle\Form\Type;

use Ibexa\Platform\Bundle\SearchBundle\Form\DataTransformer\DateIntervalTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType as BaseDateIntervalType;

class DateIntervalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_interval', BaseDateIntervalType::class, [
                'attr' => ['hidden' => true],
                'input' => 'string',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('end_date', IntegerType::class, [
                'attr' => ['hidden' => true],
                'required' => false,
            ])
            ->addModelTransformer(new DateIntervalTransformer());
    }
}
