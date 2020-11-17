<?php


class Console extends Service
{ 
    private $arguments;

    public function send()
    {
        $firstArg = array_reverse(explode(':', $this->arguments[0]));

        if (!class_exists($class = ucfirst($firstArg[0]) . 'Command')) {
            $class = 'HelpCommand';
        }

        $class = new $class();

        if (!isset($firstArg[1]) || !method_exists($class, $firstArg[1]))
            $method = 'help';
        else
            $method = $firstArg[1];

        $output = $class->$method(array_values(array_slice($this->arguments, 1, count($this->arguments) - 1, true)));

        echo $output . PHP_EOL;
    }

    public function prepare($arguments)
    {
        $arguments = array_values(array_slice($arguments, 1, count($arguments) - 1, true));

        if (empty($arguments)) array_push($arguments, 'help');

        $this->arguments = $arguments;

        return $this;
    }
}
