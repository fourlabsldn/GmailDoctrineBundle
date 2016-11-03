<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailBundle\Model\GmailLabelInterface;
use FL\GmailBundle\Model\GmailMessageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailSyncMessagesEvent;
use FL\GmailBundle\Model\Collection\GmailLabelCollection;

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
     * @param EntityManagerInterface $entityManager
     * @param string                 $messageClass
     * @param string                 $labelClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $messageClass, string $labelClass)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $entityManager->getRepository($messageClass);
        $this->labelRepository = $entityManager->getRepository($labelClass);
    }

    /**
     * @todo - Use less db queries, to check previous persistence
     *
     * @param GmailSyncMessagesEvent $event
     */
    public function onGmailSyncMessages(GmailSyncMessagesEvent $event)
    {
        $persistedLabels = new GmailLabelCollection();
        foreach ($event->getLabelCollection()->getLabels() as $label) {
            /* @var GmailLabelInterface $label */
            $existingLabel = $this->labelRepository->findOneBy(['name' => $label->getName(), 'userId' => $label->getUserId()]);
            if ($existingLabel instanceof GmailLabelInterface) {
                $persistedLabels->addLabel($existingLabel);
            }
        }

        /** @var GmailMessageInterface $message */
        foreach ($event->getMessageCollection()->getMessages() as $message) {

            /** @var GmailLabelInterface $label */
            foreach ($message->getLabels() as $label) {
                // substitute labels already in the db
                if ($persistedLabels->hasLabelOfNameAndUserId($label->getName(), $label->getUserId())) {
                    $message->removeLabel($label);
                    $message->addLabel($persistedLabels->getLabelOfName($label->getName()));
                }
            }

            $persistedMessage = $this->messageRepository->findOneByGmailId($message->getGmailId());
            // message is in the db, refresh labels
            if ($persistedMessage instanceof GmailMessageInterface) {
                $persistedMessage->clearLabels();
                foreach ($message->getLabels() as $label) {
                    $persistedMessage->addLabel($label);
                }
            }
            // message isn't in the db yet
            else {
                $this->entityManager->persist($message);
            }
        }

        $this->entityManager->flush();
    }
}
