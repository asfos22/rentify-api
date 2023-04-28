<?php

namespace App\Repositories\Message;

use App\Http\Resources\MessageResource;
use App\Models\Conversation;
//use App\Models\Message;
use App\Repositories\Conversation\Conversation as RConversation;
use App\Repositories\DateTime;
use App\Repositories\FCM\FCMInterface;
use App\Repositories\Message\Message as RMessage;
use App\Repositories\Model;
use App\Repositories\Notification\NotificationInterface;
use App\Repositories\Property\Property;
use App\Repositories\Repository;
use App\Repositories\User\User;
use App\Services\Messaging\Message;
use App\Traits\Tokens;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class MessageRepository extends Repository implements MessageInterface
{

    private static $messageMainQuery = <<<QUERY

        WITH cteConversation (message_id, cConversation) AS (

           SELECT cv.message_id, JSON_ARRAYAGG(JSON_OBJECT(
                'name', u.name,
                'message', cv.message,
                'status', cv.status,
                'seen', cv.seen,
                'ref', nt.code,
                'created_at', cv.created_at
                    )) FROM conversation cv JOIN users u ON u.id = cv.user_id
                       LEFT JOIN notification_token nt ON nt.user_id = cv.user_id
                       GROUP BY  cv.message_id
                       ORDER BY cv.id
           ),
           cteCountConversation (message_id,ccConversation) AS (
            SELECT cv.message_id, JSON_OBJECT(
                'id', cv.message_id,
                'total', COUNT(IFNULL(cv.message_id, 1))
            )AS count_conversation FROM conversation cv
             LEFT JOIN messages m ON  m.id = cv.message_id
             WHERE cv.seen = 0 GROUP BY cv.message_id

            )
           SELECT
                 msg.id,
                 u.name,
                 msg.message,
                 msg.seen AS seen,
                 msg.token AS token,
                 nt.code  AS ref,
                 cm.cConversation AS conversation,
                 ccc.ccConversation AS count_conversation,
           DATE_FORMAT(msg.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
           DATE_FORMAT(msg.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
           FROM messages msg
           JOIN users u ON u.id = msg.user_id
           LEFT JOIN notification_token nt on nt.user_id= u.id
           LEFT JOIN cteCountConversation  ccc on ccc.message_id = msg.id
           LEFT JOIN cteConversation cm ON cm.message_id = msg.id

        QUERY;

    private static $conversationMainQuery = <<<QUERY

             WITH cteMessage (id, mMessage) AS (

                SELECT msg.id, JSON_ARRAYAGG(JSON_OBJECT(
                        'name', u.name,
                        'message', msg.message,
                        'seen', msg.seen,
                        'ref', nt.code,
                        'created_at', msg.created_at
                 )) FROM messages msg JOIN users u ON u.id = msg.user_id
                    LEFT JOIN notification_token nt ON nt.user_id = msg.user_id
                    GROUP BY msg.id ORDER BY msg.id
                )
                SELECT
                    conv.id,
                    u.name,
                    conv.message,
                    conv.status,
                    conv.seen AS seen,
                    conv.created_at,
                    nt.code AS ref,
                    cm.mMessage AS conversation,
                DATE_FORMAT(conv.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
                DATE_FORMAT(conv.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
                FROM conversation conv JOIN users u ON u.id = conv.user_id
                LEFT JOIN notification_token nt ON nt.user_id = u.id
                LEFT JOIN cteMessage cm ON cm.id = conv.id

        QUERY;

    private static $conversationMainQuery2 = <<<QUERY

        WITH cteMessage (message_id, cConversation) AS (

            SELECT cv.message_id, JSON_ARRAYAGG(JSON_OBJECT(
                'name', msg.id,
                'message', u.name,
                'seen', cv.seen,
                'token',cv.token,
                'ref', nt.code,
                'created_at', cv.created_at
                  ))FROM conversation cv JOIN users u ON u.id = cv.user_id
                    JOIN notification_token nt ON  cv.notification_token_id = nt.id
                    GROUP BY cv.message_id
                    ORDER BY cv.created_at DESC)
            SELECT
               msg.id,
               u.name,
               msg.message,
               msg.seen AS seen,
               msg.token AS token,
               nt.code AS ref,
               cm.cConversation AS conversation,
        DATE_FORMAT(msg.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
        DATE_FORMAT(msg.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at
        FROM messages msg JOIN users u ON u.id = msg.user_id
        JOIN notification_token nt on nt.user_id = u.id
        LEFT JOIN cteConversation cm ON cm.message_id = msg.id

       QUERY;

    private $mainQuery = <<<QUERY

      FROM notification_token n
      JOIN users u ON u.id = n.user_id

    QUERY;

    private $messageSubQuery = <<<QUERY

      user_id IN (
        SELECT user_id FROM messages
         GROUP BY id
           )
    QUERY;

    private $conversationSubQuery = <<<QUERY


    user_id FROM conversation

    QUERY;

    /**
     * @var NotificationInterface
     */
    private $notificationInterface;

    /**
     * @var
     */
    private $push;

    /**
     * @var
     */
    private $fcmInterface;

    /**
     * @var
     */

    private $connection;

    /**
     * @ tokens
     */
    use Tokens;

    public function __construct(

        NotificationInterface $notificationInterface,
        FCMInterface $fcmInterface

    ) {
        $this->connection = DB::connection()->getPdo();

        $this->notificationInterface = $notificationInterface;
        $this->fcmInterface = $fcmInterface;

    }

    /**
     * @param String $sort
     * @param String $order
     * @param String $limit
     * @param int|null $userID
     * @return Collection
     */

    public function getMessages(String $sort, String $order, String $limit, int $userID = null): Collection
    {

        return collect(

            MessageResource::collection(

                Message::with(
                    'user',
                    'conversation')->orderBy($sort, $order)
                    ->where('user_id', $userID)
                    ->limit($limit)->get()));

    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $userID
     * @return array
     */

    public function fetchMessagesByUserID(int $userID, String $sort = 'created_at', String $order = 'DESC', int $limit = 100): array
    {

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $messageSubQuery = self::$messageMainQuery;

        $query = <<<SUBQUERY

         $messageSubQuery

            WHERE msg.user_id = ? OR msg.sender_id = ? AND u.blocked = 0 AND u.activated = 1
            GROUP BY msg.id ORDER BY msg.$sort $order LIMIT $limit
            OFFSET 0

        SUBQUERY;

        // msg.user_id = ? AND nt.code = ?
        //dump(" USER ID ",$userID);
        // exit();

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $userID, PDO::PARAM_INT);
        $stmt->bindValue(2, /*"e4cbce2280469c7c85a93a37"*/ $userID, PDO::PARAM_INT);
        //$stmt->bindValue(3, /*"e4cbce2280469c7c85a93a37"*/ $userID, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return array_map(function ($p) {
                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }
        return [];
    }

    /**
     * Send message
     * @param \App\Repositories\Message\Message $message
     * @param Property $property
     * @return Message
     */
    public function createRentHostMessage(RMessage $message, Property $property): RMessage
    {

        $message->setToken($this->token(64));

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY
            INSERT INTO messages (
                message,
                channel_id,
                house_id,
                token,
                user_id,
                sender_id,
                notification_token_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

            $messageText = explode("\n", $message->getText())[0];

            $stmt->bindValue(1, $messageText, PDO::PARAM_STR);
            $stmt->bindValue(2, 1, PDO::PARAM_INT);
            $stmt->bindValue(3, $property != null ? $property->getID() : null, PDO::PARAM_STR);
            $stmt->bindValue(4, $message->getToken() != null ? $message->getToken() : null);
            $stmt->bindValue(5, $message->getReceiver()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(6, $message->getUser()->getId() != null ? $message->getUser()->getId() : null, PDO::PARAM_INT);
            $stmt->bindValue(7, null, PDO::PARAM_STR);

            $stmt->execute();

            $messageId = (int) $this->connection->lastInsertId();

            $this->connection->commit();

            //$message = new RMessage();
            $message->setID($messageId);
            // $message->setText($messageText);
            // $message->setReceiver($message->getReceiver()->getId());

            return $message;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
        return $message;
    }

    /**
     * @param String $sort
     * @param String $order
     * @param String $limit
     * @param int $conversationID
     * @return RMessage
     */

    public function getConversation(String $sort, String $order, String $limit, int $conversationID): ?RMessage
    {

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $conversationSubQuery = self::$conversationMainQuery;

        $query = <<<SUBQUERY

           $conversationSubQuery

            WHERE u.blocked = 0 AND u.activated = 1 AND conv.id = ?
            GROUP BY conv.id
            ORDER BY conv.$sort  $order LIMIT $limit OFFSET 0

        SUBQUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $conversationID, PDO::PARAM_INT);

        $items = [];

        if ($stmt->execute()) {
            $items = array_map(function ($model) {

                //dump($model->created_at);
                //exit();
                $message = new RMessage();
                $message->setName($model->name ?? '');
                $message->setText($model->message);
                $message->setStatus($model->seen);
                //$message->setToken();
                $message->setReference($model->ref);
                $message->setCreatedAt($this->createDateTime(empty($model->created_at) ? date('Y-m-d') : (string) $model->created_at));

                return $message;

                //return $this->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($items);

        exit();

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $messageSubQuery = self::$messageMainQuery;

        $query = <<<SUBQUERY

            $messageSubQuery

               WHERE msg.token = ? AND u.blocked = 0 AND u.activated = 1
               GROUP BY msg.id ORDER BY msg.created_at desc
               LIMIT 1 OFFSET 0

           SUBQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_INT);

        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {
                return $this->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($items);

    }

    /*{
    return collect(

    ConversationResource::collection(

    Conversation:: select('id', 'user_id', 'seen', 'message', 'created_at')
    ->where('message_id', '=', $messageID)
    ->orderBy($sort, $order)
    // ->where('id', 1)
    ->limit($limit)->get()

    )

    );*/

    /**
     * @param \App\Repositories\Message\Message $message
     * @param User $user
     * @return RMessage
     */
    public function createConversation(RMessage $message, User $user)
    {
        //$message->setToken($this->token(64));
        /// $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();
        // --
        try {
            $query = <<<CONVOQUERY

            INSERT INTO conversation (
                message,
                status,
                message_id,
                seen,
                user_id
            )
            VALUES (?, ?, ?, ?, ?)
            CONVOQUERY;

            //--------
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(1, $message->getConversation()[0]->getText(), PDO::PARAM_STR);
            $stmt->bindValue(2, $message->getStatus() ?? null, PDO::PARAM_STR);
            $stmt->bindValue(3, $message->getID() ?? null, PDO::PARAM_STR);
            $stmt->bindValue(4, $message->getConversation()[0]->getStatus() ?? 1, PDO::PARAM_INT);
            $stmt->bindValue(5, $user->getId(), PDO::PARAM_INT);

            $stmt->execute();

            $messageId = (int) $this->connection->lastInsertId();

            $this->connection->commit();
            $message->setID($messageId);

            return $message;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
        return $message;
    }

    /**
     * @param String $messageToken
     * @return String
     */
    public function getSpecificMessage(String $messageToken): String
    {

        $token = DB::table('messages')
            ->select(
                'messages.id as id'
            )
            ->where('token', $messageToken)
            ->first();

        if ($token != null || !(empty($token))) {

            return $token->id;
        }

        return '';

    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param string|null $token
     * @return array
     */
    public function fetchMessagesByToken(String $sort, String $order, int $limit, String $token): ?RMessage
    {

        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $messageSubQuery = self::$messageMainQuery;

        $query = <<<SUBQUERY

            $messageSubQuery

               WHERE msg.token = ? AND u.blocked = 0 AND u.activated = 1
               GROUP BY msg.id ORDER BY msg.created_at desc
               LIMIT 1 OFFSET 0

           SUBQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_INT);

        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {
                return $this->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($items);

    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return RMessage
     */
    public function fetchConversationByMessageID(String $sort, String $order, int $limit, int $id): ?RMessage
    {

        $messageSubQuery = self::$messageMainQuery;

        $query = <<<SUBQUERY

        $messageSubQuery

           WHERE msg.id = ? AND u.blocked = 0 AND u.activated = 1
           GROUP BY msg.id ORDER BY msg.created_at desc
           LIMIT 1 OFFSET 0

       SUBQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {
                return $this->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($items);

    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return array
     */
    public function fetchConversationByID(String $sort, String $order, int $limit, int $id): array
    {

        /**
         * Allows [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $query = self::$conversationMainQuery . ' JOIN conversation con ON con.message_id = msg.id ' . ' WHERE ' .
            'u.blocked = 0' . ' AND  u.activated = 1' .
            ' AND con.id = ?' . ' GROUP BY msg.id ORDER BY con.' .
            $sort . ' ' . $order . ' LIMIT ' . $limit . ' OFFSET 0';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return array_map(function ($p) {

                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];
    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $id
     * @return array
     */
    public function fetchMessagesByID(String $sort, String $order, int $limit, int $id): array
    {

        $messageMainQuery = self::$messageMainQuery;

        $query = <<<SUB_QUERY

            $messageMainQuery

            WHERE msg.id = ? AND u.blocked = 0 AND u.activated = 1 GROUP BY msg.id ORDER BY msg.$sort $order LIMIT $limit OFFSET 0
            SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return array_map(function ($p) {
                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];
    }

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $userID
     * @return array
     */

    public function fetchConversationsByUserID(int $userID, String $sort = 'created_at', String $order = 'DESC', int $limit = 100): array
    {
        /**
         * Allow to [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $messageSubQuery = self::$messageMainQuery;

        $query = <<<SUBQUERY

         $messageSubQuery

            WHERE msg.user_id = ? AND u.blocked = 0 AND u.activated = 1
            GROUP BY msg.id ORDER BY msg.$sort $order LIMIT $limit
            OFFSET 0

        SUBQUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $userID, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return array_map(function ($p) {
                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }
        return [];
    }

    /**
     * Get specific conversation by ID
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return array
     */
    public function fetchSpecificConversationByID(String $sort, String $order, int $limit, int $id): array
    {

        /**
         * Allows [incompatible with sql_mode=only_full_group_by] suppression
         */
        \DB::statement("SET SQL_MODE=''");

        $messageMainQuery = self::$conversationMainQuery;

        $query = <<<SUB_QUERY

                $messageMainQuery

                JOIN messages msg ON conv.message_id = msg.id WHERE u.blocked = 0
                AND u.activated = 1 AND conv.id = ?
                ORDER BY conv.$sort  $order LIMIT $limit OFFSET 0


                SUB_QUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return array_map(function ($p) {

                return $this->format($p);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];
    }

    private function formatMessage(?stdClass $model): ?RMessage
    {
        if (empty($model)) {
            return null;
        }

        $message = new RMessage();

        $message->setName(empty($model->name) ? null : (int) $model->name);
        $message->setSender(empty($model->sender) ? null : (int) $model->sender);
        //$message->setReceiverID($this->property->getHost()->getUserToken());
        $message->setText(empty($model->text) ? null : (int) $model->text);
        $message->setUser(empty($model->user) ? null : (int) $model->user);

        return $message;
    }

    /* public function fetchConversationIDSByid (int $id):?array
    {
    dump("GRA", $id);
    return [];
    }*/

    public function fetchConversationIdsById(int $id): ?array
    {

        $query = <<<SUB_QUERY

            SELECT DISTINCT u.id AS user_id FROM rentify_db101.conversation conv
            JOIN messages msg ON conv.message_id = msg.id
            JOIN users u ON conv.user_id = u.id
            JOIN notification_token nt on nt.user_id = conv.user_id
            WHERE conv.message_id = ?
            GROUP BY conv.id;

            SUB_QUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        /*$item = [];
        if ($stmt->execute()) {
        $item = array_map(function ($model) {
        dump($model);
        return $this->formatConversation($model);//$model;
        }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }
        return $item;*/
        if ($stmt->execute()) {

            return array_map(function ($p) {
                return $p->user_id;
            }, $stmt->fetchAll(PDO::FETCH_OBJ));

        }

        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message
     */
    public function format(?stdClass $model): ?Model
    {
        if (empty($model)) {
            return null;
        }

        $message = new RMessage();

        if (isset($model->count_conversation)) {

            $decodeCountItem = json_decode($model->count_conversation);
        }

        $message->setID($model->id);
        $message->setName($model->name);
        $message->setReference($model->ref);
        $message->setText($model->message);
        $message->setStatus($model->seen);
        if (isset($model->token)) {
            $message->setToken($model->token ?? '');
            $message->setLink(route('messages/user/message/conversations', ['token' => $model->token]));
        }
        $message->setCountConvo($decodeCountItem->total ?? 0);
        // $message->setLink('http://10.0.2.2/rentify-api/public/api/messages/user/message/conversation'); //todo remove before production
        $message->setCreatedAt($this->createDateTime(empty($model->created_at) ? date('Y-m-d') : (string) $model->created_at));
        $message->setHumanCreatedAt($this->createDateTime(empty(date('m/d/Y', strtotime($model->created_at))) ? null : date('m/d/Y', strtotime($model->created_at))));

        if (isset($model->conversation)) {
            $decodeItem = json_decode($model->conversation);

            if (is_array($decodeItem) && count($decodeItem)) {
                $itemArray = array_map(function ($conversationModel) {

                    $mConversation = new RConversation();

                    $mConversation->setName(isset($conversationModel->name) ? $conversationModel->name : null);
                    $mConversation->setText(isset($conversationModel->message) ? $conversationModel->message : null);
                    if (isset($conversationModel->status)) {
                        $mConversation->setStatus(isset($conversationModel->status) ? $conversationModel->status : null);
                    }
                    if (isset($conversationModel->seen)) {
                        $mConversation->setSeen(isset($conversationModel->seen) ? $conversationModel->seen : null);
                    }
                    $mConversation->setReference(isset($conversationModel->ref) ? $conversationModel->ref : null);

                    $mConversation->setCreatedAt($this->createDateTime(empty($conversationModel->created_at) ? date('Y-m-d') : (string) $conversationModel->created_at));
                    $mConversation->setHumanCreatedAt($this->createDateTime(empty(date('m/d/Y', strtotime($conversationModel->created_at))) ? null : date('m/d/Y', strtotime($conversationModel->created_at))));

                    return $mConversation;
                }, $decodeItem);

                $message->setConversation(...$itemArray);
            }
        }
        return $message;

    }

}
