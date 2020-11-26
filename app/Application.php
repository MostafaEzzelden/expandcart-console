<?php

class Application
{
    private $serviceContainer;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->serviceContainer = ServiceContainer::getInstance();
        $this->serviceContainer->setApp($this);

        // Setup the services

        $this->serviceContainer->registerService('config', 'Configuration');
        $this->serviceContainer->registerService('logger', 'Logger');
        $this->serviceContainer->registerService('stats', 'Statistics');
        $this->serviceContainer->registerService('db', 'DB');
        $this->serviceContainer->registerService('console', 'Console');
        $this->serviceContainer->registerService('templateManager', 'TemplateManager');
        $this->serviceContainer->registerService('fileManager', 'FileManager');
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Run Request
     *
     * @param array $request command arguments 
     * @return Console response
     */
    public function run($request)
    {
        $response = $this->serviceContainer->get('console')->prepare($request);

        $this->serviceContainer->clean();

        // Return the response

        return $response;
    }
}
