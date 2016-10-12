<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Model\GmailMessageInterface;

/**
* Class GmailMessageRepository
* @package FL\GmailDoctrineBundle\Entity
*/
class GmailMessageRepository extends EntityRepository
{
    const LABEL_SPAM = "SPAM";
    const LABEL_TRASH = "TRASH";

    /**
     * @param string $dateSort (ASC/DESC)
     *
     * ThreadIds may or may not collide across userIds, play it safe!
     * @see http://stackoverflow.com/questions/25198394/are-gmail-thread-ids-unique-across-users
     *
     * @return array
     */
    public function uniqueByThreadAndSortedByDate(string $dateSort = 'DESC') {
        $queryThreadIds = $this->getEntityManager()
            ->createQuery(
                'SELECT message.threadId, message.userId, max(message.sentAt) AS latestSentAt
                  FROM TriprHqBundle:MessagingGmailMessage message
                  GROUP BY message.threadId, message.userId'
            )
        ;
        $partials = $queryThreadIds->getResult();
        $sortByLatestSentAt = function ($partialA, $partialB) use ($dateSort) {
            $dateA = new \DateTimeImmutable($partialA['latestSentAt']);
            $dateB = new \DateTimeImmutable($partialB['latestSentAt']);

            switch ($dateSort) {
                case 'ASC':
                    return ($dateA <=> $dateB);
                case 'DESC':
                    return (-1*($dateA <=> $dateB));
                default:
                    throw new \InvalidArgumentException('Invalid dateSort, must be ASC or DESC');
            }
        };

        /**
         * If sort performance is a problem, the first places to look:
         * @see http://stackoverflow.com/questions/3165984/what-sort-algorithm-does-php-use
         * @see https://www.quora.com/Why-is-quicksort-considered-to-be-better-than-merge-sort
         * @see http://bigocheatsheet.com/
         */
        usort($partials, $sortByLatestSentAt);

        /**
         * Sort is unlikely to be the performance bottleneck.
         * If there are a lot of threads, look to optimize the following lines
         * E.g. index the columns={"thread_id", "user_id", "sent_at"} in your GmailMessage entity
         * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#annref-index
         *
         * Each (message.threadId = ?%d AND message.userId = ?%d AND message.sentAt = ?%d) has O[N] = log[N], where N = number of messages in the table
         * The number of $partials has time complexity O[P] = P, where P = the number of partials
         * The total time complexity is P(log[N]) = numberOfThreads(log[numberOfMessagesInTable])
         */

        $dql = 'SELECT message FROM TriprHqBundle:MessagingGmailMessage message WHERE';
        $parameters = [];
        foreach ($partials as $key => $partial) {
            $dql .= sprintf(
                ' (message.threadId = ?%d AND message.userId = ?%d AND message.sentAt = ?%d) OR',
                3*$key + 0,
                3*$key + 1,
                3*$key + 2
            );
            $parameters[] = $partial['threadId'];
            $parameters[] = $partial['userId'];
            $parameters[] = $partial['latestSentAt'];
        }
        $dql = rtrim($dql, ' OR');
        $latestMessages = $this->getEntityManager()->createQuery($dql)->setParameters($parameters)->getResult();

        return $latestMessages;
    }

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
