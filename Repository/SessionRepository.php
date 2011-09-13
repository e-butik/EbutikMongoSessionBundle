<?php

namespace Ebutik\MongoSessionBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
* @author Magnus Nordlander
**/
class SessionRepository extends DocumentRepository
{
  /**
   * @author Magnus Nordlander
   **/
  public function purgeBefore(\DateTime $time)
  {
    $this->createQueryBuilder()
         ->remove()
         ->field('accessed_at')->lt($time)
         ->getQuery()
         ->execute();
  }
}