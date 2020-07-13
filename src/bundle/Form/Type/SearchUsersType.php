<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\Search\Form\Type;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use Ibexa\Platform\Bundle\Search\Form\Data\SearchUsersData;
use Ibexa\Platform\Bundle\Search\Form\DataTransformer\UsersTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchUsersType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new UsersTransformer(
                $this->repository,
                $this->searchService,
                $this->userContentTypeIdentifier
            )
        );
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchUsersData::class,
        ]);
    }
}
