<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_loader loads the limeade framework.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_loader.php 6959 2008-01-06 03:42:17Z Carl.Vondrick $
  */
class limeade_loader
{
  static public function core()
  {
    $root = dirname(__FILE__) . DIRECTORY_SEPARATOR;

    require_once $root . 'limeade_exception.php';
    require_once $root . 'limeade.php';
  }

  static public function test()
  {
    $root = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;

    require_once $root . 'limeade_test.php';
    require_once $root . 'limeade_test_exception.php';
  }

  static public function symfony()
  {
    $root = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'symfony' . DIRECTORY_SEPARATOR;

    require_once $root . 'limeade_sf.php';
    require_once $root . 'limeade_sf_app.php';
    require_once $root . 'limeade_sf_config.php';
    require_once $root . 'limeade_sf_event.php';
    require_once $root . 'limeade_sf_i18n.php';
    require_once $root . 'limeade_sf_cswap.php';
  }

  static public function extra()
  {
    $root = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'extra' . DIRECTORY_SEPARATOR;

    require_once $root . 'limeade_output.php';
    require_once $root . 'limeade_output_html.php';
  }

  static public function all()
  {
    self::core();
    self::test();
    self::symfony();
    self::extra();
  }
}
