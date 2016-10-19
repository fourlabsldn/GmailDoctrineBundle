<?php

namespace FL\GmailDoctrineBundle\Services;

use FL\GmailBundle\Model\GmailIdsInterface;
use FL\GmailBundle\Services\SyncGmailIds;
use FL\GmailBundle\Services\SyncMessages;
use FL\GmailBundle\Services\Directory;
use FL\GmailBundle\Services\OAuth;
use FL\GmailDoctrineBundle\Entity\GmailHistory;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class SyncWrapper
 * @package FL\GmailDoctrineBundle\Services
 *
 * This class provides a wrapper to interact with
 * @see \FL\GmailBundle\Services\SyncGmailIds
 * @see \FL\GmailBundle\Services\SyncMessages
 */
class SyncWrapper
{
    /**
     * @var SyncGmailIds
     */
    private $syncGmailIds;

    /**
     * @var SyncMessages
     */
    private $syncMessages;

    /**
     * @var OAuth
     */
    private $oAuth;

    /**
     * @var Directory
     */
    private $directory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $historyRepository;

    /**
     * @var EntityRepository
     */
    private $syncSettingRepository;

    /**
     * @var EntityRepository
     */
    private $gmailIdsRepository;

    /**
     * Oauth constructor.
     * @param SyncGmailIds $syncGmailIds
     * @param SyncMessages $syncMessages
     * @param OAuth $oAuth
     * @param Directory $directory
     * @param EntityManagerInterface $entityManager
     * @param string $historyClass
     * @param string $syncSettingClass
     * @param string $gmailIdsClass
     */
    public function __construct(
        SyncGmailIds $syncGmailIds,
        SyncMessages $syncMessages,
        OAuth $oAuth,
        Directory $directory,
        EntityManagerInterface $entityManager,
        string $historyClass,
        string $syncSettingClass,
        string $gmailIdsClass
    ) {
        $this->syncGmailIds = $syncGmailIds;
        $this->syncMessages = $syncMessages;
        $this->oAuth = $oAuth;
        $this->directory = $directory;
        $this->entityManager = $entityManager;
        $this->historyRepository = $entityManager->getRepository($historyClass);
        $this->syncSettingRepository = $entityManager->getRepository($syncSettingClass);
        $this->gmailIdsRepository = $entityManager->getRepository($gmailIdsClass);
    }

    /**
     * @param int $syncLimitPerUser
     */
    public function sync(int $syncLimitPerUser)
    {
        $domain = $this->oAuth->resolveDomain();
        $syncSetting = $this->syncSettingRepository->findOneByDomain($domain);

        if (!($syncSetting instanceof SyncSetting)) {
            return;
        }
        foreach ($syncSetting->getUserIds() as $userId) {
            $this->syncByUserId($userId, $syncLimitPerUser);
        }
    }

    /**
     * @param string $email
     * @param $syncLimit
     */
    public function syncEmail(string $email, int $syncLimit)
    {
        $domain = $this->oAuth->resolveDomain();
        $userId = $this->directory->resolveUserIdFromEmail($email, $domain, Directory::MODE_RESOLVE_PRIMARY_PLUS_ALIASES);
        $this->syncByUserId($userId, $syncLimit);
    }

    /**
     * @param string $userId
     * @param int $syncLimit
     */
    public function syncByUserId(string $userId, int $syncLimit)
    {
        $this->syncGmailIdsByUserId($userId);
        $this->syncMessagesByUserId($userId, $syncLimit);
    }

    /**
     * @param string $userId
     */
    private function syncGmailIdsByUserId(string $userId)
    {
        $previousHistory = $this->historyRepository->findOneByUserId($userId);
        if ($previousHistory instanceof  GmailHistory) {
            $this->syncGmailIds->syncFromHistoryId($userId, $previousHistory->getHistoryId());
        } else {
            $this->syncGmailIds->syncAll($userId);
        }
    }

    /**
     * @param string $userId
     * @param int $syncLimit
     */
    private function syncMessagesByUserId(string $userId, int $syncLimit)
    {
        $persistedGmailIds = $this->gmailIdsRepository->findOneByUserId($userId);
        if ($persistedGmailIds instanceof  GmailIdsInterface) {
            $allIdsToSync = $persistedGmailIds->getGmailIds();
            $idsToSyncRightNow = $persistedGmailIds->getGmailIds($syncLimit);

            /**
             * Note: we are depending on getGmailIds having the latest $idsToSyncRightNow at the start
             * such that we are syncing the latest messages first.
             * This is important, such that when we call syncs after sending emails, or making updates
             * we update the latest thing that happened.
             */
            $persistedGmailIds->setGmailIds($idsToSyncRightNow);
            $this->syncMessages->syncFromGmailIds($persistedGmailIds);

            // be careful with the ordering in array_diff
            $idsToSyncLater = array_diff($allIdsToSync, $idsToSyncRightNow);
            $persistedGmailIds->setGmailIds(is_array($idsToSyncLater) ? $idsToSyncLater : []);
            $this->entityManager->persist($persistedGmailIds);
        }

        $this->entityManager->flush();
    }
}
