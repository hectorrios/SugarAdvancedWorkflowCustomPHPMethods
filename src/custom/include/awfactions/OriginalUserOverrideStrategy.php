<?php

namespace Sugarcrm\Sugarcrm\custom\inc\awfactions;

use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomLogicExecutor;
use BeanFactory;
use SugarQuery;
use Sugarcrm\Sugarcrm\Logger\Factory;
use Psr\Log\LoggerInterface;


/**
 * Class OriginalUserOverrideStrategy is a decorator for other AWFCustomLogicExecutor implementations
 * that need to retrieve the original user that initiated the process and act as that user,
 * finally it will restore the user.
 *
 * The use case for this decorator are situations where the Executor is relegated to a background asynchronous
 * task which means that the user used is the system user and the requirement calls for the original user to be
 * used.
 * @package Sugarcrm\Sugarcrm\custom\inc\awfactions
 */
class OriginalUserOverrideStrategy implements AWFCustomLogicExecutor
{
    private $previousUser;
    /**
     * @var AWFCustomLogicExecutor
     */
    private $executor;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(AWFCustomLogicExecutor $executorToDecorate = null)
    {
        $this->executor = $executorToDecorate;
        //initialize the logger
        $logger = Factory::getLogger('custombpm');
    }

    /**
     * @inheritDoc
     */
    public function getModules()
    {
        return $this->executor->getModules();
    }

    /**
     * @inheritDoc
     */
    public function getLabelName()
    {
        return $this->executor->getLabelName();
    }

    /**
     * @inheritDoc
     * From PMSERunnable
     */
    public function run($flowData, $bean, $externalAction, $arguments)
    {
        if (empty($flowData['cas_id'])) {
            $this->logger->alert("key cas_id is not present on the flowData array");
            return;
        }

        $overrideUser = $this->retrieveOriginalUserForProcess($flowData['cas_id']);
        if (empty($overrideUser)) {
            // call to impersonate the user
            $this->impersonateUser($overrideUser);
        }

        $this->logger->debug('called customMethodWithOriginalUserOverride as '
            . $GLOBALS['current_user']->id . ' originally run by ' . $this->previousUser->id);

        /*
         * The original user has now been set as the current user in the GLOBAL $current_user variable.
         */
        if (!empty($this->executor)) {
            $this->executor->run($flowData, $bean, $externalAction, $arguments);
        }

        // call to reset back to original user
        $this->resetUser();

        return;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        //if no LoggerInterface is provided, then stick with logger configured
        //when the class instance was initialized.
        if (empty($logger)) {
            return;
        }

        $this->logger = $logger;
    }

    private function impersonateUser($user)
    {
        global $current_user;
        if (!empty($user) && ($current_user->id !== $user->id)) {
            // backup current user
            $this->previousUser = clone($current_user);
            $current_user = clone($user);
        }
    }

    private function retrieveOriginalUserForProcess($cas_id = null)
    {
        if (empty($cas_id)) {
            return null;
        }

        // retrieve the first flow record of this process run
        $sugarQuery = new SugarQuery();
        $sugarQuery->from(BeanFactory::newBean('pmse_BpmFlow'));
        $sugarQuery->select(array('id', 'created_by'));
        $sugarQuery->where()->equals('cas_id', $cas_id);
        $sugarQuery->where()->equals('cas_index', '1');
        $sugarQuery->limit(1);
        $records = $sugarQuery->execute();
        
        if (empty($records) || empty($records['0']) || empty($records['0']['created_by'])) {
            $this->logger->debug("OriginalUserOverrideStrategy: No results were returned back from the Query");
            return null;
        }
        
        $override_user = BeanFactory::getBean('Users', $records['0']['created_by']);
        if (empty($override_user->id)) {
            $this->logger->debug("OriginalUserOverrideStrategy: No user was found for user with id: "
                . $records['0']['created_by']);
            return null;
        }

        if ($override_user->id === '1') {
            //if the user that we've searched for is the Admin then forget about it
            $this->logger->debug("OriginalUserOverrideStrategy: The user we are looking for is the Admin, skip it");
            return null;
        } 

        return $override_user;
    }

    private function resetUser()
    {
        global $current_user;
        if (!empty($this->previousUser->id)) {
            // restore current user with previous user
            $current_user = clone($this->previousUser);
            $this->previousUser = null;
        }
    }
}