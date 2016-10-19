<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailBundle\Event\GmailSyncHistoryEvent;
use FL\GmailBundle\Model\GmailHistoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class PersistHistoryListener
 * @package FL\GmailDoctrineBundle\EventListener
 */
class PersistHistoryListener
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
     * @param string $historyClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $historyClass)
    {
        $this->entityManager = $entityManager;
        $this->historyRepository = $entityManager->getRepository($historyClass);
    }

    /**
     * Persist the history ID for the user anytime it's updated.
     * Updates the row if it exists, creates it otherwise.
     * @param GmailSyncHistoryEvent $event
     */
    public function onGmailSyncHistory(GmailSyncHistoryEvent $event)
    {
        $newHistory = $event->getHistory();
        $entityManager = $this->entityManager;

        $oldHistory = $this->historyRepository->findOneBy(['userId'=> $newHistory->getUserId()]);

        if (
            (!is_int($newHistory->getHistoryId())) ||
            (!is_string($newHistory->getUserId()))
        ) {
            return;
        }

        if ($oldHistory instanceof GmailHistoryInterface) { // already have a history in the db for this user
            if ($newHistory->getHistoryId() > $oldHistory->getHistoryId()) {
                $oldHistory->setHistoryId($newHistory->getHistoryId());
            }
            $entityManager->persist($oldHistory);
        } else { //no history in the db for this user, create a new one
            $entityManager->persist($newHistory);
        }
        $entityManager->flush();
    }
}
