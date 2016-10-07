<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class LabelRepository
 * @package FL\GmailDoctrineBundle\Entity
 */
class GmailLabelRepository extends EntityRepository
{
    /**
     * Get a set of labels by their names.
     * @param $labelNames
     * @return array
     */
    public function getLabelsByName($labelNames)
    {
        return $this->createQueryBuilder('l')
            ->where('l.name IN (:names)')
            ->setParameter(':names', $labelNames)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $userIds
     * @return array
     */
    public function getAllFromUserIds(array $userIds)
    {
        return $this->createQueryBuilder('l')
            ->where('l.userId IN (:ids)')
            ->setParameter('ids', $userIds)
            ->getQuery()
            ->getResult();
    }
}
