<?php


namespace Sugarcrm\Sugarcrm\custom\logichooks\application;


use Sugarcrm\Sugarcrm\custom\inc\awfactions\StarterCustomAction;

class RegisterTheStarterCustomAction
{
    public function registerAction($event, $arguments)
    {
        $depContainer = Container::getInstance();
        $depContainer->set(
            StarterCustomAction::class,
            function(ContainerInterface $container) {
                //we don't need anything else to wire up
                return new StarterCustomAction();
            }
        );
    }
}