<?php


namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


class SampleCustomAction2
{
    public static function execute($b, $override_user, $additional_info)
    {
        $GLOBALS['log']->fatal('static execute on SampleCustomAction2 is running');
    }
}