<?php
namespace mhndev\oauthClient\repository;

use DateTime;
use mhndev\oauthClient\entity\common\Token;
use mhndev\oauthClient\exceptions\DataSourceConnectionException;
use mhndev\oauthClient\exceptions\ModelNotFoundException;
use mhndev\oauthClient\interfaces\entity\iToken;
use mhndev\oauthClient\interfaces\repository\iTokenRepository;
use PDO;

/**
 * Class TokenRepositorySqlite
 * @package mhndev\digipeyk\services\oauth2\interfaces\repository
 */
class TokenRepositorySqlite extends aSqliteRepository implements iTokenRepository
{

    /**
     * @param $client_id
     * @return iToken
     * @throws DataSourceConnectionException
     */
    function findByClientId($client_id)
    {
        $queryString = 'SELECT * FROM tokens WHERE "client_id"= :client_id';

        try{
            $qry = $this->dataSource->prepare($queryString);

            if($qry == false){
                throw new DataSourceConnectionException(sprintf(
                    $queryString. ' return false'
                ));
            }

        }
        catch (\PDOException $e){
            throw new DataSourceConnectionException($e->getMessage());
        }


        $qry->bindValue(':client_id', $client_id, \PDO::PARAM_STR);

        $result = $qry->execute();

        if($result){
            $data = $qry->fetch(PDO::FETCH_ASSOC);

            if(empty($data)){
                throw new ModelNotFoundException(sprintf(
                    'entity not found with client_id : %s',
                    $client_id
                ));
            }

            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['expires_at']);

            $data['expires_at'] = $dateTime;

            $token = Token::fromArray($data);

            return $token;
        }

        $this->handleError($qry);
    }

    /**
     * @param iToken $token
     * @return mixed
     * @throws DataSourceConnectionException
     */
    function writeOrUpdate(iToken $token)
    {
        $queryString = 'SELECT * FROM tokens WHERE "client_id"= :client_id';


        try{
            $qry = $this->dataSource->prepare($queryString);

            if($qry == false){
                throw new DataSourceConnectionException(sprintf(
                    $queryString. ' return false'
                ));
            }

        }
        catch (\PDOException $e){
            throw new DataSourceConnectionException($e->getMessage());
        }

        $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);
        $qry->execute();
        $result = $qry->fetchAll();


        if(!empty($result)){
            $queryString = 'UPDATE tokens set "credentials"=:credentials WHERE "client_id"= :client_id';
            $qry = $this->dataSource->prepare($queryString);
            $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);
            $qry->bindValue(':credentials', $token->getCredentials(), \PDO::PARAM_STR);

            $qry->execute();
        }else{

            $queryString = 'INSERT INTO tokens (client_id, client_secret, type, credentials,
                             expires_at) VALUES (:client_id, :client_secret, :type,
                             :credentials, :expires_at)';

            $qry = $this->dataSource->prepare($queryString);

            $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);
            $qry->bindValue(':client_secret', $token->getClientSecret(), \PDO::PARAM_STR);
            $qry->bindValue(':credentials', $token->getCredentials(), \PDO::PARAM_STR);
            $qry->bindValue(':type', $token->getType(), \PDO::PARAM_STR);
            $qry->bindValue(':expires_at', date('Y-m-d H:i:s', $token->getExpiresAt()->getTimestamp()), \PDO::PARAM_STR);


            $qry->execute();
        }

        $this->handleError($qry);
    }


    /**
     * @param iToken $token
     */
    function writeOrUpdateIfExpired(iToken $token)
    {
        try{
            $token = $this->findByClientId($token->getClientId());

            if($token->getExpiresAt() > (new DateTime())->setTimestamp(time() - 10 ) ){
                $queryString = 'UPDATE tokens set "credentials"=:credentials WHERE "client_id"= :client_id';
                $qry = $this->dataSource->prepare($queryString);
                $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);
                $qry->bindValue(':credentials', $token->getCredentials(), \PDO::PARAM_STR);

                $qry->execute();
            }

        }catch (ModelNotFoundException $e){
            $queryString = 'INSERT INTO tokens (client_id, client_secret, type, credentials,
                             expires_at) VALUES (:client_id, :client_secret, :type,
                             :credentials, :expires_at)';

            try{
                $qry = $this->dataSource->prepare($queryString);

                if($qry == false){
                    throw new DataSourceConnectionException(sprintf(
                        $queryString. ' return false'
                    ));
                }

            }
            catch (\PDOException $e){
                throw new DataSourceConnectionException($e->getMessage());
            }


            $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);
            $qry->bindValue(':client_secret', $token->getClientSecret(), \PDO::PARAM_STR);
            $qry->bindValue(':credentials', $token->getCredentials(), \PDO::PARAM_STR);
            $qry->bindValue(':type', $token->getType(), \PDO::PARAM_STR);
            $qry->bindValue(':expires_at', date('Y-m-d H:i:s', $token->getExpiresAt()->getTimestamp()), \PDO::PARAM_STR);


            $qry->execute();

            $this->handleError($qry);
        }

    }


    /**
     * @return bool
     * @throws DataSourceConnectionException
     */
    function deleteAll()
    {
        $queryString = 'DELETE FROM tokens';
        try{
            $qry = $this->dataSource->prepare($queryString);

            if($qry == false){
                throw new DataSourceConnectionException(sprintf(
                    $queryString. ' return false'
                ));
            }

        }
        catch (\PDOException $e){
            throw new DataSourceConnectionException($e->getMessage());
        }

        $result = $qry->execute();

        $this->handleError($qry);

        return $result;
    }


    /**
     * @param iToken $token
     * @return mixed
     * @throws DataSourceConnectionException
     */
    function delete(iToken $token)
    {
        $queryString = 'DELETE FROM tokens WHERE client_id=":client_id"';
        try{
            $qry = $this->dataSource->prepare($queryString);

            if($qry == false){
                throw new DataSourceConnectionException(sprintf(
                    $queryString. ' return false'
                ));
            }

        }
        catch (\PDOException $e){
            throw new DataSourceConnectionException($e->getMessage());
        }


        $qry->bindValue(':client_id', $token->getClientId(), \PDO::PARAM_STR);

        $result = $qry->execute();

        if($result){
            return $token;
        }

        else{
            $this->handleError($qry);
        }
    }


}
