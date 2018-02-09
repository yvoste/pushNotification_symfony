<?php

namespace DEUSI\PlatformBundle\Repository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getLikeQueryBuilder($pattern)
    {
      return $this
        ->createQueryBuilder('c')
        ->where('c.name LIKE :pattern')
        ->setParameter('pattern', $pattern)
      ;
    }
}

