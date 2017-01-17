<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class CleanUpMessagesListener.
 */
class CorrectSyncSettingIdsListener
{
    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if (!($entity instanceof SyncSetting)) {
            return;
        }

        $this->correctSyncSetting($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!($entity instanceof SyncSetting)) {
            return;
        }

        $this->correctSyncSetting($entity);
    }

    /**
     * Every id in @see SyncSetting::$userIdsCurrentlyFlagged
     * must be in @see SyncSetting::$userIds.
     *
     * @param SyncSetting $syncSetting
     */
    public function correctSyncSetting(SyncSetting $syncSetting)
    {
        $syncSetting->setUserIdsCurrentlyFlagged(array_intersect($syncSetting->getUserIdsCurrentlyFlagged(), $syncSetting->getUserIds()));
    }
}
