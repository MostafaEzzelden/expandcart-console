<?php


class Console extends Service
{
    const HELP_COMMAND = "HelpCommand";
    const HELP_ACTION = "help";

    private $arguments;
    private $command;
    private $parameters;

    private $output;

    public function run($arguments): self
    {
        $arguments = array_values(array_slice($arguments, 1, count($arguments) - 1, true));

        if (empty($arguments)) array_push($arguments, self::HELP_ACTION);

        $this->arguments  = $arguments;
        $this->command    = $this->prepareCommand();
        $this->parameters = $this->prepareParameters();

        $this->output     = $this->command[0]->{$this->command[1]}(...$this->parameters);

        return $this;
    }

    private function prepareCommand()
    {
        $commandParts = array_reverse(explode(':', $this->arguments[0]));

        if (!class_exists($commandName = ucfirst($commandParts[0]) . 'Command'))
            $commandName = self::HELP_COMMAND;

        $command = new $commandName();
        $command->setServiceContainer($this->getServiceContainer());

        $action = !isset($commandParts[1]) ? self::HELP_ACTION : $commandParts[1];

        if (!method_exists($command, $action)) $action = self::HELP_ACTION;

        return [$command, $action];
    }

    private function prepareParameters()
    {
        return  [array_values(array_slice($this->arguments, 1, count($this->arguments) - 1, true))];
    }

    public function output()
    {
        return $this->output;
    }
}
