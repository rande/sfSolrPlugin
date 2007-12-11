<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This manages and represents a category in the index.
 * @package    sfLucenePlugin
 * @subpackage Storage
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneStorageFilesystem implements sfLuceneStorage
{
  protected $file;

  public function __construct($file)
  {
    $this->file = $file;
  }

  public function read()
  {
    if (file_exists($this->file))
    {
      return file_get_contents($this->file);
    }

    return null;
  }

  public function write($data)
  {
    $this->mkdir(dirname($this->file));

    return file_put_contents($this->file, $data);
  }

  public function delete()
  {
    unlink($this->file);

    clearstatcache();
  }

  protected function mkdir($dir)
  {
    if (is_dir($dir))
    {
      return true;
    }

    return mkdir($dir, 0777, true);
  }
}