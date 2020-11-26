<?php

class DB extends Service
{
    // Constants

    const DEFAULT_PORT = 3306;

    // Fields

    private $pdo;

    private $config;
    private $logger;
    private $stats;
    private $lastQuery;

    // Constructor

    public function onRegister()
    {
        parent::onRegister();

        // -----
        $this->config  = $this->get('config');
        $this->stats   = $this->get('stats');
        $this->logger  = $this->get('logger');

        $this->reconnect();
    }

    // Methods

    public function connect($dsn, $user, $pass)
    {
        try {
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->execute('SET NAMES utf8');
            $this->execute('Set sql_mode = (Select Replace(@@sql_mode, "ONLY_FULL_GROUP_BY",""))');
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }

        return !empty($this->pdo);
    }

    public function reconnect()
    {
        $connection = sprintf(
            "mysql:dbname=%s;host=%s;port=%s",
            $this->config->getConfig('database.connections.mysql.database'),
            $this->config->getConfig('database.connections.mysql.host'),
            $this->config->getConfig('database.connections.mysql.port')
        );

        return $this->connect(
            $connection,
            $this->config->getConfig('database.connections.mysql.username'),
            $this->config->getConfig('database.connections.mysql.password')
        );
    }

    public function execute($q, $params = null)
    {
        $this->ensureValidConnection();

        // Split all queries into separate ones

        $queries = $this->splitQueries($q);

        // Execute all queries and return the result of the last one

        $result = true;

        try {
            foreach ($queries as $q) {
                $this->lastQuery = $q;

                $statement = $this->pdo->prepare($q);

                if ($statement) {
                    $result = $result && $statement->execute($params);

                    $this->stats->inc('db_queries');
                }
            }

            return $result;
        } catch (Exception $e) {
            // Log

            $this->logger->error($e->getMessage());

            return false;
        }

        return false;
    }

    public function query($q, $params = null)
    {
        $this->ensureValidConnection();

        // Split all queries into separate ones

        $queries = $this->splitQueries($q);

        // Execute all queries and return the result of the last one

        $result = false;

        try {
            foreach ($queries as $q) {
                $this->lastQuery = $q;

                $statement = $this->pdo->prepare($q);

                if ($statement) {
                    $statement->execute($params);

                    $result = $statement->fetchAll();

                    $this->stats->inc('db_queries');
                }
            }

            return $result;
        } catch (Exception $e) {
            // Log

            $this->logger->error($e->getMessage());

            return false;
        }

        return false;
    }

    public function queryOne($q, $params = null)
    {
        $this->ensureValidConnection();

        try {
            $this->lastQuery = $q;

            $statement = $this->pdo->prepare($q);

            if ($statement) {
                $statement->execute($params);

                $this->stats->inc('db_queries');

                return $statement->fetch();
            }
        } catch (Exception $e) {
            // Log

            $this->logger->error($e->getMessage());

            return false;
        }

        return false;
    }

    public function lastInsertId()
    {
        $this->ensureValidConnection();

        return intval($this->pdo->lastInsertId());
    }

    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    public function getTables()
    {
        $this->ensureValidConnection();

        $result = [];
        $tables = $this->query('SHOW TABLES');

        $this->stats->inc('db_queries');

        if ($tables) {
            foreach ($tables as $tableInfo) {
                $result[] = $tableInfo[0];
            }
        }

        return $result;
    }

    protected function splitQueries($q)
    {
        // Replace \r\n with \n

        $nlFixed = str_replace("\r\n", "\n", $q);

        // Remove comments and trim

        $pureQueries = trim(preg_replace([

            "/\/\*.*(\n)*.*(\*\/)?/",
            "/\s*--.*\n/",
            "/\s*#.*\n/"

        ], "\n", $nlFixed));

        // Split

        $queries = explode(";\n", $pureQueries);

        // Remove white space

        foreach ($queries as &$query) {
            $query = trim($query);
        }

        return $queries;
    }

    protected function ensureValidConnection()
    {
        if (!$this->pdo) {
            throw new Exception("Invalid Database connection");
        }
    }
}
