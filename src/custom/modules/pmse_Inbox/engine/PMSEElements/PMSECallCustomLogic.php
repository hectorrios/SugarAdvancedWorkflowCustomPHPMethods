<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0

use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomLogicExecutor;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

SugarAutoLoader::load('custom/modules/pmse_Project/AWFCustomActionLogic.php');
SugarAutoLoader::load('modules/pmse_Inbox/engine/PMSEElements/PMSEScriptTask.php');

class PMSECallCustomLogic extends PMSEScriptTask
{
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        /* @var $bean SugarBean */

        // retrieve flow settings
        $bpmnElement = $this->retrieveDefinitionData($flowData['bpmn_id']);

        //$AWFCustomActionLogic = new AWFCustomActionLogic();

        $classFile = $bpmnElement['act_service_method'];

        $executor = $this->getExecutor($classFile, $bean->getModuleName());

        if (!empty($executor)) {
            //Run it
           $executor->run($flowData, $bean, $externalAction, $arguments);
        }

        //==================
        //if (!empty($method)) {
        //    $AWFCustomActionLogic->callCustomLogic($bean, $method, array(
        //        'flowData' => $flowData,
        //        'bpmnElement' => $bpmnElement,
        //        'externalAction' => $externalAction,
        //        'arguments' => $arguments
        //    ));
        //}

        $flowAction = $externalAction === 'RESUME_EXECUTION' ? 'UPDATE' : 'CREATE';

        return $this->prepareResponse($flowData, 'ROUTE', $flowAction);
    }

    private function getExecutor($executorKey, $moduleName)
    {
        if (empty($executorKey)) {
            return null;
        }

        $depContainer = Container::getInstance();

        //Grab the registry
        /* @var $executorRegistry AWFCustomActionRegistry */
        $executorRegistry = $depContainer->get(AWFCustomActionRegistry::class);

        if(empty($executorRegistry)) {
            $GLOBALS['log']->fatal("Could not fetch the Registry...Skipping Execution");
            return null;
        }

        $executor = $executorRegistry->getCustomActionExecutor($moduleName, $executorKey);
        if (empty($executor) || !($executor instanceof AWFCustomLogicExecutor)) {
            $GLOBALS['log']->fatal("Could not locate the Executor...Skipping Execution");
            return null;
        }

        return $executor;
    }
}
