<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This manages and represents a category in the index.
 * @package    sfLucenePlugin
 * @subpackage Storage
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneStorageFilesystem implements sfLuceneStorage
{
  protected $file;

  protected $mode;

  public function __construct($file,  $mode = 0777)
  {
    $this->file = $file;
    $this->mode = $mode;
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

    $retval = file_put_contents($this->file, $data);

    self::chmod($this->file, $this->mode);

    return $retval;
  }

  public function delete()
  {
    unlink($this->file);
  }

  protected function mkdir($dir)
  {
    if (is_dir($dir))
    {
      return true;
    }

    return mkdir($dir, $this->mode, true);
  }

  static public function chmod($file, $mode)
  {
    if (file_exists($file) && substr(sprintf('%o', fileperms($file)), -4) != sprintf('%o', $mode))
    {
      chmod($file, 0777);
    }
  }
}