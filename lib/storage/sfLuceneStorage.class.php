<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This interface defines the storage containers.
 * @package    sfLucenePlugin
 * @subpackage Storage
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

interface sfLuceneStorage
{
  public function __construct($file);

  public function read();

  public function write($data);

  public function delete();
}