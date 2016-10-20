<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FL\GmailBundle\Model\GmailIds as BaseGmailIds;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Store the gmailIds that need to be synced.
 * @ORM\MappedSuperclass
 */
class GmailIds extends BaseGmailIds
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @var string[]|null
     */
    protected $gmailIds = [];

    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @var string
     */
    protected $domain = '';
}
