<?php
$installdefs['post_execute'] = array('<basepath>/scripts/post_execute.php');
$installdefs['jsgroups'] = array(
    array(
        'from' => '<basepath>/custom/Extension/application/Ext/JSGroupings/pmsegrouping.php',
        'to_module' => 'application',
    ),
);

$installdefs['language'] = array(
    array(
        'from' => '<basepath>/custom/Extension/application/Ext/Language/en_us.custom_workflow_logic.php',
        'to_module' => 'application',
        'language' => 'en_us'
    ),
);

$installdefs['hookdefs'] = array(
    array(
        'from' => '<basepath>/custom/Extension/application/Ext/LogicHooks/install.LoadContainerConfigs.php',
        'to_module' => 'application',
    ),
);
