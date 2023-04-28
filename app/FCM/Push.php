<?php

namespace App\FCM;

class Push
{

    /**
     * @var
     */
    private $title;
    private $message;
    private $data;
    private $id;
    private $unread;
    private $ref;
    private $isLiked;
    private $sender;
    private $time;
    private $date;
    private $humanDate;
    private $media;



    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param $unread
     */
    public function setUnread($unread)
    {
        $this->unread = $unread;
    }

    /**
     * @param $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @param $isLiked
     */
    public function setIsLiked($isLiked)
    {
        $this->isLiked = $isLiked;
    }

    /**
     * @param $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param $humanDate
     *
     */
    public function setHumanDate($humanDate)
    {
        $this->humanDate = $humanDate;
    }

    /**
     * @param $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @param $data
     */
    public function setPayload($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getPush()
    {
        $res = array();

        $res['id'] = $this->id ?? null;
        $res['unread'] = $this->unread ?? null;
        $res['title'] = $this->title ?? null;
        $res['message'] = $this->message ?? null;
        $res['isLiked'] = $this->isLiked ?? null;
        $res['ref'] = $this->ref ?? null;
        $res['sender'] = $this->sender ?? null;
        $res['time'] = $this->time ?? null;
        $res['date'] = $this->date ?? null;
        $res['humanDate'] = $this->humanDate ?? null;
        $res['media'] = $this->media ?? null;
        $res['timestamp'] = date('Y-m-d G:i:s');

        return $res;
    }

}
