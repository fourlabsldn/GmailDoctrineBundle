<?php

namespace FL\GmailDoctrineBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailSyncMessagesEvent;
use FL\GmailBundle\Model\Collection\GmailLabelCollection;
use FL\GmailBundle\Services\OAuth;
use FL\GmailDoctrineBundle\Entity\GmailLabel;
use FL\GmailDoctrineBundle\Entity\GmailMessage;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use FL\GmailDoctrineBundle\Exception\MissingSyncSettingException;

/**
 * Class PersistMessagesListener.
 */
class PersistMessagesListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $messageRepository;

    /**
     * @var EntityRepository
     */
    private $labelRepository;

    /**
     * @var EntityRepository
     */
    private $syncSettingRepository;

    /**
     * @var string
     */
    private $domain;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $messageClass
     * @param string                 $labelClass
     * @param string                 $syncSettingClass
     * @param OAuth                  $oAuth
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $messageClass,
        string $labelClass,
        string $syncSettingClass,
        OAuth $oAuth
    ) {
        $this->entityManager = $entityManager;
        $this->messageRepository = $entityManager->getRepository($messageClass);
        $this->labelRepository = $entityManager->getRepository($labelClass);
        $this->syncSettingRepository = $entityManager->getRepository($syncSettingClass);
        $this->domain = $oAuth->resolveDomain();
    }

    /**
     * @todo - Use less db queries, to check previous persistence
     *
     * @param GmailSyncMessagesEvent $event
     */
    public function onGmailSyncMessages(GmailSyncMessagesEvent $event)
    {
        $syncSetting = $this->syncSettingRepository->findOneBy(['domain'=>$this->domain]);
        if (! ($syncSetting instanceof SyncSetting)) {
            throw new MissingSyncSettingException();
        }


        $persistedLabels = new GmailLabelCollection();
        foreach ($event->getLabelCollection()->getLabels() as $label) {
            /* @var GmailLabel $label */
            $existingLabel = $this->labelRepository->findOneBy(['name' => $label->getName(), 'userId' => $label->getUserId()]);
            if ($existingLabel instanceof GmailLabel) {
                $persistedLabels->addLabel($existingLabel);
            }
        }

        /** @var GmailMessage $message */
        foreach ($event->getMessageCollection()->getMessages() as $message) {

            /** @var GmailLabel $label */
            foreach ($message->getLabels() as $label) {
                // substitute labels already in the db
                if ($persistedLabels->hasLabelOfNameAndUserId($label->getName(), $label->getUserId())) {
                    $message->removeLabel($label);
                    $message->addLabel($persistedLabels->getLabelOfName($label->getName()));
                }
            }

            $persistedMessage = $this->messageRepository->findOneByGmailId($message->getGmailId());
            // message is in the db, refresh labels
            if ($persistedMessage instanceof GmailMessage) {
                $persistedMessage->clearLabels();
                foreach ($message->getLabels() as $label) {
                    $persistedMessage->addLabel($label);
                }
            }
            // message isn't in the db yet
            else {
                if (in_array($message->getUserId(), $syncSetting->getUserIdsCurrentlyFlagged())) {
                    $message->setFlagged(true);
                }
                $this->entityManager->persist($message);
            }
        }

        $this->entityManager->flush();
    }
}
