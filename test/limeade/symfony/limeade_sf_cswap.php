<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf_cswap can swap in different configuration files without clearing
  * the cache.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf_cswap.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_sf_cswap
{
  public $app, $file, $backup;

  protected $modified = false;

  public function __construct(limeade_sf_app $app, $file)
  {
    $file = sfConfigCache::getInstance()->checkConfig($file);

    $this->app = $app;
    $this->file = $file;
    $this->backup = $file . '~' . md5($file . __FILE__);
  }

  public function backup()
  {
    if (!$this->modified)
    {
      copy($this->file, $this->backup);
    }

    return $this;
  }

  public function restore()
  {
    if ($this->modified)
    {
      rename($this->backup, $this->file);

      $this->modified = false;
    }

    return $this;
  }

  public function write($data)
  {
    $this->backup();

    file_put_contents($this->file, $data);

    $this->modified = true;

    return $this;
  }

  public function read()
  {
    return file_get_contents($this->file);
  }
}