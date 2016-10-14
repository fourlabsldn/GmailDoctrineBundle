<?php

namespace FL\GmailDoctrineBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Email
 * @package FL\GmailDoctrineBundle\Model
 */
class ThreadCollection
{
    /**
     * @var \SplObjectStorage
     */
    private $threads;

    public function __construct()
    {
        $this->threads = new \SplObjectStorage();
    }

    /**
     * @return \SplObjectStorage
     */
    public function getThreads(): \SplObjectStorage
    {
        return $this->threads;
    }

    /**
     * @param Thread $thread
     * @return ThreadCollection
     */
    public function addThread(Thread $thread): ThreadCollection
    {
        if (! $this->isThreadInCollection($thread)) {
            $this->threads->attach($thread);
        }

        return $this;
    }

    /**
     * @param Thread $thread
     * @return ThreadCollection
     */
    public function removeThread(Thread $thread): ThreadCollection
    {
        if ($this->isThreadInCollection($thread)) {
            $this->threads->detach($thread);
        }

        return $this;
    }

    /**
     * @param Thread $thread
     * @return boolean
     */
    private function isThreadInCollection(Thread $thread)
    {
        /** @var Thread $thread */
        foreach ($this->threads as $threadInCollection) {
            if ($threadInCollection == $thread) { // == compare as value objects!!!
                return true;
            }
        }
        return false;
    }
}
