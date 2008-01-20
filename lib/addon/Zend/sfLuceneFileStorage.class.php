<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

sfLuceneToolkit::loadZend();

/**
 * symfony adapter for Zend_Search_Lucene_Storage_File_Filesystem
 * @package sfLucenePlugin
 * @subpackage Addon
 * @version SVN: $Id$
 */
class sfLuceneFileStorage extends Zend_Search_Lucene_Storage_File_Filesystem
{
  public function __construct($filename, $mode = 'r+b')
  {
    parent::__construct($filename, $mode);

    sfLuceneStorageFilesystem::chmod($filename, 0777);
  }
}