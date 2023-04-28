<?php
declare(strict_types = 1);
namespace App\Repositories\System\Event;

use App\Repositories\Model;

class SystemBroadcaseChannel extends Model
{

    /**
     * Channel for communicating with all users on the system
     *
     * @var string
     */
    const CHANNEL_USERS_ALL = 'system.users';

    /**
     * Channel for communicating with all administrators on the system
     *
     * @var string
     */
    const CHANNEL_USERS_ADMINISTRATORS = 'system.administrators';


    /**
     * Channel for communicating with all host on the system
     *
     * @var string
     */
    const CHANNEL_USERS_HOST = 'system.host';

    /**
     * Channel for communicating with all guest on the system
     *
     * @var string
     */
    const CHANNEL_USERS_BUSINESSSES = 'system.guest';


    /**
     * Channel for communicating with chat all on the system
     *
     * @var string
     */
    const CHANNEL_USERS_MESSAGE_CHAT = 'system.users.chat.message';

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

