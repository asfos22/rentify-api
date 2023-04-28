<?php

namespace App\FCM;

use GuzzleHttp\Client;
use App\FCM\Config;

class Firebase
{

    /**
     * Sending push message to single user by firebase reg id
     * @param $to
     * @param Push $push
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(array $to, Push $push)
    {
        $registration_ids = array($to);

        $fields = array(
            /*'to' => implode(',', array_map(function ($f) {
                return $f[0];
            }, $registration_ids)),*/
            //'to' => $to,
            "registration_ids" => $to,
            "notification" => [
                "title" => $push->getPush()['title'] ?? null,
                "text" => $push->getPush()['message'] ?? null,
                "image" => $push->getPush()['media'] ?? null
            ],
            'data' => $push->getPush() ?? null,
        );
        return $this->sendPushNotification($fields);

    }


    /**
     * Sending message to a topic by topic name
     * @param $topic
     * @param $title
     * @param $text
     * @param $data
     * @param $image
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendToTopic($topic, $title, $text, $data, $image)
    {

        $notification = array(

            'title' => $title,
            'text' => $text,
            'image' => $image,
            'sound' => 'default',
            'badge' => '1',);
            $arrayToSend = array(
            'to' => "/topics/" . $topic,
            'notification' => $notification,
            'data' => $data, 'priority' => 'high');

        return $this->sendPushNotification($arrayToSend);
    }


    /**
     * Sending push message to multiple users by firebase registration ids
     * @param $registration_ids
     * @param $title
     * @param $text
     * @param $message
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendMultiple($registration_ids, $title, $text, $message)
    {
        $fields = array(
            //'to' => $registration_ids,
            'to' => $registration_ids,
            "notification" => [
                "title" => $title,
                "text" => $text,
            ],
            'data' => $message,
        );

        // print($fields);

        return $this->sendPushNotification($fields);
    }

    /**
     * POST request to firebase servers
     * @param $fields
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendPushNotification($fields)
    {
       
        // Config
        $config = new Config();

        // Set POST variables
        $client = new Client();

        $result = $client->post($config->fcmURL, [
            'json' =>
                $fields,
            'headers' => [
                'Authorization' => 'key=' . $config->authorizationKey,
                'Content-Type' => $config->contentType,
            ],
        ]);

        return json_decode($result->getBody(), true);

    }
}

