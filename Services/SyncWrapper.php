<?php

namespace FL\GmailDoctrineBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use FL\GmailBundle\Model\GmailIdsInterface;
use FL\GmailBundle\Services\SyncGmailIds;
use FL\GmailBundle\Services\SyncHelper;
use FL\GmailBundle\Services\SyncMessages;
use FL\GmailDoctrineBundle\Entity\GmailHistory;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Services\Directory;
use FL\GmailBundle\Services\OAuth;
use FL\GmailBundle\Services\SyncManager;

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
        $this->historyRepository = $entityManager->getRepository($historyClass);
        $this->syncSettingRepository = $entityManager->getRepository($syncSettingClass);
        $this->gmailIdsRepository = $entityManager->getRepository($gmailIdsClass);
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
        $this->syncGmailIdsByUserId($userId);
        $this->syncMessagesByUserId($userId);
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
     */
    private function syncMessagesByUserId(string $userId)
    {
        $this->syncGmailIdsByUserId($userId);
        $previousGmailIds = $this->gmailIdsRepository->findOneByUserId($userId);
        if ($previousGmailIds instanceof  GmailIdsInterface) {
            // @todo only sync some, and leave the rest for later
            $this->syncMessages->syncFromGmailIds($previousGmailIds->getGmailIds());
        }
    }


}
