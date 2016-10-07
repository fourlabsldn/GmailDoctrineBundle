<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailDoctrineBundle\Entity\GmailHistory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Event\GmailHistoryUpdatedEvent;

/**
 * Class GmailHistoryUpdatedListener
 * @package FL\GmailDoctrineBundle\EventListener
 */
class GmailHistoryUpdatedListener
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $historyRepository;

    /**
     * GmailHistoryUpdatedListener constructor.
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $historyRepository
     */
    public function __construct(EntityManagerInterface $entityManager, EntityRepository $historyRepository)
    {
        $this->entityManager = $entityManager;
        $this->historyRepository = $historyRepository;
    }

    /**
     * Persist the history ID for the user anytime it's updated.
     * Updates the row if it exists, creates it otherwise.
     * @param GmailHistoryUpdatedEvent $event
     */
    public function onGmailHistoryUpdated(GmailHistoryUpdatedEvent $event)
    {
        $newHistory = $event->getHistory();
        $entityManager = $this->entityManager;

        if ($newHistory->getHistoryId() && $newHistory->getUserId()) {
            $oldHistory = $this->historyRepository->findOneBy(['userId'=> $newHistory->getUserId()]);

            if ($oldHistory instanceof GmailHistory) { // already have a history in the db for this user, replace it
                $oldHistory->setHistoryId($newHistory->getHistoryId());
                $entityManager->persist($oldHistory);
            } else { //no history in the db for this user, create a new one
                $entityManager->persist($newHistory);
            }
            $entityManager->flush();
        }
    }
}
