<?php

namespace FL\GmailDoctrineBundle\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CleanUpMessagesListener
 * @package FL\GmailDoctrineBundle\EventListener
 *
 * Clean up messages, when there's changes in GmailSyncSetting entities
 */
class CleanUpMessagesListener
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
     * @var array
     *
     * We will be removing entities, but doctrine doesn't encourage doing this in the same flush.
     * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#preupdate
     * "PREUPDATE: Changes to associations of the updated entity are never allowed in this event, since Doctrine cannot guarantee to correctly handle
     * referential integrity at this point of the flush operation. This event has a powerful feature however, it is executed with a PreUpdateEventArgs
     * instance, which contains a reference to the computed change-set of this entity."
     *
     * A good way around this is to do another flush later.
     * The trade-off is that we lose the atomicity of doing things in one flush.
     * I.e. if there is a power cut between the two flushes... the second flush won't execute.
     * @link http://stackoverflow.com/questions/16904462/adding-additional-persist-calls-to-preupdate-call-in-symfony-2-1#answer-16906067
     */
    private $removeTheseEntities;

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
        $this->removeTheseEntities = [];
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
            $this->removeTheseEntities[] = $email;
        }
    }

    /**
     * @param string[] $userIdsRemoved
     * @param ObjectManager $objectManager
     */
    private function removeLabels(array $userIdsRemoved, ObjectManager $objectManager)
    {
        foreach ($objectManager->getRepository($this->labelClass)->getAllFromUserIds($userIdsRemoved) as $label) {
            $this->removeTheseEntities[] = $label;
        }
    }

    /**
     * @param string[] $userIdsRemoved
     * @param ObjectManager $objectManager
     */
    private function removeHistories(array $userIdsRemoved, ObjectManager $objectManager)
    {
        foreach ($objectManager->getRepository($this->historyClass)->getAllFromUserIds($userIdsRemoved) as $history) {
            $this->removeTheseEntities[] = $history;
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args){
        if (! empty($this->removeTheseEntities)) {
            $em = $args->getEntityManager();
            foreach ($this->removeTheseEntities as $entity) {
                $em->remove($entity);
            }
            $this->removeTheseEntities = []; // prevents doctrine exception
            $em->flush();
        }
    }
}
