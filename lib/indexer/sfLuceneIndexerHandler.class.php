<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */

abstract class sfLuceneIndexerHandler
{
  protected 
    $search,
    $factory;

  public function __construct($search)
  {
    $this->search  = $search;
    $this->factory = new sfLuceneIndexerFactory($this->getSearch());
  }

  /**
   *
   * @return sfLucene
   */
  protected function getSearch()
  {
    return $this->search;
  }

  protected function getFactory()
  {
    return $this->factory;
  }

  abstract public function rebuild();
}