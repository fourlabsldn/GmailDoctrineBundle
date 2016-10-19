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
     * @return SendEmail
     */
    public function setFrom(string $from): SendEmail
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
     * @return SendEmail
     */
    public function setToCSV(string $to): SendEmail
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
     * @return SendEmail
     */
    public function setSubject(string $subject): SendEmail
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
     * @return SendEmail
     */
    public function setBodyHtml(string $bodyHtml): SendEmail
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
     * @return SendEmail
     */
    public function setBodyPlainText(string $bodyPlainText): SendEmail
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
     * @return SendEmail
     */
    public function setThreadId($threadId): SendEmail
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
