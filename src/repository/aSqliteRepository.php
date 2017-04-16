<?php
namespace mhndev\oauthClient\repository;

/**
 * Class aSqliteRepository
 * @package mhndev\digipeyk\services\oauth2\interfaces\repository
 */
abstract class aSqliteRepository
{


    /**
     * @var \PDO
     */
    protected $dataSource;

    /**
     * aSqliteRepository constructor.
     * @param \PDO $dataSource
     */
    public function __construct(\PDO $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @param \PDOStatement $qry
     */
    protected function handleError(\PDOStatement $qry)
    {
        $error = $qry->errorInfo();

        if(!empty($error[0]) && !empty($error[1])){
            throw new \PDOException($error[2], $error[1]);
        }
    }

}