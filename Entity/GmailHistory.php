<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FL\GmailBundle\Model\GmailHistory as BaseGmailHistory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Store the historyId of the most recent email for this user
 * whenever a sync is executed. This way in the future it can be synced
 * from that point onwards.
 *
 * @ORM\MappedSuperclass
 */
class GmailHistory extends BaseGmailHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $historyId;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $domain = '';
}
