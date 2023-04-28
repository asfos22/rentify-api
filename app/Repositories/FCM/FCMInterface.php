<?php

namespace App\Repositories\FCM;

use App\FCM\Push;
use App\Repositories\Message\Message;

interface FCMInterface
{
    /**
     * Send message FCM
     * @param Message $message
     * @return array|null
     */
    public function sendMessageFCM(Message $message): ?array;

    /**
     * Send account account activated conversation FCM
     * @param Message $message
     * @return array|null
     */
    public function sendConversationFCM(Message $message): ?array;

    /**
     * @param Push $push
     * @param array $token
     * @param String $pushType
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendFCM(Push $push, $token =[], String $pushType ='');
}
