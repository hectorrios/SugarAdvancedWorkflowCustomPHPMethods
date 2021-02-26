# Work In Progress

# SugarAdvancedWorkflowCustomPHPMethods
SugarCRM SugarBPM custom PHP actions

## Technical Description (Breaking Changes)
**[Breaking Changes]** Customizations developed using the previous version will need to be re-developed as they will no longer work with this version. 

The purpose of this customisation is to be able to trigger complex PHP actions leveraging SugarBPM.
It does this by exposing a generic BPM Action component that allows a BPM flow designer to add custom backend functionality into a BPM flow. In the screenshot below, custom functionality has been introduced 
into sample flow at two points.

![Advanced Workflow Sample Screenshot](https://raw.githubusercontent.com/esimonetti/SugarAdvancedWorkflowCustomPHPMethods/master/screenshot.png)


## Works on Sugar Cloud
In order to support both on-premise and SugarCloud, the original version of this customisation has
been re-architected because the use of dynamic class instantiation is not allowed in SugarCloud. 
This means that any previously developed Custom Action PHP *methods* need to be reimplemented in a separtate module-loadable-package. 

## Requirements
* Tested on Sugar Enterprise 10.0.0
* Tested on Linux

## Installation
* Clone the repository and enter the cloned directory
* Retrieve the Sugar Module Packager dependency by running either `composer install`
* Generate the installable .zip Sugar module within the `releases` directory with ``./vendor/bin/package `cat version` ``
* Install the generated module-loadable-package into the instance
* Execute a Repair and Rebuild
* Make sure the browser's cache is purged, so that the Advanced Workflow custom action displays
* Make sure cron is running successfully

## Quick check to make sure installation worked
Sometimes, because of cached files, the installation might appear to not have worked. Primarily this will apply to the custom javascript that is run within the SugarBPM designer. The module-loadable-package extends the SugarBPM class
*AdamActivity* prototype to add an array called *customWorkflowActionModules* and this array is populated once a user logs into Sugar. By default, the array will have the modules "Accounts", "Contacts", and "Leads". As a troubleshooting tip, the array can be examined to make sure it's been populated. Using Chrome DevTools console to examine the *AdamActivity* prototype you can verify if the array is present and if it contains any values.


![AdamActivity Prototype](https://raw.githubusercontent.com/hectorrios/SugarAdvancedWorkflowCustomPHPMethods/add_custom_logic_executor_interface/prototype.png)

## How it works
Once installed, the customization will inform the BPM Designer if there is custom functionality 
available for the current BPM flow module. If so, then an extra option will 
be available on the *Action Type* context menu. It will appear at the bottom and will read 
"Call Custom Logic".

![Call Custom Logic menu](https://raw.githubusercontent.com/hectorrios/SugarAdvancedWorkflowCustomPHPMethods/add_custom_logic_executor_interface/call_custom_logic_menu.png)

Selecting the "Call Custom Logic" option will enable a dropdown that will be visible in the "Settings" context menu. The dropdown will have a list of custom Actions that are available to this Action BPM element.

![Call Custom Settings](https://raw.githubusercontent.com/hectorrios/SugarAdvancedWorkflowCustomPHPMethods/add_custom_logic_executor_interface/custom_action_dropdown.png)

## Creating new custom actions
Once this library has been installed, creating new logic that can be leveraged in a SugarBPM Process Definition involves the following two steps.

1. Create a new namespaced class and
make sure it implements the **AWFCustomLogicExecutor** interface.
    - Implement the methods on the interface. The AWFCustomLogicExecutor has two
direct methods and inherits the "run" method from PMSERunnable interface. The 
"run" method is where custom logic should be placed.
2. Create the DI Container config file. It MUST be placed in the **custom/include/bpmactions/registry/config**
directory. The array contains the instantiation logic for the custom action. Since dynamically 
instantiating classes is not supported in SugarCloud, we leverage the DI Container that ships with Sugar and let it 
instantiate classes for us. However, we need to tell the Container how it should instantiate the class. 
**Very Important** The key for the custom action must be the fully qualified class name.
Below is an example from the included *StarterCustomAction* registry file.
```php
<?php

use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\inc\awfactions\StarterCustomAction;

return [
    StarterCustomAction::class => function (ContainerInterface $container) {
        $logger = $container->get("customBPMLogger");
        return new StarterCustomAction($logger);
    },
];
```
**Best Practice Recommendation:**
The suggestion is to leverage custom job queues for PHP intensive methods, and send jobs to the background by using timers wherever possible.
With the implemented functionality it is possible to find out the originating user of the call, so that it is a possibility to act on behalf of that user if required.

## Default custom action
The repo comes with a starter custom action located in **custom/include/awfactions/StarterCustomAction** which
can be used for quick functionality that you'd like to add to test out the library. The starter custom action
class gets registered into the Sugar Dependency Container via an after_entry_point logic hook. 
Additionally, the class implements the **AWFCustomLogicExecutor** interface that was mentioned in 
the previous section. 

## Logging
In order to help with developing any custom BPM actions, a dedicated PSR-3 logger has been
configured. The channel name is **custombpm** and default logging level has been set to **info**. Once
installed, the logging level can be adjusted in the **config_override.php** file.
It has also been registered into the DI Container so that it is available to any custom actions. In the
code block above, the **Starter Action** is injected with the Logger when it is instantiated by the Container.
The key for the Logger is **customBPMLogger**

## The Registry
The registry class *Sugarcrm\Sugarcrm\modules\pmse_Project\AWFCustomActionRegistry*
keeps track of all actions that are registered with it. It provides the information to the SugarBPM \
designer if there are any custom actions that have been configured for a module that can be
leveraged for any BPM flow definitions for that module.



