<?php


abstract class Command
{
    protected $templateManager;

    public function __construct()
    {
        $this->templateManager = new TemplateManager;
    }

    public abstract function help(array $arguments);
}
