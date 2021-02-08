<?php

namespace Sugarcrm\Sugarcrm\custom\logichooks\application;

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\dependencyinjection\DIContainerConfigImporter;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class LoadContainerConfigs
{

    /**
    *
    * @param $event the after_entry_point event
    * @param $arguments Array containing some arguments
    */
    public function loadConfigs($event, $arguments )
    {
        $GLOBALS['log']->info('Calling method loadConfigs');

        //IMPORTANT: The Registry must be registered into the container first and then the
        //importer 
        $this->registerTheRegistry();
        $this->registerTheImporter();

        /** @var ContainerInterface */
        $diContainer = Container::getInstance();

        $importer = $diContainer->get(DIContainerConfigImporter::class);
        $importer->load("custom/include/bpmactions/registry/config");

        $GLOBALS['log']->info('Done calling method loadConfigs');
    }

    private function registerTheRegistry() 
    {
        $diContainer = Container::getInstance();
        $diContainer->set(AWFCustomActionRegistry::class, function(ContainerInterface $container) {
            //Throw an error if the Container is not the
            //Ultra-Lite Container
            if (!($container instanceof \UltraLite\Container\Container)) {
                throw new \Exception("Expecting an instance of the Ultra-Lite Container implementation");
            }
            return new AWFCustomActionRegistry(new \Administration(), $container);
        });
    }

    private function registerTheImporter()
    {
        $diContainer = Container::getInstance();
        $diContainer->set(DIContainerConfigImporter::class, function(ContainerInterface $container) {
            /** @var AWFCustomActionRegistry */
            $registry = $container->get(AWFCustomActionRegistry::class);
            $importer = new DIContainerConfigImporter($container, $registry);
            $importer->setLogger($container->get(LoggerInterface::class));
            
            return $importer;
        });
    }
}
