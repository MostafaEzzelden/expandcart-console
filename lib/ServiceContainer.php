<?php

class ServiceContainer
{
    private static $instance = null;

    // private $app = null;

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

    public function get($id)
    {
        if (is_array($this->services[$id])) {
            $this->services[$id] = new $this->services[$id][0](...$this->services[$id][1]);
            $this->services[$id]->setServiceContainer($this);
        }

        return $this->services[$id];
    }

    public function set(string $id, string $ServiceClass, array $opts = []): void
    {
        $this->services[$id] = array($ServiceClass, $opts);
    }

    public function setInstance($id, $instance)
    {
        $this->services[$id] = $instance;
    }

    public function has($id)
    {
        return isset($this->services[$id]);
    }


    public function clean()
    {
        foreach ($this->services as $id => $service) {
            if (!is_array($service)) $service->onRemove();
        }

        $this->services = array();
    }


    public function __get($id)
    {
        if ($this->has($id)) return $this->get($id);

        return null;
    }
}
