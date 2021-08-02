<?php


abstract class Command extends Service
{
    public abstract function help(array $arguments);
}
