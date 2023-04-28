<?php
namespace App\Services\Messaging\Messages\Push;

use App\Services\Messaging\MessageInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;

class FcmService implements FcmInterface
{

    /**
     * @var fcm url
     */
    private $fcmURL;
    /**
     * @var content type
     */
    private $contentType;

    /**
     * @var authorizationKey
     */
    private $authorizationKey;

    
    public function __construct(

    ) {
        $config = Config::get('custom.firebase');

        $this->fcmURL = $config['fcmURL'];
        $this->contentType = $config['contentType'];
        $this->authorizationKey = $config['authorizationKey'];
    }
    /**
     * @param $deviceTokens
     * @param $data
     * @param MessageInterface ...$messages
     * @throws GuzzleException
     */
    public function sendBatchNotification($deviceTokens, $data = [], MessageInterface ...$messages)
    {
        //$conversation,

        // self::subscribeTopic($deviceTokens, $data['topicName']);
        self::sendNotification($data, $deviceTokens, ...$messages/*$data['topicName']*/);
        //self::unsubscribeTopic($deviceTokens, $data['topicName']);
    }

    /**
     * Send notificaion to registered FCM token
     * @param $data
     * @param $topicName
     * @throws GuzzleException
     */
    public function sendNotification($data, $token = null, MessageInterface ...$messages): ?int
    {
        foreach ($messages as $message) {

            $data = [
                'content_available' => true,
                'registration_ids' => $token,
                'priority' => 'high',
                'sound' => true,
                'vibrate' => true,
                'clearNotifications' => true,
                'forceShow' => true,
                'notification' => [
                    'body' => $message->getRecipient()->getContent() /*$data['body']*/ ?? 'New notification',
                    'title' => $message->getRecipient()->getSubject() /*$data['title']*/ ?? 'New message from Rent App',
                    'image' => $data['image'] ?? null,
                    "content_available" => true,
                    'click_action' => 'FCM_OPEN_ACTIVITY',
                    'badge' => '1',
                ],

                /*'data' => $message->getPayload(), */
               // 'data' => $message->getPayload() ?? [],
               'data' =>$message->getPayload()!=null? $message->getPayload(): null, //json_encode($message->getPayload()) ?? null,
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'mutable-content' => 1,
                        ],
                    ],
                    'fcm_options' => [
                        'image' => $data['image'] ?? null,
                    ],
                ],
            ];

            return $this->execute($this->fcmURL, $data);
            //$this->formatNotification($data, $token = null, $message);
        }
        return [];
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function subscribeTopic($deviceTokens, $topicName = null): ?int
    {
       // $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        return $this->execute($this->fcmURL, $data);
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */
    public function unsubscribeTopic($deviceTokens, $topicName = null): ?int
    {
       //$url = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];
        return $this->execute($this->fcmURL, $data);
    }

    /**
     * @param $url
     * @param array $dataPost
     * @param string $method
     * @return bool
     * @throws GuzzleException
     */
    private function execute($url, $dataPost = []): ?int
    {

        $result = false;

        try {

            // Set POST variables
            $client = new Client();

            $result = $client->post($url, [
                'json' => $dataPost,
                'timeout' => 300,
                'headers' => [
                    'Authorization' => sprintf('%s%s', 'key=', $this->authorizationKey),
                    'Content-Type' => $this->contentType,
                ],
            ]);

            $result = $result->getStatusCode() == 200; // Response::HTTP_OK;

            return $result;

        } catch (Exception $e) {
            // dd($e);
            // Log::debug($e);
            return 401;

        }

        return $result;
    }

    /**
     * @param $deviceToken
     * @param $topicName
     * @throws GuzzleException
     */

    private function formatNotification($data, $token = null, MessageInterface $messages)
    {

        $data = [
            'content_available' => true,
            'registration_ids' => $token,
            'priority' => 'high',
            'sound' => true,
            'vibrate' => true,
            'clearNotifications' => true,
            'forceShow' => true,
            'notification' => [
                'body' => $data['body'] ?? 'New notification',
                'title' => $data['title'] ?? 'New message from Rent App',
                'image' => $data['image'] ?? null,
                "content_available" => true,
                'click_action' => 'FCM_OPEN_ACTIVITY',
                'badge' => '1',
            ],
            'data' => [
                'url' => $data['url'] ?? null,
                'redirect_to' => $data['redirect_to'] ?? null,
                "body" => $data['body'],
                "title" => $data['title'],
                "key_1" => "Value for key_1",
                "image_url" => "https://via.placeholder.com/150",
                //"image_url"=> "www.abc.com/xyz.jpeg",
                "key_2" => "Value for key_2",
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                    ],
                ],
                'fcm_options' => [
                    'image' => $data['image'] ?? null,
                ],
            ],
        ];

        $this->execute($this->fcmURL, $data);
    }
}
