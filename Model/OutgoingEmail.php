<?php

namespace FL\GmailDoctrineBundle\Model;

use FL\GmailBundle\Swift\SwiftGmailMessage;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OutgoingEmail
 * @package FL\GmailDoctrineBundle\Model
 */
class OutgoingEmail
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $from;

    /**
     * @var array
     * @Assert\Count(min=1, minMessage="This field requires at least one email address")
     * @Assert\All(
     *     @Assert\Email
     * )
     */
    private $to;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $subject;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $bodyHtml;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $bodyPlainText;

    /**
     * @var string|null
     */
    private $threadId = null;

    /**
     * @return string|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return OutgoingEmail
     */
    public function setFrom(string $from): OutgoingEmail
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param array $to
     * @return $this
     */
    public function setTo(array $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param string $to CSV
     * @return OutgoingEmail
     */
    public function setToCSV(string $to): OutgoingEmail
    {
        $this->to = array_map('trim', explode(',', $to));

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return OutgoingEmail
     */
    public function setSubject(string $subject): OutgoingEmail
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBodyHtml()
    {
        return $this->bodyHtml;
    }

    /**
     * @param string $bodyHtml
     * @return OutgoingEmail
     */
    public function setBodyHtml(string $bodyHtml): OutgoingEmail
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBodyPlainText()
    {
        return $this->bodyPlainText;
    }

    /**
     * @param string $bodyPlainText
     * @return OutgoingEmail
     */
    public function setBodyPlainText(string $bodyPlainText): OutgoingEmail
    {
        $this->bodyPlainText = $bodyPlainText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * @param string|null $threadId
     * @return OutgoingEmail
     */
    public function setThreadId($threadId): OutgoingEmail
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * @return SwiftGmailMessage
     */
    final public function getAsSwiftGmailMessage()
    {
        $swiftMessage = SwiftGmailMessage::newInstance($this->getSubject());
        $swiftMessage
            ->setBody($this->getBodyHtml(), 'text/html')
            ->addPart($this->getBodyPlainText(), 'text/plain')
            ->setFrom($this->getFrom())
            ->setTo($this->getTo())
            ->setThreadId($this->getThreadId())
        ;

        return $swiftMessage;
    }
}
