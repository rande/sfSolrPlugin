<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* This class represents a base task for all sfLucene tasks.
*
* @author Carl Vondrick <carlv@carlsoft.net>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

abstract class sfLuceneBaseTask extends sfBaseTask
{
  protected function standardBootstrap($app)
  {
    $this->bootstrapSymfony($app, 'search', true);
  }
}