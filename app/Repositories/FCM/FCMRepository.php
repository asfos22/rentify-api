<?php

namespace App\Repositories\FCM;

use App\FCM\Firebase;
use App\FCM\Push;
use App\Repositories\Auth\Auth;
use App\Repositories\Conversation\Conversation;
use App\Repositories\Message\Message;
use App\Repositories\NotificationToken\NotificationToken;
use App\Repositories\Util\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDO;
use stdClass;

class FCMRepository implements FCMInterface
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

    private $messageSubQuery = <<<QUERY

        SELECT user_id FROM messages
         
    QUERY;


    private $conversationSubQuery = <<<QUERY
        SELECT user_id FROM conversation
     QUERY;

    private $fields = 'n.id, n.user_id, n.token ,n.device_id AS device_number, n.ip, n.client,
            u.created_at,u.updated_at, u.deleted_at';


    public function __construct()
    {
        $this->connection = DB::connection()->getPdo();

    }
    /**
     * @param Push $push
     * @param array $token
     * @param String $pushType
     * @param Message $message
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendFCM(Push $push, $token = [], String $pushType = '')
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

        } else if ($pushType === 'default') {
           
            $this->response = $firebase->send($token, $push);

            return response()->json([

                'response' => $this->response
            ]);
        }else if ($pushType === 'multiple') {

        $this->response = $firebase->sendMultiple('','', '',$push/*array($token), $push*/);

            return response()->json([

                'response' => $this->response
            ]);
        }else{

            //-- default 
            $this->response = $firebase->send(array($token), $push);

            return response()->json([

                'response' => $this->response
            ]);
        }

        return [];

    }




    /**
     * Send message FCM
     * @param Message $message
     * @return array|null
     */
    public function sendMessageFCM(Message $message): ?array
    {

        //u.is_physical_device = 1
        $query = 'SELECT ' . $this->fields . ' FROM notification_token n JOIN users u ON u.id = n.user_id WHERE user_id ' .
            ' IN ( ' . $this->messageSubQuery . ' where user_id = ?' .
            ' GROUP BY id ) AND n.blocked = 0';

        $stmt = $this->connection->prepare($query);


        $stmt->bindValue(1, $message->getUser()->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (false != $data) {

                return $data;

            }
        }

        return [];
    }

    /**
     * Send account account activated conversation FCM
     * @param Message $message
     * @return array|null
     */
    public function sendConversationFCM(Message $message): ?array
    {
        $query = 'SELECT ' . $this->fields . ' FROM notification_token n JOIN users u ON u.id = n.user_id WHERE user_id ' .
            ' IN ( ' . $this->conversationSubQuery . ' where message_id = ?' .
            ' GROUP BY id ) AND u.activated = 1 AND u.blocked = 0';

        // print ($query);
        $stmt = $this->connection->prepare($query);


        $stmt->bindValue(1, $message->getID(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (false != $data) {

                return $data;

            }
        }

        return [];

    }
}
