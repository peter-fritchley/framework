<?php

namespace Tomahawk\HttpKernel\Test\Bundles\BarBundle;

use Tomahawk\HttpKernel\Bundle\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BarBundle extends Bundle
{

    public function boot()
    {
        $this->container->set('bar_bundle', 'yay!');
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }

}
