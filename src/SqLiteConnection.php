<?php
namespace mhndev\oauthClient;

/**
 * Class SqLiteConnection
 * @package mhndev\oathClient
 */
class SqLiteConnection
{
    /**
     * PDO instance
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $dbPath;

    /**
     * SqLiteConnection constructor.
     * @param $dbPath
     */
    public function __construct($dbPath)
    {
        $this->dbPath = $dbPath;
    }

    /**
     * return in instance of the PDO object that connects to the SQLite database
     * @return \PDO
     */
    public function connect()
    {
        if ($this->pdo == null) {

            try{
                $this->pdo = new \PDO("sqlite:" . $this->dbPath);

            }catch (\PDOException $e){
                throw $e;
            }
        }
        return $this->pdo;
    }

}
