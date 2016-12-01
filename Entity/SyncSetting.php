<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FL\GmailDoctrineBundle\Form\Type\FromType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Store the userIds that should be synced.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class SyncSetting
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $domain;

    /**
     * User Ids being synced.
     *
     * @ORM\Column(type="array", nullable=false)
     * @Assert\NotBlank()
     *
     * @var string[]
     */
    protected $userIds;

    /**
     * Whenever a new instance of @see GmailMessage is created,
     * if the message's userId is in $userIdsCurrentlyFlagged,
     * the message should be marked as flagged.
     *
     * @see GmailMessage::$flagged
     *
     * @ORM\Column(type="array", nullable=true)
     *
     * @var string[]
     */
    protected $userIdsCurrentlyFlagged;

    /**
     * When sending an email using @see FromType, only addresses corresponding to
     * $userIdsAvailableAsFromAddress will be available as choices.
     *
     * @ORM\Column(type="array", nullable=true)
     *
     * @var string[]
     */
    protected $userIdsAvailableAsFromAddress;

    /**
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return SyncSetting
     */
    public function setDomain(string $domain): SyncSetting
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getUserIds()
    {
        return $this->userIds;
    }

    /**
     * @param array $userIds
     *
     * @return SyncSetting
     */
    public function setUserIds(array $userIds): SyncSetting
    {
        $this->userIds = $userIds;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getUserIdsCurrentlyFlagged()
    {
        return $this->userIdsCurrentlyFlagged;
    }

    /**
     * @param array|null $userIds
     *
     * @return SyncSetting
     */
    public function setUserIdsCurrentlyFlagged(array $userIds = null): SyncSetting
    {
        $this->userIdsCurrentlyFlagged = $userIds;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getUserIdsAvailableAsFromAddress()
    {
        return $this->userIdsAvailableAsFromAddress;
    }

    /**
     * @param array|null $userIds
     *
     * @return SyncSetting
     */
    public function setUserIdsAvailableAsFromAddress(array $userIds = null): SyncSetting
    {
        $this->userIdsAvailableAsFromAddress = $userIds;

        return $this;
    }

    /**
     * Every id in @see SyncSetting::$userIdsCurrentlyFlagged
     * must be in @see SyncSetting::$userIds
     * Every id in @see SyncSetting::$userIdsAvailableAsFromAddress
     * must be in @see SyncSetting::$userIds.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function correctUserIds()
    {
        $this->userIdsCurrentlyFlagged = array_intersect($this->userIdsCurrentlyFlagged, $this->userIds);
        $this->userIdsAvailableAsFromAddress = array_intersect($this->userIdsAvailableAsFromAddress, $this->userIds);
    }
}
