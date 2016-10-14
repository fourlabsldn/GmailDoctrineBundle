<?php

namespace FL\GmailDoctrineBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Email
 * @package FL\GmailDoctrineBundle\Model
 *
 * Treat these as ValueObjects!
 */
class Thread
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $userId = '';

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $threadId = '';

    /**
     * @param string $userId
     * @param string $threadId
     */
    public function __construct(string $userId, string $threadId)
    {
        $this->userId = $userId;
        $this->threadId = $threadId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getThreadId(): string
    {
        return $this->threadId;
    }
}
