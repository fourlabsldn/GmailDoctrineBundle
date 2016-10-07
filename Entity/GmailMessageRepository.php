<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
* Class GmailMessageRepository
* @package FL\GmailDoctrineBundle\Entity
*/
class GmailMessageRepository extends EntityRepository
{
    const LABEL_SPAM = "SPAM";
    const LABEL_TRASH = "TRASH";

    /**
    * Get all messages in a given label (such as inbox, sent, etc.)
    * This should only return the most recent email for each thread.
    * @param string $label
    * @return array
    */
    public function getMostRecentByLabel(string $label)
    {
        $ids = $this->getMostRecentGmailMessageIdsByLabel($label);

        return $this->createQueryBuilder('m')
            ->where('m.gmailId IN(:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('m.sentAt', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function getAllFromUserIds(array $userIds)
    {
        return $this->createQueryBuilder('m')
            ->where('m.userId IN(:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->getResult();
    }

    /**
    * Get all emails with the given label. This groups by thread ID
    * so we will only get the most recent email for each thread.
    * @param string $label
    * @return array
    */
    private function getMostRecentGmailMessageIdsByLabel(string $label): array
    {
        // TODO: clean up $label string
        $gmailIds = $this->createQueryBuilder('m')
            ->select('MAX(m.gmailId) AS gmailId, m.threadId')
            ->innerJoin('m.labels', 'l')
            ->where('l.name = :name')
            ->setParameter('name', $label)
            ->groupBy('m.threadId')
            ->getQuery()
            ->getResult();

        return array_column($gmailIds, 'gmailId');

        // TODO
        // Up to this point it works fine in returning emails in that label.
        // However, this will also return emails in Spam or Trash that are also
        // marked with the given label. (For example, an email can be in SENT and TRASH
        // and we don't want it to be displayed in the Sent inbox).

        // The following code is a skeleton of how the flow will work with the exclusion.
        // It only needs the actual exclusion inside their respective conditionals.

//        $query = $this->createQueryBuilder('m')
//            ->select('MAX(m.gmailId) AS gmailId, m.threadId')
//            ->innerJoin('m.labels', 'l')
//            ->where('l.name = :name')
//            ->setParameter('name', $label);
//
//        // Exclude all emails with the 'SPAM' label
//        if ($label != self::LABEL_SPAM) {
//            // TODO: Exclude emails in GmailIds that also have the 'SPAM' label
//        }
//
//        // Exclude all emails with the 'TRASH' label
//        if ($label != self::LABEL_TRASH) {
//            // TODO: Exclude emails in GmailIds that also have the 'TRASH' label
//        }
//
//        $gmailIds = $query->groupBy('m.threadId')
//            ->getQuery()
//            ->getResult();
//
//        return array_column($gmailIds, 'gmailId');
    }
}
