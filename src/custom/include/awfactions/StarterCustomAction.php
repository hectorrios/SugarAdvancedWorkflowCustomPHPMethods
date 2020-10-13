<?php


namespace Sugarcrm\Sugarcrm\custom\inc\awfactions;

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomLogicExecutor;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\ContainerRegisterAction;
use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\Logger\Factory;

class StarterCustomAction extends ContainerRegisterAction implements AWFCustomLogicExecutor
{
    public function __construct(LoggerInterface $logger = null)
    {
        if (empty($logger)) {
            $this->setLogger();
        } else {
            $this->setLogger($logger);
        }
    }

    public function getModules()
    {
        //return an array of modules this action will be made
        //available to in the SugarBPM designer

        //For example, this action will be available to any
        //process definitions defined for the modules below.
        return ["Accounts", "Contacts", "Leads"];
    }

    public function getLabelName()
    {
        //The user friendly label that will be shown in the
        //designer
        return "Custom Starter Action";
    }

    public function run($flowData, $bean, $externalAction, $arguments)
    {
        //The business logic you want to execute when the custom
        //element that has this Action configured executes.
        $this->logger->debug("Executing the Custom Starter Action");
    }

    public function initializeNewClassInstance(ContainerInterface $container)
    {
        return new StarterCustomAction();        
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        //if no LoggerInterface is present, then we will
        //fallback to the Default channel
        if (empty($logger)) {
            $logger = Factory::getLogger('custombpm');
        }

        $this->logger = $logger;
    }

}