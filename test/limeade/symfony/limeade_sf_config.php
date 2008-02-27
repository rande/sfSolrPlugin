<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf_config provides tests and access to sfConfig
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf_config.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_sf_config
{
  public $limeade;

  public function __construct(limeade_sf $limeade)
  {
    $this->limeade = $limeade;
  }

  /**
   * Sets a key in symfony config
   */
  public function set($key, $value)
  {
    sfConfig::set($key, $value);

    return $this;
  }

  /**
   * Returns a symfony config value
   */
  public function get($key)
  {
    return sfConfig::get($key, null);
  }

  public function add($data)
  {
    sfConfig::add($data);

    return $this;
  }

  /**
   * Removes a key from symfony config
   */
  public function remove($key)
  {
    $all = sfConfig::getAll();
    unset($all[$key]);
    sfConfig::clear();
    sfConfig::add($all);

    return $this;
  }

  public function is($key, $expected, $msg = null)
  {
    return $this->limeade->lime->is($this->get($key), $expected, $msg);
  }

  public function isnt($key, $unexpected, $msg = null)
  {
    return $this->limeade->lime->isnt($this->get($key), $unexpected, $msg);
  }

  public function __get($key)
  {
    return $this->get($key);
  }

  public function __set($key, $value)
  {
    $this->set($key, $value);
  }
}