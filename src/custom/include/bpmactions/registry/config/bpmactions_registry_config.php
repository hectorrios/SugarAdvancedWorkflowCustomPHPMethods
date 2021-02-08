<?php

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\awfactions\StarterCustomAction;
use Sugarcrm\Sugarcrm\Logger\Factory;

return [
    "customBPMLogger" => function(ContainerInterface $container) {
        return Factory::getLogger('custombpm'); 
    },
    StarterCustomAction::class => function (ContainerInterface $container) {
        $logger = $container->get("customBPMLogger");
        return new StarterCustomAction($logger);
    },
];