<?php

class Helper
{
    protected static $path = __DIR__;

    protected static $envBag = null;

    public static function getEnv($key, $default = null)
    {
        if (is_null(static::$envBag)) {
            static::$envBag = [];
            $envPath = static::$path . '/../.env';
            if (file_exists($envPath)) {
                $lines = file($envPath);

                $envs = [];

                foreach ($lines as $line) {
                    $parts = explode('=', $line, 2);

                    if (count($parts) === 2)
                        $envs[$parts[0]] = trim($parts[1]);
                }

                static::$envBag = $envs;
            }
        }

        return isset(static::$envBag[$key]) ? static::$envBag[$key] : $default;
    }
}
