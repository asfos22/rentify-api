<?php

namespace App\Repositories\Message;

use App\Models\Conversation;
///use App\Models\Message;
use App\Repositories\NotificationToken\NotificationToken;
use App\Repositories\Property\Property;
use App\Repositories\User\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Repositories\Message\Message  as RMessage;
use App\Services\Messaging\Message;
          
interface MessageInterface
{


    /**
     * Send message
     * @param \App\Repositories\Message\Message $message
     * @param Property $property
     * @return Message
     */
    public function createRentHostMessage(RMessage $message, Property $property): RMessage;

    /**
     * @param String $sort
     * @param String $order
     * @param String $limit
     * @param int|null $userID
     * @return Collection
     */
    public function getMessages(String $sort, String $order, String $limit, int $userID = null): Collection;

    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $userID
     * @return array
     */
    public function fetchMessagesByUserID(int $userID,String $sort, String $order, int $limit): array;


    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $id
     * @return array
     */
    public function fetchMessagesByID(String $sort, String $order, int $limit, int $id): array;


    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param string|null $token
     * @return Message
     */
    public function fetchMessagesByToken(String $sort, String $order, int $limit, String $token):?RMessage;



    /**
    * Fetch  conversations by user's ID
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int|null $id
     * @return array
     */
   // public function fetchConversationsByUserID (int $userID, String $sort = 'created_at', String $order = 'DESC', int $limit = 100): array;

 

    /**
     * Fetch  conversation by user's ID
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return array
     */

    public function fetchConversationByID(String $sort, String $order, int $limit, int $id): array;

    /**
     * Get specific conversation by ID
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return array
     */
    public function fetchSpecificConversationByID(String $sort, String $order, int $limit, int $id): array;


    /**
     * @param String $sort
     * @param String $order
     * @param int $limit
     * @param int $id
     * @return RMessage
     */

    public function fetchConversationByMessageID(String $sort, String $order, int $limit, int $id): ?RMessage;


    /**
     * @param String $sort
     * @param String $order
     * @param String $limit
     * @param int $conversationID
     * @return RMessage
     */

    public function getConversation(String $sort, String $order, String $limit, int $conversationID): ?RMessage;

    /**
     * @param RMessage $message
     * @param User $user
     * @return mixed
     */
    public function createConversation(RMessage $message, User $user);//: Conversation;

    /**
     * @param String $messageToken
     * @return String
     */
    public function getSpecificMessage(String $messageToken): String;

    /**
     * @param int $id
     * @return array
     */
    public function fetchConversationIdsById (int $id):?array;

}
