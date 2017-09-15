<?php

namespace Riverway\Grid\Twig;

use Doctrine\ORM\Query;
use Riverway\Grid\Widget\GridWidget;

class RiverwayGridExtension extends \Twig_Extension
{
    private $widget;

    public function __construct(GridWidget $widget)
    {
        $this->widget = $widget;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFunction('riverway_grid_render', array($this, 'renderGrid')),
        );
    }

    public function renderGrid(\Twig_Environment $env, array $fields, Query $query)
    {
        $this->widget->setFields($fields);
        $this->widget->setQuery($query);
        return $env->render('@RiverwayGrid/grid.html.twig', $this->widget->getGridParams());
    }
}