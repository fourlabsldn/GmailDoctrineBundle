<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Model\GmailMessageInterface;

/**
* Class GmailMessageRepository
* @package FL\GmailDoctrineBundle\Entity
*/
class  GmailMessageRepository extends EntityRepository
{
    const LABEL_SPAM = "SPAM";
    const LABEL_TRASH = "TRASH";

    /**
     * @see GmailMessageRepository::uniqueByThreadPartials()
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $dateSort
     * @param string|null $userId
     * @param string[]|null $labelNames
     * @param string|null $from
     * @param string|null $to
     *
     * @return GmailMessageInterface[]
     */
    public function findUniqueByThread(int $limit = null, int $offset = null, string $dateSort = null, string $userId = null, array $labelNames = null, string $from = null, string $to = null) {
        $partials = $this->uniqueByThreadPartials($limit, $offset, $dateSort, $userId, $labelNames);

        $dql = 'SELECT message, labels FROM TriprHqBundle:MessagingGmailMessage message LEFT JOIN message.labels labels ';
        $parameters = [];
        $nextParameterKey = 0;

        $this->uniqueByThreadWhereClause($dql, $parameters, $nextParameterKey, $userId, null, $from, $to); // passing labelNames as null here ensures each message is hydrated with all its labels

        if (count($partials) > 0) {
            $dql .= ' AND ( ';
            foreach ($partials as $partial) {
                $dql .= sprintf(
                    ' (message.threadId = ?%d AND message.userId = ?%d AND message.sentAt = ?%d) OR ',
                    $nextParameterKey + 0,
                    $nextParameterKey + 1,
                    $nextParameterKey + 2
                );
                $parameters[] = $partial['threadId'];
                $parameters[] = $partial['userId'];
                $parameters[] = $partial['latestSentAt'];
                $nextParameterKey = $nextParameterKey + 3;
            }
            $dql = rtrim($dql, ' OR ');
            $dql .= ')';
            switch ($dateSort) {
                case 'ASC':
                    $dql .= ' ORDER BY message.sentAt ASC';
                    break;
                case 'DESC':
                    $dql .= ' ORDER BY message.sentAt DESC';
                    break;
                case null:
                    // avoid the exception
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid dateSort, must be ASC or DESC');
            }
            return $this->getEntityManager()->createQuery($dql)->setParameters($parameters)->getResult();
        }
        return [];
    }

    /**
     * @see GmailMessageRepository::uniqueByThreadPartials()
     *
     * @param string|null $userId
     * @param string[]|null $labelNames
     * @param string|null $from
     * @param string|null $to
     *
     * @return int
     */
    public function countUniqueByThread(string $userId = null, array $labelNames = null, string $from = null, string $to = null)
    {
        return count($this->uniqueByThreadPartials(null, null, null, $userId, $labelNames, $from, $to));
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $dateSort ('ASC', 'DESC', null)
     * @param string|null $userId (null = all results independent of userId)
     * @param string[]|null $labelNames (null = all results independent of labelName)
     * @param string|null $from (null = all results independent of from)
     * @param string|null $to (null = all results independent of to)
     *
     * @return array
     * Note: Each element in the return array looks like this:
     *  [
     *      'threadId' => 'someThreadId',
     *      'userId' => 'someUserId',
     *      'latestSentAt' => 'someDateString',
     *  ]
     *
     *
     * ThreadIds may or may not collide across userIds, play it safe!
     * @link http://stackoverflow.com/questions/25198394/are-gmail-thread-ids-unique-across-users
     *
     * If there are a lot of threads, look to optimize this query
     * E.g. index the columns={"thread_id", "user_id", "sent_at"} in your GmailMessage entity
     * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/annotations-reference.html#annref-index
     *
     * With indexes, the time complexity for partials is P(log[N]) = numberOfPartials(log[numberOfMessagesInTable])
     */
    private function uniqueByThreadPartials(int $limit = null, int $offset = null, string $dateSort = null, string $userId = null, array $labelNames = null, string $from = null, string $to = null)
    {
        $dql ='SELECT message.threadId, message.userId, max(message.sentAt) AS latestSentAt FROM TriprHqBundle:MessagingGmailMessage message LEFT JOIN message.labels labels ';

        $parameters = [];
        $nextParameterKey = 0;

        $this->uniqueByThreadWhereClause($dql, $parameters, $nextParameterKey, $userId, $labelNames, $from, $to);
        $dql .= ' GROUP BY message.threadId, message.userId ';

        switch ($dateSort) {
            case 'ASC':
                $dql .= ' ORDER BY latestSentAt ASC';
                break;
            case 'DESC':
                $dql .= ' ORDER BY latestSentAt DESC';
                break;
            case null:
                // avoid the exception
                break;
            default:
                throw new \InvalidArgumentException('Invalid dateSort, must be ASC or DESC');
        }

        $queryThreadIds = $this->getEntityManager()->createQuery($dql)->setParameters($parameters);

        if ( is_null($limit) && is_int($offset)) {
            $queryThreadIds->setFirstResult($offset);
        }
        if ( is_int($limit) && is_null($offset)) {
            $queryThreadIds->setMaxResults($limit);
        }
        if (is_int($limit) && is_int($offset)) {
            $queryThreadIds->setMaxResults($limit);
            $queryThreadIds->setFirstResult($offset);
        }

        return $queryThreadIds->getResult();
    }

    /**
     * @param string $dql
     * @param array $parameters
     * @param int $nextParameterKey
     * @param string|null $userId
     * @param array|null $labelNames
     * @param string|null $from
     * @param string|null $to
     */
    private function uniqueByThreadWhereClause(string &$dql, array &$parameters, int &$nextParameterKey, string $userId = null, array $labelNames = null, string $from = null, string $to = null)
    {
        /**
         * If no where statements are created, append 'WHERE true=true' to the $dql
         * such that 'AND' statements can be appended safely  to the $dql
         */
        $dql .= ' WHERE true=true AND '; //
        if (is_array($labelNames) && count($labelNames)  > 0) {
            $dql .= sprintf(' labels.name IN (?%d)  AND ', $nextParameterKey);
            $parameters[] = $labelNames;
            $nextParameterKey++;
        }
        if (is_string($userId)) {
            $dql .= sprintf(' message.userId = ?%d  AND ', $nextParameterKey);
            $parameters[] = $userId;
            $nextParameterKey++;
        }
        if (is_string($from)) {
            $dql .= sprintf(' message.from LIKE ?%d  AND ', $nextParameterKey);
            $parameters[] = '%' . $from .'%';
            $nextParameterKey++;
        }
        if (is_string($to)) {
            $dql .= sprintf(' message.to LIKE ?%d  AND ', $nextParameterKey);
            $parameters[] = '%' . $to .'%';
            $nextParameterKey++;
        }
        $dql = rtrim($dql, ' AND ');
    }
}
