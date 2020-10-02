<?php

namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

//TODO ContainerRegisterActionHook
abstract class ContainerRegisterAction
{
    public function registerInContainer($event, $arguments)
    {
        $depContainer = Container::getInstance();
        /* @var $executorRegistry AWFCustomActionRegistry */
        $executorRegistry = $depContainer->get(AWFCustomActionRegistry::class);
        //register the Container key (e.g. usually the namespace of the class but its not required)
        //with our internal registry
        $clazzInstance = $this->initializeNewClassInstance($depContainer);
        $executorRegistry->registerExecutor(get_class(),
            function(ContainerInterface $container) use($clazzInstance) {
                //we don't need anything else to wire up
                return $clazzInstance;
            }
        );

    }

    public abstract function initializeNewClassInstance(ContainerInterface $container);
}