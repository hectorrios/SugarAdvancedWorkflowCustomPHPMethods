<?php

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\awfactions\StarterCustomAction;

return [
    StarterCustomAction::class => function (ContainerInterface $container) {
        $logger = $container->get("customBPMLogger");
        return new StarterCustomAction($logger);
    },
];