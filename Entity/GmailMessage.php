<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use FL\GmailBundle\Model\GmailMessage as BaseGmailMessage;
use FL\GmailBundle\Model\GmailMessageInterface;
use FL\GmailBundle\Model\GmailLabelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stores the relevant fields of Gmail Message, including its labels.
 * @ORM\Entity(repositoryClass="FL\GmailDoctrineBundle\Entity\GmailMessageRepository")
 */
class GmailMessage extends BaseGmailMessage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @var string
     */
    protected $gmailId;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected $threadId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $historyId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotNull
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(name="message_to", type="string", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $to;

    /**
     * @ORM\Column(name="message_from", type="string", nullable=false)
     * @Assert\NotNull
     * @var string
     */
    protected $from;

    /**
     * @ORM\Column(type="datetimetz", nullable=false)
     * @Assert\NotNull
     * @var \DateTimeInterface
     */
    protected $sentAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $subject;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="GmailLabel", cascade={"persist"})
     */
    protected $labels;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $snippet;

    /**
     * GmailMessage constructor.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
    }

    /**
     * Set Gmail Message ID.
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get Gmail Message ID.
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function addLabel(GmailLabelInterface $label): GmailMessageInterface
    {
        $this->labels->add($label);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @inheritdoc
     */
    public function removeLabel(GmailLabelInterface $label): GmailMessageInterface
    {
        $this->labels->removeElement($label);

        return $this;
    }

    /**
     * Returns true if the message has the specified label, false otherwise.
     * @param string $name
     * @return bool
     */
    // NOTE: If this will be used for anything other than isUnread and isDeleted,
    // the $name string will have to be cleaned up.
    public function hasLabel(string $name): bool
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', $name));
        return (count($this->labels->matching($criteria)) > 0);
    }

    /**
     * Return whether this message is unread.
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->hasLabel('UNREAD');
    }

    /**
     * Return whether this message is deleted.
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->hasLabel('TRASH');
    }
}
