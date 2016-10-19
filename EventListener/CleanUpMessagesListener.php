<?php

namespace FL\GmailDoctrineBundle\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use FL\GmailDoctrineBundle\Entity\GmailHistoryRepository;
use FL\GmailDoctrineBundle\Entity\GmailIdsRepository;
use FL\GmailDoctrineBundle\Entity\GmailLabelRepository;
use FL\GmailDoctrineBundle\Entity\GmailMessageRepository;
use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class CleanUpMessagesListener
 * @package FL\GmailDoctrineBundle\EventListener
 *
 * When email accounts are removed from the list of emails to be synced in SyncSettings,
 * remove all associated messages, labels, histories and gmailIds
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
     * @var string
     */
    private $gmailIdsClass;

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
    private $removeTheseUserIds = [];

    /**
     * SyncSettingListener constructor.
     * @param string $messageClass
     * @param string $labelClass
     * @param string $historyClass
     * @param string $gmailIdsClass
     */
    public function __construct(string $messageClass, string $labelClass, string $historyClass, string $gmailIdsClass)
    {
        $this->messageClass = $messageClass;
        $this->labelClass = $labelClass;
        $this->historyClass = $historyClass;
        $this->gmailIdsClass = $gmailIdsClass;
    }


    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if (
            !($args->getObject() instanceof SyncSetting)
            || !$args->hasChangedField('userIds')
        ) {
            return;
        }

        /** @var string[] $oldUserIds */
        $oldUserIds = $args->getOldValue('userIds');
        /** @var string[] $newUserIds */
        $newUserIds = $args->getNewValue('userIds');

        foreach($oldUserIds as $oldUserId) {
            if (!in_array($oldUserId, $newUserIds)) {
                $this->removeTheseUserIds[] = $oldUserId;
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!($entity instanceof SyncSetting)) {
            return;
        }

        $this->removeTheseUserIds = $entity->getUserIds();
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (count($this->removeTheseUserIds) === 0) {
            return;
        }

        $em = $args->getEntityManager();

        /** @var GmailMessageRepository $messageRepository */
        $messageRepository = $em->getRepository($this->messageClass);
        foreach ($messageRepository->getAllFromUserIds($this->removeTheseUserIds) as $message) {
            $em->remove($message);
        }

        /** @var GmailLabelRepository $labelRepository */
        $labelRepository = $em->getRepository($this->labelClass);
        foreach ($labelRepository->getAllFromUserIds($this->removeTheseUserIds) as $label) {
            $em->remove($label);
        }

        /** @var GmailHistoryRepository $historyRepository */
        $historyRepository = $em->getRepository($this->historyClass);
        foreach ($historyRepository->getAllFromUserIds($this->removeTheseUserIds) as $history) {
            $em->remove($history);
        }

        /** @var GmailIdsRepository $idsRepository */
        $idsRepository = $em->getRepository($this->gmailIdsClass);
        foreach ($idsRepository->getAllFromUserIds($this->removeTheseUserIds) as $ids) {
            $em->remove($ids);
        }

        $em->flush();
    }
}
