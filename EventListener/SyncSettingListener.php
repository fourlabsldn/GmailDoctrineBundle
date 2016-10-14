<?php

namespace FL\GmailDoctrineBundle\EventListener;

use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class SyncSettingListener
 * @package FL\GmailDoctrineBundle\EventListener
 */
class SyncSettingListener
{

    /**
     * @var string
     */
    private $messageClass;

    /**
     * @var string
     */
    private $labelClass;

    /**
     * @var string
     */
    private $historyClass;

    /**
     * SyncSettingListener constructor.
     * @param string $messageClass
     * @param string $labelClass
     * @param string $historyClass
     */
    public function __construct(string $messageClass, string $labelClass, string $historyClass)
    {
        $this->messageClass = $messageClass;
        $this->labelClass = $labelClass;
        $this->historyClass = $historyClass;
    }


    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if (! ($entity instanceof SyncSetting)) {
            return;
        }

        if ($args->hasChangedField('userIds')) {
            $oldUserIds = $args->getOldValue('userIds');
            $newUserIds = $args->getNewValue('userIds');

            // be careful with the ordering in array_diff
            $userIdsRemoved = array_diff($oldUserIds, $newUserIds);
            $objectManager = $args->getObjectManager();

            $this->removeMessages($userIdsRemoved, $objectManager);
            $this->removeLabels($userIdsRemoved, $objectManager);
            $this->removeHistories($userIdsRemoved, $objectManager);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (! ($entity instanceof SyncSetting)) {
            return;
        }

        $userIdsRemoved = $entity->getUserIds();
        $objectManager = $args->getObjectManager();
        $this->removeMessages($userIdsRemoved, $objectManager);
        $this->removeLabels($userIdsRemoved, $objectManager);
        $this->removeHistories($userIdsRemoved, $objectManager);
    }


    /**
     * @param string[] $userIdsRemoved
     * @param ObjectManager $objectManager
     */
    private function removeMessages(array $userIdsRemoved, ObjectManager $objectManager)
    {
        foreach ($objectManager->getRepository($this->messageClass)->getAllFromUserIds($userIdsRemoved) as $email) {
            $objectManager->remove($email);
        }
    }

    /**
     * @param string[] $userIdsRemoved
     * @param ObjectManager $objectManager
     */
    private function removeLabels(array $userIdsRemoved, ObjectManager $objectManager)
    {
        foreach ($objectManager->getRepository($this->labelClass)->getAllFromUserIds($userIdsRemoved) as $label) {
            $objectManager->remove($label);
        }
    }

    /**
     * @param string[] $userIdsRemoved
     * @param ObjectManager $objectManager
     */
    private function removeHistories(array $userIdsRemoved, ObjectManager $objectManager)
    {
        foreach ($objectManager->getRepository($this->historyClass)->getAllFromUserIds($userIdsRemoved) as $label) {
            $objectManager->remove($label);
        }
    }
}
