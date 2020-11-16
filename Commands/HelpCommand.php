<?php

class HelpCommand extends Command
{
    public function help(array $arguments)
    {
        $output = "Console Application\n";

        foreach (scandir(__DIR__) as $class) {
            $class = str_replace('.php', '', $class);
            if ($class == '.' || $class == ".." || $class == __CLASS__) continue;
            $output .= (new $class)->help($arguments) . PHP_EOL;
        }
        return $output;
    }
}
