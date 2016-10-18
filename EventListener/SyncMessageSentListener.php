<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailDoctrineBundle\Services\SyncWrapper;

/**
 * Class SyncMessagesSentListener
 * @package FL\GmailDoctrineBundle\EventListener
 * @link http://stackoverflow.com/questions/18033210/logging-swiftmailer-send-activity-in-symfony2
 */
class SyncMessagesSentListener implements \Swift_Events_SendListener
{

    /**
     * @var SyncWrapper
     */
    private $syncWrapper;

    /**
     * GmailMessageSentListener constructor.
     * @param SyncWrapper $syncWrapper
     */
    public function __construct(SyncWrapper $syncWrapper)
    {
        $this->syncWrapper = $syncWrapper;
    }

    /**
     * @inheritdoc
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    /**
     * @inheritdoc
     * A sync must be triggered, because the Gmail API does not return a response with
     * enough parameters to construct a new GmailMessage entity.
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        $fromString = $evt->fromEmailAddress;
        if (!empty($fromString)) {
            $this->syncWrapper->syncEmail($fromString, 5);
        }
    }
}
