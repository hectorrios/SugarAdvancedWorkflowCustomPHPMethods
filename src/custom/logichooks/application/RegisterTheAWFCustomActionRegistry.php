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
                $actionRegistry = new AWFCustomActionRegistry("custom/include/awfactions",
                    "Sugarcrm\\Sugarcrm\\custom\\inc\\awfactions");
                //initialize the Custom Action Registry
                $actionRegistry->initRegistry();
                return $actionRegistry;
            }
        );
    }
}