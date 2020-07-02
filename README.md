# Work In Progress

# SugarAdvancedWorkflowCustomPHPMethods
SugarCRM's Advanced Workflow custom PHP actions

## Technical Description
The purpose of this customisation is to be able to trigger complex PHP actions leveraging Advanced Workflows.
In order to support both on-premise and SugarCloud, the original version of this customisation has
been re-architected to leverage the [Strategy pattern](https://refactoring.guru/design-patterns/strategy/php/example).

This means that any developed Custom Action PHP *methods* would live separate of this customisation. All PHP custom
actions require implementing the **AWFCustomLogicExecutor** interface and providing a logic hook to register the Executor 
into the Sugar DependencyInjection Container. The coded PHP strategies can be configured from the UI, so that the 
Sugar Administrator can choose what methods to call based on the need. See below for an example of how this works.

**Best Practice Recommendation:**
The suggestion is to leverage custom job queues for PHP intensive methods, and send jobs to the background by using timers wherever possible.
With the implemented functionality it is possible to find out the originating user of the call, so that it is a possibility to act on behalf of that user if required.

## Requirements
* Tested on Sugar Enterprise 10.0.0
* Tested on Linux

## Installation
* Clone the repository and enter the cloned directory
* Modify the required code on `src/custom/modules/pmse_Project/AWFCustomActionLogic.php`
* Retrieve the Sugar Module Packager dependency by running either `composer install`
* Generate the installable .zip Sugar module within the `releases` directory with ``./vendor/bin/package `cat version` ``
* Install the generated module into the instance
* Execute a Repair and Rebuild
* Make sure the browser's cache is purged, so that the Advanced Workflow custom action displays
* Make sure cron is running successfully

## PHP Customisations (This is no longer valid)
The only class that needs to be customised is located on `custom/modules/pmse_Project/AWFCustomActionLogic.php`.
The current version of the class, has 3 sample methods that log a fatal message on the sugarcrm.log file.
Two methods are available for Accounts and one for Contacts.
The method customMethodWithOriginalUserOverride, will retrieve the original user that initiated the process and act as that user, finally it will restore the user.
The implemented functionality allows the user to put a job in the background using a timer, and still act in behalf of the original user, instead of Admin.

## Creating new Strategies
1. **Very important** create a new namespaced class in **custom/include/awfactions** and
make sure that it implements the AWFCustomLogicExecutor interface.
1a. Implement the methods on the interface. The AWFCustomLogicExecutor has two
direct methods and inherits the "run" method from PMSERunnable interface. The 
"run" method is where any custom logic should be placed.
2. Create an "after_entry_point" logic hook
2a. The actual hook class should create an entry for the Executor class into
the DI Container. It is **important** that the key be the FQCN of the class being
instantiated.

## Sample Screenshot
![Advanced Workflow Sample Screenshot](https://raw.githubusercontent.com/esimonetti/SugarAdvancedWorkflowCustomPHPMethods/master/screenshot.png)
