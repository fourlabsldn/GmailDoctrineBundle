<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class GmailIdsRepository
 * @package FL\GmailDoctrineBundle\Entity
 */
class GmailIdsRepository extends EntityRepository
{
    /**
     * @param array $userIds
     * @return array
     */
    public function getAllFromUserIds(array $userIds)
    {
        return $this->createQueryBuilder('g')
            ->where('g.userId IN(:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->getResult();
    }
}
