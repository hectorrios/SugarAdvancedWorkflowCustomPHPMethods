<?php


namespace Sugarcrm\Sugarcrm\custom\inc\awfactions;


use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomLogicExecutor;

class StarterCustomAction implements AWFCustomLogicExecutor
{
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
        $GLOBALS['log']->fatal("Executing the Custom Starter Action");
    }

}