<?php

namespace Sugarcrm\Sugarcrm\custom\inc\dependencyinjection;

use DirectoryIterator;
use Psr\Container\ContainerInterface;
use Sugarcrm\Sugarcrm\custom\modules\pmse_Project\AWFCustomActionRegistry;
use UnexpectedValueException;
use UltraLite\Container\Container;
use Psr\Log\LoggerInterface;

class DIContainerConfigImporter
{
    /** @var ContainerInterface */
    private $container;

    /** @var AWFCustomActionRegistry */
    private $registry;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ContainerInterface $container, AWFCustomActionRegistry $registry)
    {
        $this->container = $container;
        $this->registry = $registry;
    }

    /**
     * Will load all custom action configuration files into the
     * DI Container. NOTE: You have to be very careful which directory
     * path is provided. Please make sure that the directory only contains
     * files that need to be loaded into the Container. Anything else might
     * leave the system in an unstable state.
     *
     * @param string $baseDir the path to the directory where we can
     * find all the config files of the flows that need to be loaded
     * into the DI container.
     * @throws Exception if the internal container reference is not an 
     * UltraLite container
     */
    public function load(string $baseDir = "")
    {
        //is the baseDir an actual dir? Use a Directory Iterator.
        //For the moment, let an exception halt Execution!
        $dirIter = new DirectoryIterator($baseDir);
        if (! $dirIter->isDir()) {
            //throw an Exception
            throw new UnexpectedValueException("The directory: " . $baseDir . " is not a directory.");
        }

        //Loop over each file in the directory and pass it to the Container to load.
        foreach ($dirIter as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            //pass the filename to our container
            //log out the Pathname.
            $GLOBALS['log']->debug("Loading AWF custom action configs. The pathname is: " . $fileInfo->getPathname());
            //check if our internal container reference is an Ultra-lite 
            //container and if so, then delegate to it.
            
            $this->configureFromFile($fileInfo->getPathname());
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function configureFromFile($filepath)
    {
        foreach (require $filepath as $serviceKey => $serviceFactory) {
            //register the service key (class) with the Registry which is a separate
            //registration than the registration with the Container
            $this->registry->registerCustomAction($serviceKey);

            //Now register the factory with the container
            if ($this->container instanceof Container) {
                $this->container->set($serviceKey, $serviceFactory);
                return;
            }

            throw new \Exception("The local instance of container needs to be UltraLite/Container");
        }
    }

}