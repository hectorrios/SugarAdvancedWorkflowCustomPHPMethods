# Work In Progress

# SugarAdvancedWorkflowCustomPHPMethods
SugarCRM's Advanced Workflow custom PHP actions

## Technical Description (Breaking Changes)
**[Breaking Changes]** Customizations developed using the previous version will need to be re-developed as they will no longer work with this version. 

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
* Retrieve the Sugar Module Packager dependency by running either `composer install`
* Generate the installable .zip Sugar module within the `releases` directory with ``./vendor/bin/package `cat version` ``
* Install the generated module into the instance
* Execute a Repair and Rebuild
* Make sure the browser's cache is purged, so that the Advanced Workflow custom action displays
* Make sure cron is running successfully

## The Registry
The registry class *Sugarcrm\Sugarcrm\modules\pmse_Project\AWFCustomActionRegistry*
keeps track of all executors that are registered with it. It 
provides the information to the SugarBPM front-end if there are any executors that
have been configured for a module that can be leveraged for any Process Definitions for that module.
 
## Creating new Strategies
Once this library has been installed, creating new logic that can be leveraged in a SugarBPM Process Definition involves the following two steps.

1. **Very important** create a new namespaced class in **custom/include/awfactions** and
make sure it implements the AWFCustomLogicExecutor interface.
    - Implement the methods on the interface. The AWFCustomLogicExecutor has two
direct methods and inherits the "run" method from PMSERunnable interface. The 
"run" method is where any custom logic should be placed.
2. Create an "after_entry_point" logic hook
    - The actual hook class should create an entry for the Executor class into
the DI Container. It is **important** that the key be the FQCN of the class being
instantiated. See file *custom/logichooks/application/RegisterTheStarterCustomAction.php* 
file for an example.

## Example Strategy
The repo comes with a starter strategy located in **custom/include/awfactions/StarterCustomAction** which
can be used for quick functionality that you'd like to add to test out the library. The starter custom action
class gets registered into the Sugar Dependency Container via an after_entry_point logic hook. Additionally, the class
implements the AWFCustomLogicExecutor interface that was mentioned in the previous section. 

The hook configuration
can be found at **custom/Extension/application/Ext/LogicHooks/install_StarterCustomAction_afterEntryPoint.php**.
The hook class **RegisterTheCustomAction** handles the registration of the Strategy in to the dependency container
via the **AWFCustomActionRegistry** registry class which is also configured into the Container. The registry keeps
track of the custom strategies that have been registered via it's **registerExecutor** method. One important function
it serves is to map the container keys of the strategies so that the registry can know which Strategy should be made
visible during the configuration of a BPM process. The BPM process definition relies on this so that it can store the
container key in its definition.

## Sample Screenshot
![Advanced Workflow Sample Screenshot](https://raw.githubusercontent.com/esimonetti/SugarAdvancedWorkflowCustomPHPMethods/master/screenshot.png)
