<?php
declare(strict_types = 1);
namespace App\Services\Messaging\Messages\Push;
use App\Services\Messaging\MessageInterface;


interface FcmInterface
{
    public function sendBatchNotification($deviceTokens, $data, MessageInterface ...$messages);

    public function sendNotification($data, MessageInterface ...$messages):? int;

    public function subscribeTopic($deviceTokens, $topicName):? int;

    public function unsubscribeTopic($deviceTokens, $topicName):? int;
}