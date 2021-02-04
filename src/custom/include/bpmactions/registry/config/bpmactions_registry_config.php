<?php

use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Psr\Container\ContainerInterface;

return [
    AWFCustomActionRegistry::class => function(ContainerInterface $container) {
        //Throw an error if the Container is not the
        //Ultra-Lite Container
        if (!($container instanceof \UltraLite\Container\Container)) {
            throw new Exception("Expecting an instance of the Ultra-Lite Container implementation");
        }
        return new AWFCustomActionRegistry(new Administration(), $container);
    },
];