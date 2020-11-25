<?php


class Console extends Service
{
    private $arguments;
    private $command;
    private $parameters;

    public function prepare($arguments): self
    {
        $arguments = array_values(array_slice($arguments, 1, count($arguments) - 1, true));

        if (empty($arguments)) array_push($arguments, 'help');

        $this->arguments = $arguments;

        $this->prepareCommand()
            ->prepareAction()
            ->prepareParameters();

        return $this;
    }

    public function send()
    {
        $output = call_user_func_array([$this->command, $this->action], [$this->parameters]);
        echo $output . PHP_EOL;
    }

    private function prepareCommand()
    {
        $commandName = array_reverse(explode(':', $this->arguments[0]))[0];

        if (!class_exists($commandName = ucfirst($commandName) . 'Command'))
            $commandName = 'HelpCommand';

        $this->command =  new $commandName;
        $this->command->setServiceContainer($this->services);
        return $this;
    }

    private function prepareAction()
    {
        $command = array_reverse(explode(':', $this->arguments[0]));
        $action = !isset($command[1]) ? 'help' : $command[1];

        if ($this->command && !method_exists($this->command, $action)) $action = 'help';

        $this->action = $action;

        return $this;
    }

    private function prepareParameters()
    {
        $this->parameters = array_values(array_slice($this->arguments, 1, count($this->arguments) - 1, true));
    }
}
