<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Push;

use App\Repositories\ModelFormatter;
use stdClass;


class PushMessageMapper extends ModelFormatter
{

    public function __construct()
    {
        $fields = array(
            new Field('id', 'm.id', 'mId'),
            new Field('subject', 'm.subject', 'mSubject'),
            new Field('content', 'm.content', 'mContent'),
            new Field('payload', 'm.payload', 'mPayload'),
            new Field('is_read', 'm.is_read', 'mIsRead', true, false, new BoolConverter(), new BoolValidator()),
            new Field('read_at', 'm.read_at', 'mReadAt', true, false, new DateTimeConverter(), new DateTimeValidator()),
            new Field('created_at', 'm.created_at', 'mCreatedAt', true, false, new DateTimeConverter(), new DateTimeValidator()),
            new Field('updated_at', 'm.updated_at', 'mUpdatedAt', true, false, new DateTimeConverter(), new DateTimeValidator())
        );

        $userFields = array(
            new Field('user.id', 'u.id', 'uId'),
            new Field('user.name', 'u.name', 'uName'),
            new Field('user.phone', 'u.phone', 'uPhone'),
            new Field('user.email', 'u.email', 'uEmail'),
            new Field('user.activated', 'u.activated', 'uActivated', true, false, new BoolConverter(), new BoolValidator()),
            new Field('user.blocked', 'u.blocked', 'uBlocked', true, false, new BoolConverter(), new BoolValidator()),
            new Field('user.created_at', 'u.created_at', 'uCreatedAt', true, false, new DateTimeConverter(), new DateTimeValidator()),
            new Field('user.updated_at', 'u.updated_at', 'uUpdatedAt', true, false, new DateTimeConverter(), new DateTimeValidator())
        );

        $eventFields = array(
            new Field('event.id', 'e.id', 'eId'),
            new Field('event.description', 'e.description', 'eDescription'),
            new Field('event.code', 'e.code', 'eCode')
        );

        $stateFields = array(
            new Field('state.id', 's.id', 'sId'),
            new Field('state.name', 's.name', 'sName'),
            new Field('state.code', 's.code', 'sCode')
        );

        $fields = array_merge($fields, $userFields, $eventFields, $stateFields);

        $relations = array(
            new Relation('user', ...$userFields),
            new Relation('event', ...$eventFields),
            new Relation('state', ...$stateFields)
        );

        parent::__construct($fields, $relations);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\ModelFormatterInterface::format()
     */
    public function format(?stdClass $model)
    {
        if (empty($model)) {
            return null;
        }

        $message = new PushMessage();
        $message->setId($model->mId ?? null);
        $message->setSubject($model->mSubject ?? null);
        $message->setContent($model->mContent ?? null);
        $message->setPayload(json_decode($model->mPayload ?? 'null', true));
        $message->setRead($this->toBool($model->mIsRead ?? null));
        $message->setReadAt($this->createDateTime($model->mReadAt ?? null));
        $message->setCreatedAt($this->createDateTime($model->mCreatedAt ?? null));
        $message->setUpdatedAt($this->createDateTime($model->mUpdatedAt ?? null));
        $message->setUser($this->formatAccount($model, 'u'));
        
        $message->setEvent($this->formatSystemEvent($model, 'e'));
        $message->setState($this->formatMessageState($model, 's'));

        return $message;
    }
}

