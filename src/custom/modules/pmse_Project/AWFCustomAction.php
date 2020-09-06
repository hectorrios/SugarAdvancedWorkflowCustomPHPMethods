<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0

namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;

use BeanFactory;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomLogicExecutor;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use SugarQuery;

class AWFCustomAction
{
    public $previousUser;

    /* @var $executorRegistry AWFCustomActionRegistry */
    private $executorRegistry;

    /**
     * AWFCustomAction constructor.
     * @param AWFCustomActionRegistry $executorRegistry
     */
    public function __construct(AWFCustomActionRegistry $executorRegistry)
    {
        $this->executorRegistry = $executorRegistry;
    }

    /**
     * Fetch the list of module names that have
     * custom action executors registered against them
     */
    public function getAvailableModulesApis()
    {
        //Use the Registry to fetch the list of modules
        return $this->executorRegistry->getAvailableModules();
    }

    public function getAvailableApis($module = null)
    {
        if (empty($module)) {
            return array('success' => false);
        }

        $response = array(
            'success' => true,
            'result' => array(),
        );

        //Use the registry to get the list of Executors for this module
        $executorEntries = $this->executorRegistry->getAvailableExecutorsForModule($module);
        foreach ($executorEntries as $executorEntry) {
            $response["result"][] = [
                'value' => $executorEntry["containerKey"],
                'text' => $executorEntry["label"],
            ];
        }

        return $response;
    }

    public function impersonateUser($user)
    {
        global $current_user;
        if (!empty($user) && ($current_user->id !== $user->id)) {
            // backup current user
            $this->previousUser = clone($current_user);
            $current_user = clone($user);
        }
    }

    public function resetUser()
    {
        global $current_user;
        if (!empty($this->previousUser->id)) {
            // restore current user with previous user
            $current_user = clone($this->previousUser);
            $this->previousUser = null;
        }
    }

    public function retrieveOriginalUserForProcess($cas_id = null)
    {
        if (!empty($cas_id)) {
            // retrieve the first flow record of this process run
            $sugarQuery = new SugarQuery();
            $sugarQuery->from(BeanFactory::newBean('pmse_BpmFlow'));
            $sugarQuery->select(array('id', 'created_by'));
            $sugarQuery->where()->equals('cas_id', $cas_id);
            $sugarQuery->where()->equals('cas_index', '1');
            $sugarQuery->limit(1);
            $records = $sugarQuery->execute();

            if (!empty($records) && !empty($records['0']) && !empty($records['0']['created_by'])) {
                $override_user = BeanFactory::getBean('Users', $records['0']['created_by']);
                if (!empty($override_user->id) && $override_user->id !== '1') {
                    return $override_user;
                }
            }
        }

        return false;
    }
}
