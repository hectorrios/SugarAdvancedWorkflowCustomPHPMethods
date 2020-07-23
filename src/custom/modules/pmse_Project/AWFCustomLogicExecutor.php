<?php


namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


use PMSERunnable;

interface AWFCustomLogicExecutor extends PMSERunnable
{
    /**
     * The modules that are supported for this Executor. The concrete Executor
     * will be made visible to a BPM definition if the BPM Module is part of this list.
     *
     * @return array of module names that are supported by the concrete Executor
     */
    public function getModules();

    /**
     * The readable name for this Executor that will be displayed to the BPM definition designer when configuring
     * the @see \PMSECallCustomLogic element.
     *
     * @return string
     */
    public function getLabelName();
}