<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailDoctrineBundle\Entity\GmailMessage;
use FL\GmailDoctrineBundle\Entity\GmailLabel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailSyncEndEvent;
use FL\GmailBundle\Model\Collection\GmailLabelCollection;

/**
 * Class GmailHistoryUpdatedListener
 * @package FL\GmailDoctrineBundle\EventListener
 */
class GmailSyncEndListener
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
     * @param string $messageClass
     * @param string $labelClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $messageClass, string $labelClass)
    {
        $this->entityManager = $entityManager;
        $this->messageRepository = $entityManager->getRepository($messageClass);
        $this->labelRepository = $entityManager->getRepository($labelClass);
    }

    /**
     * @todo - Use less db queries, to check previous persistence
     * @param GmailSyncEndEvent $event
     */
    public function onSyncEnd(GmailSyncEndEvent $event)
    {
        $persistedLabels = new GmailLabelCollection();
        foreach ($event->getLabelCollection()->getLabels() as $label) {
            /** @var GmailLabel $label */
            $existingLabel = $this->labelRepository->findOneBy(['name'=>$label->getName(), 'userId' => $label->getUserId()]);
            if ($existingLabel instanceof GmailLabel) {
                $persistedLabels->addLabel($existingLabel);
            }
        }

        /** @var GmailMessage $message */
        foreach ($event->getMessageCollection()->getMessages() as $message) {

            /** @var GmailLabel $label */
            foreach ($message->getLabels() as $label) {
                if ($persistedLabels->hasLabelOfNameAndUserId($label->getName(), $label->getUserId())) {
                    $message->removeLabel($label);
                    $message->addLabel($persistedLabels->getLabelOfName($label->getName()));
                }
            }

            if (! ($this->messageRepository->findOneByGmailId($message->getGmailId()))) { //message isn't persisted already
                $this->entityManager->persist($message);
            }
        }

        $this->entityManager->flush();
    }
}
