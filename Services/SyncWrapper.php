<?php

namespace FL\GmailDoctrineBundle\Services;

use FL\GmailBundle\Model\GmailIds;
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
 * @see \FL\GmailBundle\Services\SyncGmailIds
 * @see \FL\GmailBundle\Services\SyncMessages
 */
class SyncWrapper
{
    const MODE_SYNC_GMAIL_IDS = 0;
    const MODE_SYNC_GMAIL_MESSAGES = 1;
    const MODE_SYNC_ALL = 2;
    const MODES = [
        self::MODE_SYNC_GMAIL_IDS,
        self::MODE_SYNC_GMAIL_MESSAGES,
        self::MODE_SYNC_ALL,
    ];


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
     * @param SyncGmailIds           $syncGmailIds
     * @param SyncMessages           $syncMessages
     * @param OAuth                  $oAuth
     * @param Directory              $directory
     * @param EntityManagerInterface $entityManager
     * @param string                 $historyClass
     * @param string                 $syncSettingClass
     * @param string                 $gmailIdsClass
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
     * Syncs gmailIds and messages for all users (if configured at @see SyncSetting::$userIds).
     *
     * @param int $syncLimitPerUser
     * @param int $mode
     */
    public function sync(int $syncLimitPerUser, int $mode)
    {
        foreach ($this->directory->resolveUserIds() as $userId) {
            $this->syncByUserId($userId, $syncLimitPerUser, $mode);
        }
    }

    /**
     * Syncs gmailIds and messages for a user, by email (if configured at @see SyncSetting::$userIds).
     *
     * @param string $email
     * @param int    $syncLimit
     * @param int    $mode
     */
    public function syncEmail(string $email, int $syncLimit, int $mode)
    {
        $userId = $this->directory->resolveUserIdFromEmail($email, Directory::MODE_RESOLVE_PRIMARY_PLUS_ALIASES);
        $this->syncByUserId($userId, $syncLimit, $mode);
    }

    /**
     * Syncs gmailIds and messages for a user, by userId (if configured by @see SyncSetting::$userIds).
     *
     * @param string $userId
     * @param int    $syncLimit
     * @param int    $mode
     */
    public function syncByUserId(string $userId, int $syncLimit, int $mode)
    {
        if (!in_array($mode, self::MODES)) {
            throw new \InvalidArgumentException();
        }

        $domain = $this->oAuth->resolveDomain();
        $syncSetting = $this->syncSettingRepository->findOneByDomain($domain);

        if (!($syncSetting instanceof SyncSetting)) {
            return;
        }

        if (in_array($userId, $syncSetting->getUserIds())) {
            return;
        }

        switch ($mode) {
            case self::MODE_SYNC_GMAIL_IDS:
                $this->syncGmailIdsByUserId($userId);
                break;
            case self::MODE_SYNC_GMAIL_MESSAGES:
                $this->syncMessagesByUserId($userId, $syncLimit);
                break;
            case self::MODE_SYNC_ALL:
                $this->syncGmailIdsByUserId($userId);
                $this->syncMessagesByUserId($userId, $syncLimit);
                break;
        }
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
     * @param int    $syncLimit
     */
    private function syncMessagesByUserId(string $userId, int $syncLimit)
    {
        $persistedGmailIds = $this->gmailIdsRepository->findOneByUserId($userId);
        if ($persistedGmailIds instanceof  GmailIdsInterface) {
            /*
             * Note: we are depending on getGmailIds having the latest $idsToSyncRightNow at the start
             * such that we are syncing the latest messages first.
             * This is important, such that when we call syncs after sending emails, or making updates
             * we update the latest thing that happened.
             */
            $gmailIdsObject = (new GmailIds())
                ->setDomain($persistedGmailIds->getDomain())
                ->setGmailIds($persistedGmailIds->getGmailIds($syncLimit))
                ->setUserId($persistedGmailIds->getUserId());
            $this->syncMessages->syncFromGmailIds($gmailIdsObject);
        }
    }
}
