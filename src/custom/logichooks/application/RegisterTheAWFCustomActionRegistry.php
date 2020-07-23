<?php

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class RegisterTheAWFCustomActionRegistry
{
    public function registerRegistry($event, $arguments)
    {
        $depContainer = Container::getInstance();
        $depContainer->set(
            AWFCustomActionRegistry::class,
            function(ContainerInterface $container) {
                //we don't need anything else to wire up
                return new AWFCustomActionRegistry(new Administration());
            }
        );
    }
}