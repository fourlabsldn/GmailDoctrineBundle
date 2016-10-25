<?php

namespace FL\GmailDoctrineBundle\Model;

use FL\GmailBundle\Swift\SwiftGmailMessage;
use Html2Text\Html2Text;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     *     @Assert\Email(message="Invalid email address")
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
     * @return array
     */
    public function getToCSV()
    {
        return implode(",", $this->to);
    }

    /**
     * @param string $toCSV
     * @return OutgoingEmail
     */
    public function setToCSV(string $toCSV): OutgoingEmail
    {
        $this->to = array_map('trim', explode(',', $toCSV));

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
        $html = new Html2Text($bodyHtml);
        $this->bodyPlainText = $html->getText();

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
            ->setBody($this->getBodyPlainText(), 'text/plain')
            ->addPart($this->getBodyHtml(), 'text/html')
            ->setFrom($this->getFrom())
            ->setTo($this->getTo())
            ->setThreadId($this->getThreadId())
        ;

        return $swiftMessage;
    }
}
