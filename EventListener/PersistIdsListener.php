<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailBundle\Model\GmailIdsInterface;
use FL\GmailDoctrineBundle\Entity\GmailMessage;
use FL\GmailDoctrineBundle\Entity\GmailLabel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailSyncIdsEvent;
use FL\GmailBundle\Model\Collection\GmailLabelCollection;

/**
 * Class PersistIdsListener
 * @package FL\GmailDoctrineBundle\EventListener
 */
class PersistIdsListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $gmailIdsRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $gmailIdsClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $gmailIdsClass)
    {
        $this->entityManager = $entityManager;
        $this->gmailIdsRepository = $entityManager->getRepository($gmailIdsClass);
    }

    /**
     * @param GmailSyncIdsEvent $event
     */
    public function onGmailSyncIds(GmailSyncIdsEvent $event)
    {
        $persistedGmailIds = $this->gmailIdsRepository->findBy($event->getGmailIdsObject()->getUserId());

        if ($persistedGmailIds instanceof GmailIdsInterface) {
            $persistedGmailIds->setGmailIds(
                array_merge($persistedGmailIds->getGmailIds(), $event->getGmailIdsObject()->getGmailIds())
            );
            $this->entityManager->persist($persistedGmailIds);

        } else {
            $this->entityManager->persist($event->getGmailIdsObject());
        }

        $this->entityManager->flush();
    }
}
