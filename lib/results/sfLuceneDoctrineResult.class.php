<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * TODO : make the fetch* method a bit more clever
 *
 * Result from the model indexing engine.
 * @package    sfLucenePlugin
 * @subpackage Results
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: sfLuceneModelResult.class.php 25408 2009-12-15 14:17:30Z rande $
 */
class sfLuceneDoctrineResult extends sfLuceneModelResult
{

  /**
   * return the related Doctrine_Record
   *
   * @return Doctrine_Record 
   */
  public function getFetchRecord()
  {
    $model = $this->getInternalModel();
    $id    = $this->getId();

    return Doctrine_Core::getTable($model)
      ->find($id);
  }
  
  /**
   * return the related Doctrine_Record in array mode
   *
   * @return Doctrine_Record 
   */
  public function getFetchArray()
  {
    $model = $this->getInternalModel();
    $id    = $this->getId();
    
    $results = Doctrine_Core::getTable($model)
      ->createQuery()
      ->where('id = ?', $id)
      ->limit(1)
      ->fetchArray();
      
    if(count($results) == 1)
    {
      return $results[0];
    }
    
    return false;
  }
}