<?php

namespace App\Repositories\Notification;

use App\FCM\Firebase;
use App\FCM\Push;
use App\Repositories\Auth\Auth;
use App\Repositories\Model;
use App\Repositories\NotificationToken\NotificationToken;
use App\Repositories\Util\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\Repository;
use App\Repositories\User\User;
use DateTime;
use PDO;
use stdClass;

class NotificationRepository extends Repository implements NotificationInterface//implements NotificationInterface
{
    /**
     * @var
     */
    private $response;

    /**
     * @var
     */
    private $utils;

    /**
     * @var null
     */
    private $userID = null;

    /**
     * @var
     */
    private $notificationToken;

    /**
     * @var
     */
    protected $connection;

    //------
    private static  $fields = <<<QUERY
        id, 
        user_id,
        token, 
        code AS ref,
        device_id AS device_number, 
        ip, 
        client,
        created_at,
        updated_at,
        deleted_at
      QUERY;

        private $mainQuery = <<<QUERY
        FROM notification_token n
        JOIN users u ON u.id = n.user_id
        WHERE u.blocked = 0
        QUERY;

        private $subQuery = <<<QUERY
        FROM notification_token n
        JOIN users u ON u.id = n.user_id
        WHERE u.blocked = 0;
        QUERY;


      private static  $fieldsSubQuery = <<<QUERY
        id, 
        user_id, 
        token, 
        code AS ref,
        device_id AS device_number, 
        ip, 
        client,
        created_at,
        updated_at, 
        deleted_at
      QUERY;
      
      

    public function __construct()
    {
        $this->connection = DB::connection()->getPdo();

    }

    /**
     * @param NotificationToken $notificationToken
     * @return mixed|void
     * @throws \Exception
     */
    public function createNotificationToken(NotificationToken $notificationToken): ?NotificationToken
    {

        $this->utils = new Utils();

        $notificationToken->setRefCode($this->utils->generateToken(12));

        if ($notificationToken->getUser() != null) {

            $this->userID = $notificationToken->getUser()->getId();
        }

        if ($this->userID != null) {

            $this->updateTokenByName($notificationToken);
        }


        if ($this->fetchTokenDeviceID($notificationToken) != null) {

          $this->updateTokenByName($notificationToken);
        }

        if ($this->fetchTokenDeviceID($notificationToken) == null)//if doesn't exist: create
        {
           $this->notificationToken = $this->InsertToken($notificationToken);
        }

        return $notificationToken;
    }


    /**
     * @param Push $push
     * @param array $token
     * @param String $pushType
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createNotification(String $pushType,Push $push, $token = [])
    {
        $data = json_decode(\request()->getContent());

        $firebase = new Firebase();


        //-- data

        $data = [
            "id" => "185",//$receiver_id,
            "unread" => 0,
            "text" => "test test test",
            "isLiked" => 0,//$request->title,
            "sender" => "Foster",
            "time" => "11:03AM",
            "date" => "24 Jan 2021",
            "humanDate" => "5 minutes ago",
            // "time" => $request->title,
            //  "description" => $request->body,
            // "text" => $request->body,
            "is_read" => 0,

        ];

        //convert to json

        if ($pushType === 'topic') {

            $this->response = $firebase->sendToTopic('weather', "test topic", "test", $data, '');

        } else if ($pushType === 'individual') {

            $this->response = $firebase->send(array($token), $push);

            return response()->json([

                'response' => $this->response
            ]);
        }


    }


    /***
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenByName(NotificationToken $notificationToken)
    {

        $fields = self::$fields;
    
        $query = <<<FCMQUERY

              SELECT
              $fields
              FROM notification_token
              WHERE token=?

             FCMQUERY;

        //$fields = 'id, user_id, token, code AS ref,device_id AS device_number, ip, client,created_at,updated_at, deleted_at';
       // $query = 'SELECT ' . $fields . ' FROM notification_token' . ' WHERE token=?';
        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $notificationToken->getPushToken(), PDO::PARAM_INT);


        if ($stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (false != $data) {

                return $data;

            }
        }

        return [];
    }

    /***
     * Get notification  by user id
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenByUserID(NotificationToken $notificationToken)
    {
    
        $fields = self::$fields;
    
        $query = <<<FCMQUERY

              SELECT
              $fields
              FROM notification_token
              WHERE user_id = ?

            FCMQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $notificationToken->getUser()->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (false != $data) {

                return $data;

            }
        }

        return [];
    }


    /***
     * Fetch token by device ID
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenDeviceID(NotificationToken $notificationToken)
    {

        $fields = self::$fields;
    
        $query = <<<FCMQUERY

              SELECT
              $fields
              FROM notification_token
              WHERE device_id=?

        FCMQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $notificationToken->getDeviceNumber() ?
         $notificationToken->getDeviceNumber() : null, PDO::PARAM_STR);

        $items = [];
        if ($stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (false != $data) {
                return $data;
            }
        }


        return array_shift($items);
    }


    /**
     * @param NotificationToken $notificationToken
     * @return NotificationToken
     */
    public function updateTokenByName(NotificationToken $notificationToken): ?NotificationToken
    {
        $query = <<<UPDATE_QUERY
                UPDATE notification_token nt 
                JOIN auth au ON 
                au.user_id = nt.user_id
                SET nt.user_id = ?,
                nt.token = ?,
                nt.code = ?,
                nt.device_id = ?,
                nt.ip = ?,
                nt.client = ?,
                nt.platform = ?,
                nt.model_name = ?,
                nt.is_push_enabled = ?,
                nt.is_physical_device = ?,
                nt.device_version_release = ?,
                nt.updated_at = ?
                WHERE au.user_id = ?
        UPDATE_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1,  $notificationToken->getUser()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(2,  $notificationToken->getPushToken(), PDO::PARAM_INT);
        $stmt->bindValue(3,  $notificationToken->getRefCode(), PDO::PARAM_STR);
        $stmt->bindValue(4,  $notificationToken->getDeviceNumber(), PDO::PARAM_STR);
        $stmt->bindValue(5,  $notificationToken->getIp(), PDO::PARAM_STR);
        $stmt->bindValue(6,  $notificationToken->getClient(), PDO::PARAM_STR);
        $stmt->bindValue(7,  $notificationToken->getPlatform(), PDO::PARAM_STR);
        $stmt->bindValue(8,  $notificationToken->getModelName(), PDO::PARAM_STR);
        $stmt->bindValue(9,  $notificationToken->isPushEnabled() ? 1:0, PDO::PARAM_INT);
        $stmt->bindValue(10, $notificationToken->isPhysicalDevice() ? 1:0, PDO::PARAM_INT);
        $stmt->bindValue(11, $notificationToken->getVersionRelease(), PDO::PARAM_STR);
        $stmt->bindValue(12, $notificationToken->getUpdatedAt(), PDO::PARAM_STR);
        $stmt->bindValue(13, $notificationToken->getUser()->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $notificationToken;
        }
        return $notificationToken;
    }

    /**
     * insert notification token
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function insertToken (NotificationToken $notificationToken): ?NotificationToken
    {
        
        try {

            $this->connection->beginTransaction();

            $query = <<<QUERY
                INSERT INTO notification_token (
                        user_id,
                        token,
                        code,
                        device_id,
                        ip,
                        client,
                        platform,
                        model_name,
                        is_push_enabled,
                        is_physical_device,
                        device_version_release,
                        created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

            QUERY;

            $stmt = $this->connection->prepare($query);


            $stmt->bindValue(1,  $notificationToken->getUser()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(2,  $notificationToken->getPushToken(), PDO::PARAM_INT);
            $stmt->bindValue(3,  $notificationToken->getRefCode(), PDO::PARAM_STR);
            $stmt->bindValue(4,  $notificationToken->getDeviceNumber(), PDO::PARAM_STR);
            $stmt->bindValue(5,  $notificationToken->getIp(), PDO::PARAM_STR);
            $stmt->bindValue(6,  $notificationToken->getClient(), PDO::PARAM_STR);
            $stmt->bindValue(7,  $notificationToken->getPlatform(), PDO::PARAM_STR);
            $stmt->bindValue(8,  $notificationToken->getModelName(), PDO::PARAM_STR);
            $stmt->bindValue(9,  $notificationToken->isPushEnabled() ? 1:0, PDO::PARAM_INT);
            $stmt->bindValue(10, $notificationToken->isPhysicalDevice() ? 1:0, PDO::PARAM_INT);
            $stmt->bindValue(11, $notificationToken->getVersionRelease(), PDO::PARAM_STR);
            $stmt->bindValue(12, $notificationToken->getCreatedAt(), PDO::PARAM_STR);
            
            $stmt->execute();

            //again optional if on MyIASM or DB that doesn't support transactions
            $this->connection->commit();
        } catch (\PDOException $e) {
            //optional as above:
            $this->connection->rollback();

            //handle your exception here $e->getMessage() or something
        }

        return $notificationToken;
    }

    /***
     * Fetch  notification token by ref code
     * @param NotificationToken $notificationToken
     * @return NotificationToken 
     */
    public function fetchTokenByRefCode(NotificationToken $notificationToken):?NotificationToken 
    {

        $fields = self::$fieldsSubQuery;
    
        $query = <<<SUBQUERY
         SELECT
         $fields 
         FROM notification_token 
         WHERE code=? ORDER BY id DESC LIMIT 1
        SUBQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $notificationToken->getRefCode(), PDO::PARAM_INT);


        $items = [];

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                $notificationToken = new NotificationToken();
                $user = new User();
                $user->setId($data->user_id);
                $notificationToken->setId($data->user_id);
                $notificationToken->setID($data->id);
                $notificationToken->setUser($user);
                $notificationToken->setPushToken($data->token);
                $notificationToken->setDeviceNumberID($data->device_number);
                $notificationToken->setIp($data->ip);
                $notificationToken->setClient($data->client);
                $notificationToken->setCreatedAt($this->createDateTime($data->created_at));
                $notificationToken->setUpdatedAt($this->createDateTime($data->updated_at));
                $notificationToken->setDeletedAt($this->createDateTime($data->deleted_at));

            
                return $notificationToken;

            }
        }

        return array_shift($items); 
    }
    


/**
*  Get a notificationToken
* @param Request $request
* @param int $id
* @return NotificationToken|null
*/

public function findTokenByID(Request $request, int $id): ?NotificationToken
{

   $notificationToken = new NotificationToken();

  // $fields = 'n.id, n.user_id, n.token ,n.device_id AS device_number, n.ip, n.client, u.created_at,u.updated_at, u.deleted_at';

  // $query = 'SELECT ' . $fields . $this->mainQuery . ' AND u.id=?';

   $fields = self::$fields;
   $mainQuery = self::$mainQuery;
    
   $query = <<<FCMQUERY

         SELECT
         $fields
         $mainQuery 
         AND
         u.id = ?

       FCMQUERY;

   $stmt = $this->connection->prepare($query);

   $stmt->bindValue(1, $id, PDO::PARAM_INT);

   if ($stmt->execute()) {
       $data = $stmt->fetch(PDO::FETCH_OBJ);

       if (false != $data) {

           $user = new User();
           $user->setId($data->user_id);
           $notificationToken->setID($data->id);
           $notificationToken->setUser($user);
           $notificationToken->setPushToken($data->token);
           $notificationToken->setDeviceNumberID($data->device_number);
           $notificationToken->setIp($data->ip);
           $notificationToken->setClient($data->client);
           $notificationToken->setCreatedAt($this->createDateTime($data->created_at));
           $notificationToken->setUpdatedAt($this->createDateTime($data->updated_at));
           $notificationToken->setDeletedAt($this->createDateTime($data->deleted_at));

       }
   }

   return $notificationToken;
}

}


