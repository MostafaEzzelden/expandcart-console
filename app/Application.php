<?php

class Application
{
    private $container;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->container = ServiceContainer::getInstance();

        // Setup the services

        $this->container->set('config', 'Configuration');
        $this->container->set('logger', 'Logger');
        $this->container->set('stats', 'Statistics');
        $this->container->set('db', 'DB');
        $this->container->set('console', 'Console');
        $this->container->set('templateManager', 'TemplateManager');
        $this->container->set('fileManager', 'FileManager');
    }

    public function getServiceContainer()
    {
        return $this->container;
    }

    /**
     * Run Request
     *
     * @param array $request command arguments 
     * @return Console response
     */
    public function run($request)
    {
        $response = $this->container->get('console')->run($request);

        $this->container->clean();

        // Return the response

        return $response;
    }
}
