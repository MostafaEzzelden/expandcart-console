<?php

class Application
{
    private $services;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->services = ServiceContainer::getInstance();
        $this->services->setApp($this);
        $this->config();
    }

    /**
     * Setup the services
     *
     * @return void
     */
    public function config()
    {
        $this->services->registerService('console', 'Console');
        // $this->services->registerService('config', 'Configuration');
        // $this->services->registerService('logger', 'Logger');
        // $this->services->registerService('stats', 'Statistics');
        // $this->services->registerService('db', 'Database');
    }

    /**
     * Run Request
     *
     * @param array $request command arguments 
     * @return Console response
     */
    public function run($request)
    {
        $response = $this->services->get('console')->prepare($request);

        $this->services->clean();

        // Return the response

        return $response;
    }
}
