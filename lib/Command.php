<?php


abstract class Command
{
    protected $services;

    public function setServiceContainer($container)
    {
        $this->services = $container;
    }

    public function get($serviceId)
    {
        return $this->services->get($serviceId);
    }

    public abstract function help(array $arguments);
}
