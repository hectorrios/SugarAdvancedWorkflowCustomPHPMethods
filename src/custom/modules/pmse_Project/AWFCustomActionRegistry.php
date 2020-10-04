<?php


namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


use Administration;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Logger\Factory;
use Psr\Log\LoggerInterface;

class AWFCustomActionRegistry
{
    const REGISTRY_CATEGORY = "awfCustomExecutors";

    //Collect namespace and module information
    private $registry;

    /* @var $adminConfig Administration */
    private $adminConfig;

    private $isRegistryInitialized = false;

    /* @var $container \UltraLite\Container\Container */
    private $container;

    /* @var $logger Psr\Log\LoggerInterface */
    private $logger;

    /**
     * AWFCustomActionRegistry constructor.
     * @param Administration $adminConfig
     * @param \UltraLite\Container\Container $container
     */
    public function __construct($adminConfig, \UltraLite\Container\Container $container)
    {
        $this->registry = [];
        $this->container = $container;
        $this->adminConfig = $adminConfig;
        //Load up just our executors
        $this->adminConfig->retrieveSettings(self::REGISTRY_CATEGORY);
        
        $this->logger = Factory::getLogger('custombpm');
    }

    public function registerExecutor($containerKey, $callback, $overrideExisting = false)
    {
        //grab just the classname from the possibly namespaced class
        $justTheClassName = $this->getJustTheClassName($containerKey);

        $registerAdmin = $this->adminConfig;

        //Put the callback into the DI Container but first make sure it's not already present.
        if (! ($this->container->has($containerKey))) {
            $this->container->set($containerKey, $callback);
        }

        //If the container Key is already present and overrideExisting is NOT true then we don't
        //need to re-register the key
        $settingsKey = self::REGISTRY_CATEGORY . "_" . $justTheClassName;
        if (array_key_exists($settingsKey, $registerAdmin->settings) && !$overrideExisting) {
            //It's already been registered and we don't want to override it. Just return
            return;
        }

        //Otherwise, lets register the Key
        $registerAdmin->saveSetting(self::REGISTRY_CATEGORY, $justTheClassName, $containerKey);
    }

    public function initRegistry()
    {
        //The idea is that each Custom Executor will have registered itself with the
        //Registry when they are installed. Therefore we need to go and grab them out of the
        //DB now.
        $GLOBALS["log"]->fatal("Initiating the Custom Action Registry");

        $container = Container::getInstance();
        foreach($this->adminConfig->settings as $key => $executorSetting) {
            $this->logger->debug("Processing Admin Config setting: $executorSetting");

            if (!$this->startsWith($key, self::REGISTRY_CATEGORY)) {
                continue;
            }

            if (is_bool($executorSetting)) {
                continue;
            }

            $namespaceClass = $executorSetting;

            if (!$container->has($namespaceClass)) {
                $this->logger->debug("Unable to find a Container entry for $namespaceClass");
                continue;
            }

            /* @var $executorInstance AWFCustomLogicExecutor */
            $executorInstance =  $container->get($namespaceClass);

            if (!($executorInstance instanceof AWFCustomLogicExecutor)) {
                $this->logger->debug("Container entry with key: $namespaceClass does not " .
                    "implement the AWFCustomLogicExecutor interface");
                continue;
            }

            //Grab the supported modules for this executor
            $supportedModules = $executorInstance->getModules();
            //Loop over the modules
            foreach ($supportedModules as $moduleName) {
                if (!array_key_exists($moduleName, $this->registry)) {
                    //add the module to the registry with an empty array
                    $this->registry[$moduleName] = [];
                }

                //Add the namespaced class to the registry under this module
                $this->registry[$moduleName][] = [
                    "label" => $executorInstance->getLabelName(),
                    "containerKey" => $namespaceClass
                ];
            }
        }

        $this->isRegistryInitialized = true;
    }

    public function getCustomActionExecutor($module, $classNamespace)
    {
        if (!$this->isRegistryInitialized) {
            $this->initRegistry();
        }

        //for now, assume that the registry has been initialized.
        if (!array_key_exists($module, $this->registry)) {
            return null;
        }

        $found = false;
        foreach ($this->registry[$module] as $executorEntry) {
            if ($executorEntry["containerKey"] === $classNamespace) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return null;
        }

        return Container::getInstance()->get($classNamespace);
    }

    public function getAvailableModules()
    {
        if (!$this->isRegistryInitialized) {
            $this->initRegistry();
        }

        return array_keys($this->registry);
    }

    /**
     * Fetches the list of AWFCustomLogicExecutor that are defined for the
     * passed in module name. The returning list will be an array of array entries.
     * The inner array will have two keys:
     *  "label" = The result of calling getLabelName on the AWFCustomActionLogicExecutor
     *  "containerKey" = This will be the FQNS (fully-qualified namespace) but
     *      could in theory be some other value. It is the defined Key for the
     *      AWFCustomLogicExecutor instance in the Container.
     * @param string $moduleName The Sugar module name we want the list of
     * AWFCustomLogicExecutor entries for.
     * @return array|mixed
     */
    public function getAvailableExecutorsForModule($moduleName)
    {
        if (!$this->isRegistryInitialized) {
            $this->initRegistry();
        }

        if (!array_key_exists($moduleName, $this->registry)) {
            return [];
        }

        return $this->registry[$moduleName];
    }

    private function getJustTheClassName($namespacedClass)
    {
        //split it up
        $parts = explode("\\", $namespacedClass);
        return array_pop($parts);
    }

    private function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

}