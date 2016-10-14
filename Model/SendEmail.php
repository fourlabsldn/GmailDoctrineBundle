<?php

namespace FL\GmailDoctrineBundle\Model;

use FL\GmailBundle\Swift\SwiftGmailMessage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class Email
 * @package FL\GmailDoctrineBundle\Model
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
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (count(self::getMultipleEmailsFromString($this->to)) === 0) {
            $context
                ->buildViolation('This field requires at least one valid email.')
                ->atPath('to')
                ->addViolation()
            ;
        }
    }

    /**
     * @param SendEmail $sendEmail
     * @return SwiftGmailMessage
     */
    final public static function convertToSwiftGmailMessage(SendEmail $sendEmail)
    {
        $swiftMessage = SwiftGmailMessage::newInstance($sendEmail->getSubject());
        $swiftMessage->setBody($sendEmail->getBodyHtml(), 'text/html');
        $swiftMessage->addPart($sendEmail->getBodyPlainText(), 'text/plain');
        $swiftMessage->setFrom($sendEmail->getFrom());
        $swiftMessage->setTo(static::getMultipleEmailsFromString($sendEmail->getTo()));
        $swiftMessage->setThreadId($sendEmail->getThreadId());

        return $swiftMessage;
    }

    /**
     * Will convert even somewhat broken strings to an array of emails. E.g.:
     * email@example.com Miles <miles@example.com>, Mila <mila@example.com, Charles charles@example.com,,,,, <Mick> mick@example.com
     *
     * @param string $string
     * @return array
     */
    final protected static function getMultipleEmailsFromString(string $string = null)
    {
        $emails = [];
        if (is_string($string) && !empty($string)) {
            $possibleEmails = preg_split("/(,|<|>|,|\\s)/", $string);
            foreach($possibleEmails as $possibleEmail){
                if (filter_var($possibleEmail, FILTER_VALIDATE_EMAIL)) {
                    $emails[$possibleEmail] = $possibleEmail;
                }
            }
        }
        return $emails;
    }
}
