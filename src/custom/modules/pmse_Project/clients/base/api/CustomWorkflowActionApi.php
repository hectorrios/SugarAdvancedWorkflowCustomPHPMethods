<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0

use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomAction;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

SugarAutoLoader::load('custom/modules/pmse_Project/AWFCustomAction.php');

class CustomWorkflowActionApi extends SugarApi
{
    public function registerApiRest() {
        return array(
            array(
                'reqType' => 'GET',
                'path' => array('customv1', 'pmse_Project', 'CrmData', 'customWorkflowActions'),
                'pathVars' => array('', '', '', ''),
                //set authentication
                //'noLoginRequired' => true,
                'method' => 'getAvailableModulesApis',
                'shortHelp' => 'customv1/pmse_Project/CrmData/customWorkflowActions',
            ),
            array(
                'reqType' => 'GET',
                'path' => array('customv1', 'pmse_Project', 'CrmData', 'customWorkflowActions', '?'),
                'pathVars' => array('', '', '', '', 'module'),
                'method' => 'getAvailableApis',
                'shortHelp' => 'customv1/pmse_Project/CrmData/customWorkflowActions/:module',
            ),
        );
    }

    public function getAvailableModulesApis($api, $args) {
        //If we can't find the Service then the uncaught Exception
        //will bubble up but we'll also put something in the log to help
        //with troubleshooting issues as this will occur from the BPM designer.        
        try {
            /** @var AWFCustomAction */
            $awfService = Container::getInstance()->get(AWFCustomAction::class);
        }catch(Exception $e) {
            $GLOBALS['log']->fatal("Custom BPM Actions error. " . $e->getMessage());
            throw $e;
        }

        return $awfService->getAvailableModulesApis();
    }

    public function getAvailableApis($api, $args) {
        if(empty($args['module'])) {
            return array('success' => false);
        }

        $executorRegistry = Container::getInstance()->get(AWFCustomActionRegistry::class);
        $awfService = new AWFCustomAction($executorRegistry);
        return $awfService->getAvailableApis($args['module']);
    }

}
