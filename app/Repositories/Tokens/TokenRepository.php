<?php

namespace App\Repositories\Tokens;

use App\Http\AccessControl\AccessManagerInterface;
use App\Models\User;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Token;
use App\Repositories\DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;
use App\Repositories\Auth\Token AS RToken;
use App\Repositories\User\User as UserUser;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */

class TokenRepository implements TokenInterface
{

    private $accessManager;

    private $authRepositoryInterface;

    /**
     * 
     */
    private $token;

    /**
     * @var
     */
    protected $connection;


    public function __construct(

        AccessManagerInterface $accessManager,
      //  AuthRepositoryInterface $authRepositoryInterface 
        

    )
    {

        $this->accessManager = $accessManager;
      //  $this->authRepositoryInterface = $authRepositoryInterface;
        $this->connection = DB::connection()->getPdo();

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Auth\AuthRepositoryInterface::find()
     */
    /*public function find(int $id): ?Auth
    {
        $query = $this->query . ' WHERE a.id = ? LIMIT 1';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {
                return $this->format($data);
            }
        }

        return null;
    }*/


    /**
     * @param Request $request
     * @param int $accountID
     * @return mixed|void
     */
    public function verifyAccountToken(Request $request, int $accountID)
    {

        $tokenCheck = DB::table('auth_tokens')->where('auth_id', $accountID)->whereDate('expires_at', ">", Carbon::now())
            ->orderBy('created_at', 'asc')->get();


        if (!count($tokenCheck) > 0) {

            return 401;
            //return $tokenCheck;
        }


    }


    /**
     * @param Request $request
     * @param Auth auth
     * @return array|mixed
     * @throws \ExceptionS
     */
    public function createAccountToken(Request $request, Auth $auth)
    {


        $token = new RToken();
        $secret = strtoupper($this->accessManager->createRandomCode(64));

        $dateTime = (new DateTime())->format('Y-m-d H:i:s');


        $ip = null;
        $client = null;

        // change if exist

        if (null !== $request) {

            $ip = $request->ip();
            $client = $request->header('User-Agent');

        }

        $token->setSecret($secret);
        $token->setIp($ip);
        $token->setClient($client);

        // Set token expiration time to one (6) month from now
        //$expires_at = new DateTime();
        //$expires_at->add(new \DateInterval('P1M'));
        // Set token expiration time to six (6) month from now
        $expires_at = new DateTime();
        $expires_at->add(new \DateInterval('P6M'));
        $token->setExpiresAt($expires_at);
        $token->setCreatedAt(new DateTime($dateTime ));
        $token->setUpdatedAt(new DateTime($dateTime))/*->format('Y-m-d H:i:s')*/;

        $query = <<<QUERY
            
                INSERT INTO auth_tokens (
                    
                    auth_id, 
                    token,
                    client,
                    ip, 
                    expires_at,
                    created_at
                    
                    )
                VALUES (?, ?, ?, ?, ?, ?)

                ON DUPLICATE KEY UPDATE 
                
                auth_id = VALUES(auth_id),
                token = VALUES( token), 
                client = VALUES(client), 
                ip = VALUES (ip),
                expires_at = VALUES(expires_at),
                updated_at = VALUES (created_at);

            QUERY;


        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $auth->getId(), PDO::PARAM_INT);
        $stmt->bindValue(2, $token->getSecret(), PDO::PARAM_STR);
        $stmt->bindValue(3, $token->getClient(), PDO::PARAM_STR);
        $stmt->bindValue(4, $token->getIp(), PDO::PARAM_STR);
        $stmt->bindValue(5,$token->getExpiresAt(), PDO::PARAM_STR);
        $stmt->bindValue(6,  $token->getCreatedAt() , PDO::PARAM_STR);

        if ($stmt->execute()) {
            

            $authId = (int) $this->connection->lastInsertId();
            
            if(!$authId){
                
                $this->authRepositoryInterface->activate($authId);
            }
            return $authId;//$stmt->rowCount();
        }

        return 0;

    }


    /**
     *
     * {@inheritdoc}
     * @seeApp\Repositories\Auth\AuthRepositoryInterface::activate()
     */
   /* public function activate(int $id): int
    {
        $query = <<<UPDATEQUERY
        UPDATE auth a INNER JOIN users u ON a.user_id = u.id
        SET a.confirmation_code = NULL,
            a.updated_at = ?,
            u.activated = 1,
            u.updated_at = ?
        WHERE a.id = ?
        UPDATEQUERY;

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);
        $stmt->bindValue(2, $time, PDO::PARAM_INT);
        $stmt->bindValue(3, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
    }*/

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Tokens\TokenRepository::updateAccountToken()
     */
    public function updateAccountToken(Request $request, Auth $auth): int
    {

        $dateTime = (new DateTime())->format('Y-m-d H:i:s');

        $token = new RToken();
        $secret = strtoupper($this->accessManager->createRandomCode(64));

        $ip = null;
        $client = null;

        // if exist

        if (null !== $request) {

            $ip = $request->ip();
            $client = $request->header('User-Agent');

        }

        $token->setSecret($secret);
        $token->setIp($ip);
        $token->setClient($client);

        // Set token expiration time to one (6) month from now
        //$expires_at = new DateTime();
        //$expires_at->add(new \DateInterval('61M'));
        // Set token expiration time to six (6) month from now
        $expires_at = new DateTime();
        $expires_at->add(new \DateInterval('P6M'));
        $token->setExpiresAt($expires_at);
        $token->setCreatedAt(new DateTime($dateTime ));
        $token->setUpdatedAt(new DateTime($dateTime ));

        $query = <<<UPDATE_QUERY
        
        UPDATE  auth_tokens  at JOIN auth a ON at.auth_id = a.id
        SET at.auth_id = ?,
        at.token = ?,
        at. client = ?,
        at.ip = ?,
        at.expires_at = ?,
        at.updated_at = ?
        WHERE at.id = ?

        UPDATE_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $auth->getId(), PDO::PARAM_INT);
        $stmt->bindValue(2, $token->getSecret(), PDO::PARAM_STR);
        $stmt->bindValue(3, $token->getClient(), PDO::PARAM_STR);
        $stmt->bindValue(4, $token->getIp(), PDO::PARAM_STR);
        $stmt->bindValue(5, $token->getExpiresAt(), PDO::PARAM_STR);
        $stmt->bindValue(6, $token->getUpdatedAt() , PDO::PARAM_STR);
        $stmt->bindValue(7, $auth->getToken()->getId(), PDO:: PARAM_STR);

        if ($stmt->execute()) {

            
            return  $auth->getId();//$stmt->rowCount();

        }

        return 0;
    }


    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Auth\AuthRepositoryInterface::deleteToken()
     */
    public function  destroyAccountToken(string $token): int
    {
        $query = 'DELETE FROM auth_tokens WHERE BINARY secret = ?';

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
    }


}
