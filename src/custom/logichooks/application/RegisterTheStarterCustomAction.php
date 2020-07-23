<?php


namespace Sugarcrm\Sugarcrm\custom\logichooks\application;


use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\awfactions\StarterCustomAction;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class RegisterTheStarterCustomAction
{
    public function registerAction($event, $arguments)
    {
        $depContainer = Container::getInstance();
        /* @var $executorRegistry AWFCustomActionRegistry */
        $executorRegistry = $depContainer->get(AWFCustomActionRegistry::class);
        //register the Container key (e.g. usually the namespace of the class but its not required)
        //with our internal registry
        $executorRegistry->registerExecutor(StarterCustomAction::class,
            function(ContainerInterface $container) {
                //we don't need anything else to wire up
                return new StarterCustomAction();
            }
        );
    }
}