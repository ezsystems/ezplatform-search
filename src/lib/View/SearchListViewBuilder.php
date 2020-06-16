<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Platform\Search\View;

use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

class SearchListViewBuilder implements ViewBuilder
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator */
    private $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector */
    private $viewParametersInjector;

    public function __construct(
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector
    ) {
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
    }

    public function matches($argument): bool
    {
        return 'Ibexa\Platform\Bundle\SearchBundle\Controller\SearchController::searchAction' === $argument;
    }

    public function buildView(array $parameters): SearchListView
    {
        $view = new SearchListView();

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        return $view;
    }
}
