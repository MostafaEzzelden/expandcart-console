<?php

// Base class for all services

class Service
{
    public $container;

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
        //
    }

    public function onRemove()
    {
        //
    }


    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    // public function __call($method, $parameters)
    // {
    //     return call_user_func_array(array($this->serviceContainer, $method), $parameters);
    // }
}
