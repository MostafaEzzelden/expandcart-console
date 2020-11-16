<?php


abstract class Command
{
    protected $reader;

    public function __construct()
    {
        $this->reader = new Reader(__DIR__ . '/../tpl');
    }

    public abstract function help(array $arguments);

    
}
