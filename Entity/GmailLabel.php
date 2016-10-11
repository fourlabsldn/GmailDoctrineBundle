<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FL\GmailBundle\Model\GmailLabel as BaseLabel;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Stores labels applied to Gmail Messages.
 * @ORM\MappedSuperclass
 * @UniqueEntity(
 *     fields = {"name", "userId"},
 *     errorPath = "name",
 *     message = "Each userId may not correspond to more than one label the same name"
 * )
 */
class GmailLabel extends BaseLabel
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $userId;

    /**
     * Get label ID
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
}
