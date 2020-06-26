<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0

SugarAutoLoader::load('custom/modules/pmse_Project/AWFCustomActionLogic.php');
SugarAutoLoader::load('modules/pmse_Inbox/engine/PMSEElements/PMSEScriptTask.php');

class PMSECallCustomLogic extends PMSEScriptTask
{
    public function run($flowData, $bean = null, $externalAction = '', $arguments = array())
    {
        // retrieve flow settings
        $bpmnElement = $this->retrieveDefinitionData($flowData['bpmn_id']);

        $AWFCustomActionLogic = new AWFCustomActionLogic();     

        $namespacedClassFile = $bpmnElement['act_service_method'];
        if (!empty($namespacedClassFile)) {
            $AWFCustomActionLogic->callCustomLogic($bean, $namespacedClassFile, array(
                'flowData' => $flowData,
                'bpmnElement' => $bpmnElement,
                'externalAction' => $externalAction,
                'arguments' => $arguments
            ));
        }

        $flowAction = $externalAction === 'RESUME_EXECUTION' ? 'UPDATE' : 'CREATE';

        return $this->prepareResponse($flowData, 'ROUTE', $flowAction);
    }
}
