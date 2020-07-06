<?php


namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


use SugarAutoLoader;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class AWFCustomActionRegistry
{
    const REGISTRY_CATEGORY = "awfCustomExecutors";

    //Collect namespace and module information
    private $registry;

    /* @var $actionDir string */
    private $actionDir;

    private $namespace;

    /* @var $adminConfig \Administration */
    private $adminConfig;

    private $isRegistryInitialized = false;

    /**
     * AWFCustomActionRegistry constructor.
     * @param string $actionDir The relative directory where the Custom Actions are located and which
     * the registry will scan from
     * @param string $namespace The namespace corresponds to the actionDir parameter
     * @param \Administration $adminConfig
     */
    public function __construct($actionDir, $namespace, $adminConfig)
    {
        $this->registry = [];
        $this->actionDir = $actionDir;
        $this->namespace = $namespace;
        $this->adminConfig = $adminConfig;
        //Load up just our executors
        $this->adminConfig->retrieveSettings(self::REGISTRY_CATEGORY);
    }

    public function initRegistry()
    {

        $GLOBALS["log"]->fatal("Initiating the Custom Action Registry");

        $loadedFiles = SugarAutoLoader::scanDir($this->actionDir);
        $GLOBALS["log"]->fatal("The loaded files is: " . print_r($loadedFiles, true));

        $container = Container::getInstance();
        //Grab file names from the container
        foreach ($loadedFiles as $loadedFile => $level) {
            $GLOBALS["log"]->fatal("Looking for loaded file: $loadedFile");
            $baseFile = basename($loadedFile, ".php");
            $namespaceClass = $this->constructNameSpace($baseFile);

            if (!$container->has($namespaceClass)) {
                $GLOBALS['log']->fatal("Unable to find a Container entry for $namespaceClass");
                continue;
            }

            if (!($container->get($namespaceClass) instanceof AWFCustomLogicExecutor)) {
                $GLOBALS['log']->fatal("Container entry with key: $namespaceClass does not " .
                    "implement the AWFCustomLogicExecutor interface");
                continue;
            }

            /* @var $executorInstance AWFCustomLogicExecutor */
            $executorInstance =  $container->get($namespaceClass);

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
    }

    public function registerExecutor($containerKey, $overrideExisting = false)
    {
        //grab just the classname from the possibly namespaced class
        $justTheClassName = $this->getJustTheClassName($containerKey);

        $registerAdmin = $this->adminConfig;

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

    public function initRegistryV2()
    {
        //The idea is that each Custom Executor will have registered itself with the
        //Registry when they are installed. Therefore we need to go and grab them out of the
        //DB now.
        $GLOBALS["log"]->fatal("Initiating the Custom Action Registry");

        $container = Container::getInstance();
        foreach($this->adminConfig->settings as $key => $executorSetting) {
            $GLOBALS['log']->fatal("Processing Admin Config setting: $executorSetting");

            if (!$this->startsWith($key, self::REGISTRY_CATEGORY)) {
                continue;
            }

            if (is_bool($executorSetting)) {
                continue;
            }

            $namespaceClass = $executorSetting;

            if (!$container->has($namespaceClass)) {
                $GLOBALS['log']->fatal("Unable to find a Container entry for $namespaceClass");
                continue;
            }

            /* @var $executorInstance AWFCustomLogicExecutor */
            $executorInstance =  $container->get($namespaceClass);

            if (!($executorInstance instanceof AWFCustomLogicExecutor)) {
                $GLOBALS['log']->fatal("Container entry with key: $namespaceClass does not " .
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
            $this->initRegistryV2();
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
            $this->initRegistryV2();
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
            $this->initRegistryV2();
        }

        if (!array_key_exists($moduleName, $this->registry)) {
            return [];
        }

        return $this->registry[$moduleName];
    }

    private function constructNameSpace($baseClassName)
    {
        return $this->namespace . "\\" . $baseClassName;
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