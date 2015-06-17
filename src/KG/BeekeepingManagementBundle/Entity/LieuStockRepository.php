<?php

namespace KG\BeekeepingManagementBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * LieuStockRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LieuStockRepository extends EntityRepository
{
    public function getList($page=1, $maxperpage=10)
    {
        $q = $this->_em->createQueryBuilder()
             ->select('lieuStock')
             ->from('KGBeekeepingManagementBundle:LieuStock','lieuStock');
        
        $q->setFirstResult(($page-1)*$maxperpage)
          ->setMaxResults($maxperpage);
        
        return new Paginator($q);
    }
    
    public function getNbLieuStockTotal()
    {
        return $this->_em->createQueryBuilder()
                ->select('count(lieuStock.id)')
                ->from('KGBeekeepingManagementBundle:LieuStock','lieuStock')
                ->getQuery()
                ->getSingleScalarResult();
    }
}