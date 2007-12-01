<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Carl Vondrick
  * @version SVN: $Id$
  */

$modelDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'model';

$files = sfFinder::type('file')->name('*.php')->ignore_version_control()->in($modelDir);

foreach ($files as $file)
{
  require_once $file;
}