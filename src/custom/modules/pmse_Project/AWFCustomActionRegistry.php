<?php


namespace Sugarcrm\Sugarcrm\custom\modules\pmse_Project;


use SugarAutoLoader;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class AWFCustomActionRegistry
{
    //Collect namespace and module information
    private $registry;

    /* @var $actionDir string */
    private $actionDir;

    private $namespace;

    /**
     * AWFCustomActionRegistry constructor.
     * @param string $actionDir The relative directory where the Custom Actions are located and which
     * the registry will scan from
     * @param string $namespace The namespace corresponds to the actionDir parameter
     */
    public function __construct($actionDir, $namespace)
    {
        $this->registry = [];
        $this->actionDir = $actionDir;
        $this->namespace = $namespace;
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

    public function getCustomActionExecutor($module, $classNamespace)
    {
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
        if (!array_key_exists($moduleName, $this->registry)) {
            return [];
        }

        return $this->registry[$moduleName];
    }

    private function constructNameSpace($baseClassName)
    {
        return $this->namespace . "\\" . $baseClassName;
    }
}