<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class GmailHistoryRepository.
 */
class GmailHistoryRepository extends EntityRepository
{
    /**
     * @param array $userIds
     *
     * @return array
     */
    public function getAllFromUserIds(array $userIds)
    {
        return $this->createQueryBuilder('h')
            ->where('h.userId IN(:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->getResult();
    }
}
