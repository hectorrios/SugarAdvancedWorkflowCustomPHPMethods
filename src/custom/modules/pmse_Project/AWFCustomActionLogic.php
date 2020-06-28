<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2017-04-27
// Tested on Sugar 7.8.2.0


// This is an example of a possible skeleton implementation of 3 custom methods for Advanced Workflows
// 2 methods available for Accounts
// 1 method available for Contacts
//
// The method customMethodWithOriginalUserOverride, will retrieve the original user that initiated the process and act as that user on this method.
// This allows the user to put a job in the background using a timer, and still act in behalf of the original user, instead of Admin

SugarAutoLoader::load('custom/modules/pmse_Project/AWFCustomAction.php');

class AWFCustomActionLogic extends AWFCustomAction
{
    public $availableMethods = array(
        'Accounts' => array(
            'customMethodWithOriginalUserOverride',
            'Sugarcrm\\Sugarcrm\\custom\\modules\\pmse_Project\\SampleCustomAction',
        ),
        'Contacts' => array(
            'Sugarcrm\\Sugarcrm\\custom\\modules\\pmse_Project\\SampleCustomAction2',
        )
    );

    /**
     * Only for reference and historical purposes. The re-vamped implementation
     * of the AWF Custom Action method would require a new class to be implemented
     * having a static "execute" method and the detail of the implementation within that
     * class.
     * @param $b
     * @param $user
     * @param $additional_info
     * @return bool
     */
    public function customMethodWithOriginalUserOverride($b, $user, $additional_info)
    {
        // call to impersonate the user
        $this->impersonateUser($user);

        $GLOBALS['log']->fatal('called customMethodWithOriginalUserOverride as '.$GLOBALS['current_user']->id.' originally run by '.$this->previousUser->id);
    
        // call to reset back to original user
        $this->resetUser();

        return true;
    }

}
