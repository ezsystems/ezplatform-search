<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\Search\Form\Type;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Ibexa\Platform\Bundle\Search\Form\Data\SearchData;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as CoreSearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use eZ\Publish\API\Repository\PermissionResolver;

final class SearchType extends AbstractType
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function __construct(
        PermissionResolver $permissionResolver,
        ConfigResolverInterface $configResolver
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', CoreSearchType::class, ['required' => false])
            ->add('page', HiddenType::class, ['empty_data' => 1])
            ->add('limit', HiddenType::class, [
                'empty_data' => $this->configResolver->getParameter('search.pagination.limit'),
            ])
            ->add('content_types', ContentTypeChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('last_modified', DateIntervalType::class)
            ->add('created', DateIntervalType::class)
            ->add('search_in_users', SearchUsersType::class, [
                'property_path' => 'searchUsersData',
                'required' => false,
            ])
            ->add(
                'search_language',
                LanguageChoiceType::class,
                [
                    'required' => false,
                    'multiple' => false,
                    'expanded' => false,
                    'placeholder' => false,
                ]
            )
            ->add('subtree', HiddenType::class, [
                'required' => false,
            ])
        ;

        if ($this->permissionResolver->hasAccess('section', 'view') !== false) {
            $builder->add('section', SectionChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'placeholder' => /** @Desc("Any section") */ 'search.section.any',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'translation_domain' => 'search',
        ]);
    }
}
