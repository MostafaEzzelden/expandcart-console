<?php

class Logger extends Service
{
    const INFO    = 'info';
    const ERROR   = 'error';
    const WARNING = 'warning';

    protected $logFilePath;
    protected $logErrors;
    protected $logWarnings;
    protected $logInfos;
    protected $logCount = 0;

    // Methods

    public function onRegister()
    {
        parent::onRegister();

        $this->logFilePath = ROOT_DIR . '/' . $this->container->config->getConfig('services.logger.file');
        $this->logErrors   = $this->container->config->getConfig('services.logger.log_errors');
        $this->logWarnings = $this->container->config->getConfig('services.logger.log_warnings');
        $this->logInfos    = $this->container->config->getConfig('services.logger.log_infos');
    }

    public function error($message)
    {
        if ($this->logErrors) {
            // Include stack trace in the entry

            $stackTrace = debug_backtrace();

            $stackStr = "Stack trace:\n";

            foreach ($stackTrace as $entry) {
                $stackStr .= "{$entry['file']}:{$entry['line']} ({$entry['class']}:{$entry['function']}())\n";
            }

            $this->log(self::ERROR, $message . "\n" . $stackStr);
        }
    }

    public function warning($message)
    {
        if ($this->logWarnings) {
            $this->log(self::WARNING, $message);
        }
    }

    public function info($message)
    {
        if ($this->logInfos) {
            $this->log(self::INFO, $message);
        }
    }

    public function log($type, $message)
    {
        $datetime = date("Y-m-d H:i:s");
        $entry    = '';


        // Add entry's body

        $entry .= "$datetime [$type] $message\n";

        file_put_contents($this->logFilePath, $entry, FILE_APPEND);

        // Increment the counter
        $this->logCount++;
    }

    public function getLogs()
    {
        if (file_exists($this->logFilePath)) {
            return file_get_contents($this->logFilePath);
        }

        return '';
    }

    public function clear()
    {
        file_put_contents($this->logFilePath, '');

        // Clear the counter

        $this->logCount = 0;
    }
}
