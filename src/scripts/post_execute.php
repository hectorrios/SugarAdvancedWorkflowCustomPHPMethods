<?php

require_once "modules/Configurator/Configurator.php";

//Define a new Logger channel for this customization and potentially
//for other customization relying on this package
$configuratorObj = new Configurator();
$configuratorObj->loadConfig();
//['logger']['channels']['channel1']['level']
$configuratorObj->config['logger']['channels']['custombpm']['level'] = 'debug';
$configuratorObj->saveConfig();

// outputting manual repair info
echo '<br/><br/><strong>Please proceed with a <a href="index.php?module=Administration&amp;action=repair">Quick Repair and Rebuild</a> to complete the installation</strong><br/><br/>';
