<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0

class AWFCustomAction
{
    public $availableClasses = array();
    public $previousUser;

    /**
     * @param $fqcn string The fully Qualified Class Name to check for.
     * @param $module string the name of the module that the fully-qualified-class-name should
     * be defined under.
     * @return bool
     */
    public function classIsDefined($fqcn, $module)
    {
        if (!empty($fqcn) && !empty($module) && !empty($this->availableClasses[$module])) {
            if (in_array($fqcn, $this->availableClasses[$module])) {
                return true;
            }
        }
        return false;
    }

    public function getAvailableModulesApis()
    {
        if (!empty($this->availableClasses)) {
            return array_keys($this->availableClasses);
        }
    
        return array();
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

        if (!empty($this->availableClasses) && !empty($this->availableClasses[$module])) {
            foreach ($this->availableClasses[$module] as $method) {
                $response['result'][] = array(
                    'value' => $method,
                    'text' => $method,
                );
            }
    
            return $response;            
        }
        
        return array('success' => false);
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

    /**
     * Calls static "execute" method on the passed in $namespacedClass
     * @param SugarBean $b
     * @param string $namespacedClass
     * @param array $additional_info contains the contents that are usually passed in to the run method
     * of PMSERunnable
     * @return bool True if the method was executed and completed without any exceptions. False otherwise.
     */
    public function callCustomLogic(SugarBean $b, $namespacedClass, $additional_info)
    {
        if ($b instanceof SugarBean) {

            // retrieve the original user that initiated the process, if there was a timer to push the action in the background
            // right now there seem to be no way to differentiate if a process is real time if it was sent in the background
            // logic will have to apply to the executing logic, based on the method called

            $override_user = null;
            if (!empty($additional_info['flowData']['cas_id'])) {
                $override_user = $this->retrieveOriginalUserForProcess($additional_info['flowData']['cas_id']);
            }

            if (!$this->classIsDefined($namespacedClass, $b->module_name)) {
                $GLOBALS['log']->fatal("The class exists and is loadable but it was NOT configured for module " .
                    $b->module_name . " Skipping Execution");
                return false;
            }

            //Last check, make sure that the class has an execute method defined
            if (!method_exists($namespacedClass, "execute")) {
                $GLOBALS['log']->fatal("Error confirming the $namespacedClass class has an \"execute\" method defined");
                return false;
            }

            //cleared all hurdles, go for it and run the "execute" method on the class
            try {
                return $namespacedClass::execute($b, $override_user, $additional_info);
            } catch (Exception $e) {
                $GLOBALS['log']->fatal("Error executing the \"execute\" method on $namespacedClass. The error is: " .
                    $e->getMessage());
                return false;
            }
        }

        return false;
    }
}
