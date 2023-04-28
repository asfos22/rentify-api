<?php

namespace App\Repositories\Notification;

use App\FCM\Push;
use App\Repositories\NotificationToken\NotificationToken;
use Illuminate\Notifications\Notification;

interface NotificationInterface
{
    /**
     * @param Push $push
     * @param array $token
     * @param String $pushType
     * @return mixed
     */
    public function createNotification(String $pushType,Push $push, $token = []);

    /**
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function createNotificationToken(NotificationToken $notificationToken): ?NotificationToken;

    /***
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenByName(NotificationToken $notificationToken);


    /***
     * Fetch  notification token by ref code
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenByRefCode(NotificationToken $notificationToken): ?NotificationToken ;

    /**
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenDeviceID(NotificationToken $notificationToken);

    /**
     * update the token
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function updateTokenByName(NotificationToken $notificationToken): ?NotificationToken;


    /**
     * insert notification token
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function insertToken(NotificationToken $notificationToken): ?NotificationToken;

    
     /***
     * Fetch  notification token by user id
     * @param NotificationToken $notificationToken
     * @return mixed
     */
    public function fetchTokenByUserID(NotificationToken $notificationToken);


}
