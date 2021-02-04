<?php

namespace Sugarcrm\Sugarcrm\custom\inc\dependencyinjection;

use DirectoryIterator;
use SugarAutoLoader;
use UltraLite\Container\Container;
use UnexpectedValueException;

class DIContainerConfigImporter
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Will load all RuleEngine flow configuration files into the
     * DI Container. NOTE: You have to be very careful which directory
     * path is provided. Please make sure that the directory only contains
     * files that need to be loaded into the Container. Anything else might
     * leave the system in an unstable state.
     *
     * @param string $baseDir the path to the directory where we can
     * find all the config files of the flows that need to be loaded
     * into the DI container.
     */
    public function load(string $baseDir = "")
    {
        //is the baseDir an actual dir? Use a Directory Iterator.
        //For the moment, let an exceptions halt Execution!
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
            $GLOBALS['log']->fatal("Loading RuleEngine flow configs. The pathname is: " . $fileInfo->getPathname());
            $this->container->configureFromFile($fileInfo->getPathname());
        }
    }

}