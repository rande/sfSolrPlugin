<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Responsible for handling Doctrine's behaviors.
 * @package    sfLucenePlugin
 * @subpackage Behavior
 * @author     Carl Vondrick <carlv@carlsoft.net>
 */
class sfLuceneDoctrineTemplate extends Doctrine_Template
{
  /**
   * setTableDefinition
   *
   * @return void
   */
  public function setTableDefinition()
  {
    $this->addListener(new sfLuceneDoctrineListener);
  }
}