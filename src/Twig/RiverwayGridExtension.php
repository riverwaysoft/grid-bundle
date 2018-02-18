<?php

namespace Riverway\Grid\Twig;

use Doctrine\ORM\Query;
use Riverway\Grid\Widget\GridWidget;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class RiverwayGridExtension extends \Twig_Extension
{
    private $widget;
    private $router;
    private $requestStack;

    public function __construct(GridWidget $widget, RouterInterface $router, RequestStack $requestStack)
    {
        $this->widget = $widget;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('riverway_grid_render', array($this, 'renderGrid'),
                array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    public function renderGrid(\Twig_Environment $env)
    {
        $request = $this->requestStack->getMasterRequest();
        $exportUrl =  $this->router->generate($request->get('_route'),
            array_merge($request->query->all(), ['download' => 1]));
        return $env->render('@RiverwayGridBundle/grid.html.twig', array_merge($this->widget->getGridParams(), ['export_url'=>$exportUrl]));
    }
}
