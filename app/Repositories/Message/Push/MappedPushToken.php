<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Push;

use App\Repositories\IDModel;

class MappedPushToken
{

    /**
     *
     * @var IDModel
     */
    private $owner;

    /**
     *
     * @var string[]
     */
    private $tokens;

    /**
     *
     * @var string[]
     */
    private $topics;

    /**
     * Returns an array of topics that the token has been subscribed to
     *
     * @return string[]
     */
    public function getTopics(): array
    {
        return $this->topics ?? [];
    }

    /**
     * Sets topics that the token has been subscribed to
     *
     * @param string ...$topics
     */
    public function setTopics(string ...$topics)
    {
        $this->topics = $topics;
    }

    /**
     *
     * @return \App\Repositories\IDModel
     */
    public function getOwner(): IDModel
    {
        return $this->owner ?? new IDModel();
    }

    /**
     *
     * @return string[]
     */
    public function getTokens(): array
    {
        return $this->tokens ?? [];
    }

    /**
     *
     * @param \App\Repositories\IDModel $owner
     */
    public function setOwner(?IDModel $owner): void
    {
        $this->owner = $owner;
    }

    /**
     *
     * @param string ...$tokens
     */
    public function setTokens(string ...$tokens): void
    {
        $this->tokens = $tokens;
    }
}

