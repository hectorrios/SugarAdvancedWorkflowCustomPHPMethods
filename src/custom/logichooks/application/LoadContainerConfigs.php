<?php

namespace Sugarcrm\Sugarcrm\custom\logichooks\application;

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\dependencyinjection\DIContainerConfigImporter;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Logger\Factory;

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

        /** @var Container */
        $diContainer = Container::getInstance();

        $this->registerBaseLineEntities($diContainer);

        $importer = $diContainer->get(DIContainerConfigImporter::class);
        $importer->load("custom/include/bpmactions/registry/config");

        $GLOBALS['log']->info('Done calling method loadConfigs');
    }


    private function registerBaseLineEntities(Container $diContainer)
    {
        $this->registerLoggerChannel($diContainer);
        $this->registerTheRegistry($diContainer);
        $this->registerTheImporter($diContainer);
    }

    private function registerLoggerChannel(Container $diContainer)
    {
        $diContainer->set("customBPMLogger", function(ContainerInterface $container) {
            return Factory::getLogger('custombpm'); 
        });
    }

    private function registerTheRegistry(Container $diContainer) 
    {
        $diContainer->set(AWFCustomActionRegistry::class, function(ContainerInterface $container) {
            //Throw an error if the Container is not the
            //Ultra-Lite Container
            if (!($container instanceof \UltraLite\Container\Container)) {
                throw new \Exception("Expecting an instance of the Ultra-Lite Container implementation");
            }
            return new AWFCustomActionRegistry(new \Administration(), $container);
        });
    }

    private function registerTheImporter(Container $diContainer)
    {
        $diContainer->set(DIContainerConfigImporter::class, function(ContainerInterface $container) {
            /** @var AWFCustomActionRegistry */
            $registry = $container->get(AWFCustomActionRegistry::class);
            $importer = new DIContainerConfigImporter($container, $registry);
            $importer->setLogger($container->get(LoggerInterface::class));
            
            return $importer;
        });
    }

}
