<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Store the userIds that should be synced
 * @ORM\MappedSuperclass
 */
class SyncSetting
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @var string
     */
    protected $domain;

    /**
     * @ORM\Column(type="array", nullable=false)
     * @Assert\NotBlank()
     * @var string[]
     */
    protected $userIds;

    /**
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
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
     * @return SyncSetting
     */
    public function setUserIds(array $userIds): SyncSetting
    {
        $this->userIds = $userIds;

        return $this;
    }
}
