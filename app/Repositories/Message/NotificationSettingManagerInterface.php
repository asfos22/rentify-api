<?php
declare(strict_types = 1);
namespace App\Repositories\Message;

interface NotificationSettingManagerInterface
{

    /**
     * Sets or updates notification settings for a given account
     *
     * @param int $user
     * @param NotificationSetting $setting
     */
    public function setForUser(int $user, NotificationSetting $setting): void;

    /**
     * Gets notification settings for a given user
     *
     * @param int $user
     * @return NotificationSetting
     */
    public function getForUser(int $user): NotificationSetting;

    /**
     * Sets or updates notification settings for a given authentication token
     *
     * @param int $auth_token_id
     * @param NotificationSetting $setting
     */
    public function setForAuthToken(int $auth_token_id, NotificationSetting $setting): void;

    /**
     * Gets notification settings for a given authentication token
     *
     * @param int $auth_token_id
     * @return NotificationSetting
     */
    public function getForAuthToken(int $auth_token_id): NotificationSetting;
}

