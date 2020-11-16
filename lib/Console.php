<?php


class Console
{
    private function execute(array $arguments)
    {
        $firstArg = array_reverse(explode(':', $arguments[0]));

        if (!class_exists($class = ucfirst($firstArg[0]) . 'Command')) {
            $class = 'HelpCommand';
        }

        $class = new $class();

        if (!isset($firstArg[1]) || !method_exists($class, $firstArg[1]))
            $method = 'help';
        else
            $method = $firstArg[1];

        $output = $class->$method(array_values(array_slice($arguments, 1, count($arguments) - 1, true)));
        echo $output . PHP_EOL;
        exit;
    }

    public static function run($arguments)
    {
        $arguments = array_values(array_slice($arguments, 1, count($arguments) - 1, true));

        if (empty($arguments)) array_push($arguments, 'help');

        (new static)->execute($arguments);
    }
}
