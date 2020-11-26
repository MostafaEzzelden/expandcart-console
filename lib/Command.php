<?php


abstract class Command
{

    // Fields
    private $container;

    // Methods

    public function setServiceContainer($container)
    {
        $this->container = $container;
    }


    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }

    public abstract function help(array $arguments);
}
