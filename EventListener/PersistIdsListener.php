<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailBundle\Model\GmailIdsInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailSyncIdsEvent;

/**
 * Class PersistIdsListener.
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
     * @param string                 $gmailIdsClass
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
        $persistedGmailIds = $this->gmailIdsRepository->findOneByUserId($event->getGmailIdsObject()->getUserId());

        if ($persistedGmailIds instanceof GmailIdsInterface) {
            $persistedGmailIds->setGmailIds(
                // newest ids at the beginning of the array
                array_merge($event->getGmailIdsObject()->getGmailIds(), $persistedGmailIds->getGmailIds())
            );
            $this->entityManager->persist($persistedGmailIds);
        } else {
            $this->entityManager->persist($event->getGmailIdsObject());
        }

        $this->entityManager->flush();
    }
}
