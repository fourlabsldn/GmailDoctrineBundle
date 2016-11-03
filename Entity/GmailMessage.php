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
 *
 * @ORM\MappedSuperclass
 */
class GmailMessage extends BaseGmailMessage implements GmailMessageInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @var string
     */
    protected $gmailId;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $threadId;

    /**
     * Not being used for anything at the moment.
     * Nevertheless, since each message has a unique historyId for its corresponding userId,
     * this historyId can be useful in the event that the latest historyId is not available elsewhere.
     * In this case, the latest historyId is simply the historyId with the largest value.
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $historyId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $userId;

    /**
     * The default column name `to` will cause an SQL syntax error.
     *
     * @ORM\Column(name="to_", type="string", nullable=false)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $to;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $toCanonical;

    /**
     * The default column name `from` will cause an SQL syntax error.
     *
     * @ORM\Column(name="from_", type="string", nullable=false)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $from;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotNull
     *
     * @var string
     */
    protected $fromCanonical;

    /**
     * @ORM\Column(type="datetimetz", nullable=false)
     * @Assert\NotNull
     *
     * @var \DateTimeInterface
     */
    protected $sentAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $subject;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="GmailLabel", cascade={"persist", "detach"})
     */
    protected $labels;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $snippet;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $bodyPlainText;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $bodyHtml;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $domain = '';

    /**
     * GmailMessage constructor.
     */
    public function __construct()
    {
        $this->labels = new ArrayCollection();
    }

    /**
     * Set Gmail Message ID.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get Gmail Message ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(GmailLabelInterface $label): GmailMessageInterface
    {
        $this->labels->add($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
     */
    public function removeLabel(GmailLabelInterface $label): GmailMessageInterface
    {
        $this->labels->removeElement($label);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLabels(): GmailMessageInterface
    {
        $this->labels = new ArrayCollection();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelByName(string $name)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', $name));

        $labels = $this->labels->matching($criteria);

        if (
            ($labels->count() > 0) &&
            ($labels->first() instanceof GmailLabelInterface)
        ) {
            return $labels->first();
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function hasLabel(string $name): bool
    {
        if ($this->getLabelByName($name) instanceof GmailLabelInterface) {
            return true;
        }

        return false;
    }
}
