<?php

namespace App\Repositories\NotificationToken;

use App\Http\AccessControl\AccessManagerInterface;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\Token;
use App\Repositories\DateTime;
use App\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\User\User;
use PDO;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class NotificationTokenRepository extends Repository implements NotificationTokenInterface
{
    private $mainQuery = <<<QUERY
     FROM notification_token n
     JOIN users u ON u.id = n.user_id
     WHERE u.blocked = 0
    QUERY;

    private $subQuery = <<<QUERY
     FROM notification_token n
     JOIN users u ON u.id = n.user_id
     WHERE u.blocked = 0;
    QUERY;


    protected $connection;


    public function __construct()
    {
        $this->connection = DB::connection()->getPdo();

    }


    /**
     *  Get a notificationToken
     * @param Request $request
     * @param int $id
     * @return NotificationToken|null
     */

    public function findToken(Request $request, int $id): ?NotificationToken
    {

        $notificationToken = new NotificationToken();

        $fields = 'n.id, n.user_id, n.token ,n.device_id AS device_number, n.ip, n.client,
            u.created_at,u.updated_at, u.deleted_at';

        $query = 'SELECT ' . $fields . $this->mainQuery . ' AND u.id=?';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                $user = new User();
                $user->setId($data->user_id);
                $notificationToken->setID($data->id);
                $notificationToken->setUser($user);
                $notificationToken->setPushToken($data->token);
                $notificationToken->setDeviceNumberID($data->device_number);
                $notificationToken->setIp($data->ip);
                $notificationToken->setClient($data->client);
                $notificationToken->setCreatedAt($this->createDateTime($data->created_at));
                $notificationToken->setUpdatedAt($this->createDateTime($data->updated_at));
                $notificationToken->setDeletedAt($this->createDateTime($data->deleted_at));

            }
        }

        return $notificationToken;
    }


    /**
     * @param Request $request
     * @param int $userID
     * @return mixed
     */
    public function createToken(Request $request, int $userID)
    {
        // TODO: Implement createToken() method.
    }

    /**
     * @param Request $request
     * @param int $userID
     * @return mixed
     */
    public function destroyToken(Request $request, int $userID)
    {
        // TODO: Implement destroyToken() method.
    }

    /**
     * @param Request $request
     * @param int $accountID
     * @return mixed
     */
    public function changeToken(Request $request, int $accountID)
    {
        // TODO: Implement changeToken() method.
    }


}
