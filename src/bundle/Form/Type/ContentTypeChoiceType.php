<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\Search\Form\Type;

use eZ\Publish\API\Repository\ContentTypeService;
use Ibexa\Platform\Bundle\Search\Form\ChoiceLoader\ContentTypeChoiceLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeChoiceType extends AbstractType
{
    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \Ibexa\Platform\Bundle\Search\Form\ChoiceLoader\ContentTypeChoiceLoader */
    private $contentTypeChoiceLoader;

    public function __construct(
        ContentTypeService $contentTypeService,
        ContentTypeChoiceLoader $contentTypeChoiceLoader
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentTypeChoiceLoader = $contentTypeChoiceLoader;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choice_loader' => $this->contentTypeChoiceLoader,
                'choice_label' => 'name',
                'choice_name' => 'identifier',
                'choice_value' => 'identifier',
            ]);
    }
}
