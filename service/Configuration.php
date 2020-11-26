<?php

class Configuration extends Service
{
    private $configBag;

    public function onRegister()
    {
        parent::onRegister();
        $this->load();
    }

    private function load()
    {
        $configBag = [];

        foreach ($this->get('fileManager')->files('config') as $path) {
            $pathParts = pathinfo($path);
            if ($pathParts['extension'] !== 'php') continue;
            $configBag[$pathParts['filename']] = include $path;
        }

        $this->configBag = Utils::dot($configBag);
    }

    public function setConfig($name, $value)
    {
        $this->configBag[$name] = $value;
    }

    public function getConfig($name)
    {
        return isset($this->configBag[$name]) ? $this->configBag[$name] : null;
    }
}
