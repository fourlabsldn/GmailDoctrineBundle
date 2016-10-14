<?php

namespace FL\GmailDoctrineBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
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
     * @param EntityManagerInterface $entityManager
     * @param string $historyClass
     * @param string $syncSettingClass
     */
    public function __construct(
        SyncManager $syncManager,
        OAuth $oAuth,
        Directory $directory,
        EntityManagerInterface $entityManager,
        string $historyClass,
        string $syncSettingClass
    ) {
        $this->syncManager = $syncManager;
        $this->oAuth = $oAuth;
        $this->directory = $directory;
        $this->historyRepository = $entityManager->getRepository($historyClass);
        $this->syncSettingRepository = $entityManager->getRepository($syncSettingClass);
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
    public function syncByUserId(string $userId)
    {
        $previousHistory = $this->historyRepository->findOneByUserId($userId);
        /** @var GmailHistory|null $previousHistory */
        $this->syncManager->sync($userId, $previousHistory ? $previousHistory->getHistoryId() : null);
    }
}
