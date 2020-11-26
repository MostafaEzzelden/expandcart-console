<?php

class ServiceContainer
{
    private static $instance = null;

    private $app = null;

    private $services = [];

    // Constructor

    private function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            new ServiceContainer();
        }

        return self::$instance;
    }

    // Methods

    public function setApp($app)
    {
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function get($id)
    {
        // Create the service if not already available

        if (is_array($this->services[$id])) {
            $this->services[$id] = new $this->services[$id][0](...$this->services[$id][1]);
            $this->services[$id]->setServiceContainer($this);
        }

        // Return the service

        return $this->services[$id];
    }

    public function registerService(string $id, string $ServiceClass, array $opts = []): void
    {
        // Store the service 
        $this->services[$id] = array($ServiceClass, $opts);
    }

    public function registerServiceInstance($id, $instance)
    {
        // Store the service instance

        $this->services[$id] = $instance;
    }

    public function clean()
    {
        // Remove the registered services

        foreach ($this->services as $id => $service) {
            if (!is_array($service)) $service->onRemove();
        }

        $this->services = array();
    }
}
