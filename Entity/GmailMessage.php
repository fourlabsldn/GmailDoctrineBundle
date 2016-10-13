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
 * @ORM\MappedSuperclass
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
     * Not being used for anything at the moment.
     * Nevertheless, since each message has a unique historyId for its corresponding userId,
     * this historyId can be useful in the event that the latest historyId is not available elsewhere.
     * In this case, the latest historyId is simply the historyId with the largest value.
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
     * @ORM\Column(type="datetime", nullable=false)
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
     * @return GmailMessageInterface
     */
    public function clearLabels(): GmailMessageInterface
    {
        $this->labels = new ArrayCollection();

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasLabel(string $name): bool
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', $name));
        return ($this->labels->matching($criteria)->count() > 0);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function doesNotHaveLabel(string $name): bool
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', $name));
        return ($this->labels->matching($criteria)->count() === 0);
    }

    /**
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->doesNotHaveLabel('UNREAD');
    }

    /**
     * @return bool
     */
    public function isUnread(): bool
    {
        return $this->hasLabel('UNREAD');
    }

    /**
     * @return bool
     */
    public function isInbox(): bool
    {
        return $this->hasLabel('INBOX');
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->hasLabel('SENT');
    }

    /**
     * @return bool
     */
    public function isTrash(): bool
    {
        return $this->hasLabel('TRASH');
    }

    /**
     * @return bool
     */
    public function isNotTrash(): bool
    {
        return $this->doesNotHaveLabel('TRASH');
    }
}
