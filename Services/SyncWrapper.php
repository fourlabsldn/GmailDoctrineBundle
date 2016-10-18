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
     * @param int $messagesToSync
     */
    public function sync(int $messagesToSync)
    {
        $domain = $this->oAuth->resolveDomain();
        $syncSetting = $this->syncSettingRepository->findOneByDomain($domain);

        if ($syncSetting instanceof SyncSetting) {
            foreach ($syncSetting->getUserIds() as $userId) {
                $this->syncByUserId($userId, $messagesToSync);
            }
        }
    }

    /**
     * @param string $email
     * @param $messagesToSync
     */
    public function syncEmail(string $email, int $messagesToSync)
    {
        $domain = $this->oAuth->resolveDomain();
        $userId = $this->directory->resolveUserIdFromEmail($email, $domain, Directory::MODE_RESOLVE_PRIMARY_PLUS_ALIASES);
        $this->syncByUserId($userId, $messagesToSync);
    }

    /**
     * @param string $userId
     * @param int $messagesToSync
     */
    public function syncByUserId(string $userId, int $messagesToSync)
    {
        $this->syncGmailIdsByUserId($userId);
        $this->syncMessagesByUserId($userId, $messagesToSync);
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
     * @param int $messagesToSync
     */
    private function syncMessagesByUserId(string $userId, int $messagesToSync)
    {
        $this->syncGmailIdsByUserId($userId);
        $persistedGmailIds = $this->gmailIdsRepository->findOneByUserId($userId);
        if ($persistedGmailIds instanceof  GmailIdsInterface) {
            $allIdsToSync = $persistedGmailIds->getGmailIds();
            // note, we are depending on getGmailIds having the latest $idsToSyncRightNow at the start
            $idsToSyncRightNow = array_slice($allIdsToSync, 0, $messagesToSync);

            $persistedGmailIds->setGmailIds($idsToSyncRightNow);
            $this->syncMessages->syncFromGmailIds($persistedGmailIds);

            // be careful with the ordering in array_diff
            $persistedGmailIds->setGmailIds(array_diff($allIdsToSync, $idsToSyncRightNow));
            $this->entityManager->persist($persistedGmailIds);
        }

        $this->entityManager->flush();
    }
}
