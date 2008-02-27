<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade attempts to ease unit testing by providing some additional features
  * to the lime unit testing framework.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
abstract class limeade
{
  public $lime = null;

  public function __construct(limeade_test $lime)
  {
    $this->lime = $lime;
  }
}