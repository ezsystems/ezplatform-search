<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Bundle\SearchBundle\DependencyInjection\Compiler;

use Ibexa\Platform\Search\View\SearchViewBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ViewBuilderRegistryPass implements CompilerPassInterface
{
    public const VIEW_BUILDER_REGISTRY = 'ezpublish.view_builder.registry';
    public const VIEW_BUILDER_UPDATE_VIEW = SearchViewBuilder::class;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::VIEW_BUILDER_REGISTRY)) {
            return;
        }

        if (!$container->hasDefinition(self::VIEW_BUILDER_UPDATE_VIEW)) {
            return;
        }

        $registry = $container->findDefinition(self::VIEW_BUILDER_REGISTRY);
        $registry->addMethodCall('addToRegistry', [
            [$container->getDefinition(self::VIEW_BUILDER_UPDATE_VIEW)],
        ]);
    }
}
