<?php

namespace Sugarcrm\Sugarcrm\custom\logichooks\application;

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\dependencyinjection\DIContainerConfigImporter;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\DependencyInjection\Container as ContainerSingleton;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomAction;
use Sugarcrm\Sugarcrm\Logger\Factory;
use UltraLite\Container\Container;

class LoadContainerConfigs
{
    private const CUSTOM_ACTION_CONFIG_DIR = "custom/include/bpmactions/registry/config";
    /**
    * loadConfigs is an after_entry_point logic hook that levergages the
    * DI Container to wireup all baseline entities (Registry, Logger Channel, and Importer)
    * and uses the Importer to load any Custom Actions that are present in the 
    * custom/include/bpmactions/registry/config directory.

    * @param $event the after_entry_point event
    * @param $arguments Array containing some arguments
    */
    public function loadConfigs($event, $arguments )
    {
        $GLOBALS['log']->info('Calling method loadConfigs');

        /** @var Container */
        $diContainer = ContainerSingleton::getInstance();

        $this->registerBaseLineEntities($diContainer);

        $importer = $diContainer->get(DIContainerConfigImporter::class);
        $importer->load(self::CUSTOM_ACTION_CONFIG_DIR);

        $GLOBALS['log']->info('Done calling method loadConfigs');
    }


    private function registerBaseLineEntities(Container $diContainer)
    {
        $this->registerLoggerChannel($diContainer);
        $this->registerTheRegistry($diContainer);
        $this->registerTheImporter($diContainer);
        $this->registerTheAWFCustomAction($diContainer);
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

            $logger = $container->get("customBPMLogger");
            return new AWFCustomActionRegistry(new \Administration(), $container, $logger);
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

    private function registerTheAWFCustomAction(Container $diContainer)
    {
        $diContainer->set(AWFCustomAction::class, function(ContainerInterface $container) {
            $caRegistry = $container->get(AWFCustomActionRegistry::class);
            $awfCustomAction = new AWFCustomAction($caRegistry);
            return $awfCustomAction;
        });
    }

}
