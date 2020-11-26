<?php

// Base class for all services implementing the Singleton pattern

class Service
{
    // Fields

    private $container;

    // Methods

    public function setServiceContainer($container)
    {
        $this->container = $container;

        $this->onRegister();
    }

    public function getServiceContainer()
    {
        return $this->container;
    }

    public function onRegister()
    {
    }

    public function onRemove()
    {
    }

    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }
}
