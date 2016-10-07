<?php

namespace FL\GmailDoctrineBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Email
 * @package FL\GmailDoctrineBundle\Model\Email
 */
class SendEmail
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $from;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
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
     * @return string|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return SendEmail
     */
    public function setTo(string $to): SendEmail
    {
        $this->to = $to;

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
     * @param SendEmail $sendEmail
     * @return \Swift_Message
     */
    final public static function convertToSwiftMessage(SendEmail $sendEmail)
    {
        $swiftMessage = \Swift_Message::newInstance($sendEmail->getSubject());
        $swiftMessage->setBody($sendEmail->getBodyHtml(), 'text/html');
        $swiftMessage->addPart($sendEmail->getBodyPlainText(), 'text/plain');
        $swiftMessage->setFrom($sendEmail->getFrom());
        $swiftMessage->setTo($sendEmail->getTo());

        return $swiftMessage;
    }
}
