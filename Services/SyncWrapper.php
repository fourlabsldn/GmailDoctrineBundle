<?php

namespace FL\GmailDoctrineBundle\Services;

use FL\GmailDoctrineBundle\Entity\GmailHistory;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Services\Directory;
use FL\GmailBundle\Services\OAuth;
use FL\GmailBundle\Services\SyncManager;

/**
 * Class SyncWrapper
 * This class provides a wrapper to interact with @see \FL\GmailBundle\Services\SyncManager
 * @package FL\GmailDoctrineBundle\Services
 */
class SyncWrapper
{
    /**
     * @var SyncManager
     */
    private $syncManager;

    /**
     * @var OAuth
     */
    private $oAuth;

    /**
     * @var Directory
     */
    private $directory;

    /**
     * @var EntityRepository
     */
    private $historyRepository;

    /**
     * @var EntityRepository
     */
    private $syncSettingRepository;

    /**
     * Oauth constructor.
     * @param SyncManager $syncManager
     * @param OAuth $oAuth
     * @param Directory $directory
     * @param EntityRepository $historyRepository
     * @param EntityRepository $syncSettingRepository
     */
    public function __construct(SyncManager $syncManager, OAuth $oAuth, Directory $directory, EntityRepository $historyRepository, EntityRepository $syncSettingRepository)
    {
        $this->syncManager = $syncManager;
        $this->oAuth = $oAuth;
        $this->directory = $directory;
        $this->historyRepository = $historyRepository;
        $this->syncSettingRepository = $syncSettingRepository;
    }

    /**
     * @return void
     */
    public function sync()
    {
        $domain = $this->oAuth->resolveDomain();
        $syncSetting = $this->syncSettingRepository->findOneByDomain($domain);

        if ($syncSetting instanceof SyncSetting) {
            foreach ($syncSetting->getUserIds() as $userId) {
                $this->syncByUserId($userId);
            }
        }
    }

    /**
     * @param string $email
     */
    public function syncEmail(string $email)
    {
        $domain = $this->oAuth->resolveDomain();
        $userId = $this->directory->resolveUserIdFromEmail($email, $domain, Directory::MODE_RESOLVE_PRIMARY_PLUS_ALIASES);
        $this->syncByUserId($userId);
    }

    /**
     * @param string $userId
     */
    private function syncByUserId(string $userId)
    {
        $previousHistory = $this->historyRepository->findOneByUserId($userId);
        /** @var GmailHistory|null $previousHistory */
        $this->syncManager->sync($userId, $previousHistory ? $previousHistory->getHistoryId() : null);
    }
}
