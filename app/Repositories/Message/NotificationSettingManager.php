<?php
declare(strict_types = 1);
namespace App\Repositories\Message;

use App\Repositories\DateTime;
use Illuminate\Support\Facades\DB;
use PDO;

class NotificationSettingManager implements NotificationSettingManagerInterface
{

    /**
     *
     * @var PDO
     */
    private $connection;

    public function __construct(/*PDO $connection*/)
    {
       // $this->connection = $connection;
        $this->connection = DB::connection()->getPdo();
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\NotificationSettingManagerInterface::setForUser()
     */
    public function setForUser(int $user, NotificationSetting $setting): void
    {
        $now = (new DateTime())->toMysqlDateTime();

        $query = <<<QUERY
        INSERT INTO notification_settings (user_id, push, sms, email, created_at) VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE push = ?, sms = ?, email = ?, updated_at = ?
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $user, PDO::PARAM_INT);
        $stmt->bindValue(2, $setting->isPush() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(3, $setting->isSms() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(4, $setting->isEmail() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(5, $now, PDO::PARAM_STR);
        $stmt->bindValue(6, $setting->isPush() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(7, $setting->isSms() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(8, $setting->isEmail() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(9, $now, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\NotificationSettingManagerInterface::getForUser()
     */
    public function getForUser(int $user): NotificationSetting
    {
        $query = <<<QUERY
        SELECT s.id, s.push, s.sms, s.email, s.created_at, s.updated_at FROM notification_settings s WHERE s.user_id = ? LIMIT 1;
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $user, PDO::PARAM_INT);

        $stmt->execute();

        $setting = new NotificationSetting();
        $setting->setPush(false);
        $setting->setSms(false);
        $setting->setEmail(false);

        if ($model = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $setting->setId($model['id'] ?? null);
            $setting->setPush(boolval($model['push'] ?? null));
            $setting->setSms(boolval($model['sms'] ?? null));
            $setting->setEmail(boolval($model['email'] ?? null));
        }

        return $setting;
    }

    /**
     *
     * {@inheritdoc}
     * @see App\Repositories\Message\NotificationSettingManagerInterface::setForAuthToken()
     */
    public function setForAuthToken(int $auth_token_id, NotificationSetting $setting): void
    {
        $now = (new DateTime())->toMysqlDateTime();

        $query = <<<QUERY
        UPDATE auth_tokens SET  push_enabled = ?, updated_at = ? WHERE id = ? LIMIT 1
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $setting->isPushEnabled() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(2, $now, PDO::PARAM_STR);
        $stmt->bindValue(3, $auth_token_id, PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\NotificationSettingManagerInterface::getForAuthToken()
     */
    public function getForAuthToken(int $auth_token_id): NotificationSetting
    {
        return new NotificationSetting();
    }
}

