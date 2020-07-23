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
                //Throw an error if the Container is not the
                //Ultra-Lite Container
                if (!($container instanceof \UltraLite\Container\Container)) {
                    throw new Exception("Expecting an instance of the Ultra-Lite Container implementation");
                }
                return new AWFCustomActionRegistry(new Administration(), $container);
            }
        );
    }
}