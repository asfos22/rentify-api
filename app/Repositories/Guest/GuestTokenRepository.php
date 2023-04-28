<?php

namespace App\Repositories\Guest;

use App\Http\AccessControl\AccessManagerInterface;
use App\Models\User;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\Token;
use App\Repositories\DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class GuestTokenRepository implements GuestTokenInterface
{
    /**
     * @var AccessManagerInterface
     */
    private $accessManager;

    private $table = 'guest';

    public function __construct(

        AccessManagerInterface $accessManager

    )
    {

        $this->accessManager = $accessManager;

    }


    /**
     * @param GuestToken $guestToken
     * @return mixed
     */
    public function fetchGuestToken(GuestToken $guestToken): array
    {
        $query = DB::table($this->table)
            ->where('device_id', $guestToken->getDeviceID())
            ->OrWhere('token', $guestToken->getToken())->latest()->first();

        return [$query];

    }


    /**
     * Get subscriber by  device and token
     * @param GuestToken $guestToken
     * @return mixed
     */
    public function fetchGuestTokenByDeviceIDToken(GuestToken $guestToken): ?array
    {
        // TODO: Implement fetchGuestTokenByDeviceIDToken() method.
    }


    /**
     * @param GuestToken $guestToken
     * @return mixed
     */
    public function fetchGuestTokenByID(GuestToken $guestToken): array
    {
        $query = DB::table($this->table)
            ->where('id', $guestToken->getId())->latest()->first();

        return [$query];

    }


    /**
     * @param GuestToken $token
     * @return mixed|void
     */
    public function verifyGuestToken(GuestToken $token)
    {

        /*    $tokenCheck = DB::table('auth_tokens')->where('auth_id', $)->whereDate('expires_at', ">", Carbon::now())
                ->orderBy('created_at', 'asc')->get();


            if (!count($tokenCheck) > 0) {

                return 401;
                //return $tokenCheck;
            }
         */

    }

    /**
     * @param Request $request
     * @return Auth|mixed
     * @throws \Exception
     */
    public function createGuestToken(Request $request): GuestToken
    {

        $ip = null;
        $client = null;
        $token = new GuestToken();
        $secret = strtoupper($this->accessManager->createRandomCode(64));

        if (null !== $request) {

            $ip = $request->ip();
            $client = $request->header('User-Agent');

        }

        $req_model_name = $request['model_name'];
        $req_device_id = $request['device_id'];
        $req_token = $request['fcm_token'];
        $req_physical = $request['physical'];
        $req_version_release = $request['version_release'];


        $token->setClient($client ?? null);
        $token->setIp($ip ?? null);
        $token->setModelName($req_model_name);
        $token->setDeviceID($req_device_id);
        $token->setSecret($secret);
        $token->setToken($req_token);
        $token->setPhysicalDevice($req_physical);
        $token->setVersionRelease($req_version_release);
        $token->setCreatedAt(new DateTime());
        $token->setUpdatedAt(new DateTime());

        //--
        $guestToken = $this->fetchGuestToken($token);


        if (!empty($guestToken[0])) {

            $token->setId($guestToken[0]->id);
            $token->setUpdatedAt(new DateTime());

            $this->updateGuestTokenByID($token);

            return $token;
        }

        $item = [
            'model_name' => $token->getModelName(),
            'is_physical_device' => $token->isPhysicalDevice(),
            'device_id' => $token->getDeviceID(),
            'version_release' => $token->getVersionRelease(),
            'token' => $token->getToken(),
            'ip' => $token->getIp(),
            'client' => $token->getClient(),
            'secret' => $token->getSecret(),
            'created_at' => $token->getCreatedAt()
        ];


        $tokenID = DB::table($this->table)->insertGetId($item);

        $token->setId($tokenID);


        return $token;

    }

    /**
     * @param Request $request
     * @param int $guestID
     * @return mixed
     */
    public function destroyGuestToken(Request $request, int $guestID)
    {
        // TODO: Implement destroyAccountToken() method.
    }

    /**
     * @param GuestToken $token
     * @return mixed|void
     */
    public function updateGuestTokenByID(GuestToken $token)
    {

        $item = [
            //'id' => $token->getId(),
            'model_name' => $token->getModelName(),
            'is_physical_device' => $token->isPhysicalDevice(),
            'device_id' => $token->getDeviceID(),
            'version_release' => $token->getVersionRelease(),
            'token' => $token->getToken(),
            'ip' => $token->getIp(),
            'client' => $token->getClient(),
            'secret' => $token->getSecret(),
            'updated_at' => $token->getUpdatedAt()
        ];

        DB::table($this->table)
            ->where('id', $token->getId())
            ->limit(1)
            ->update($item);


    }


    /**
     * @param GuestToken $token
     * @return mixed|void
     */
    public function destroyGuestTokenByID(GuestToken $token)
    {
        // TODO: Implement destroyGuestTokenByID() method.
    }


}
