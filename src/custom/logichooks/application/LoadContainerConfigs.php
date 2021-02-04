<?php

namespace Sugarcrm\Sugarcrm\custom\logichooks\application;

use Sugarcrm\Sugarcrm\custom\inc\dependencyinjection\DIContainerConfigImporter;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class LoadContainerConfigs
{

    /**
    *
    * @param $event the after_entry_point event
    * @param $arguments Array containing some arguments
    */
    function loadConfigs($event, $arguments )
    {
        $GLOBALS['log']->info('Calling method loadConfigs');
        $diContainer = Container::getInstance();
        $importer = new DIContainerConfigImporter($diContainer);
        $importer->load("custom/include/bpmactions/registry/config");
        $GLOBALS['log']->info('Done calling method loadConfigs');
    }
}
