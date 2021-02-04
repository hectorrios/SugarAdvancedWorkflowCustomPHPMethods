<?php

$hook_version = 1;

$hook_array['after_entry_point'][] = array(
    5, //Integer
    'Load custom DI Container config files', //String
    null, //String or null if using namespaces
    'Sugarcrm\\Sugarcrm\\custom\\logichooks\\application\\LoadContainerConfigs', //String
    'loadConfigs', //String
);
