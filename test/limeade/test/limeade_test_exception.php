<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_exception is the exception object to practice DRY exception
  * handling unit tests.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_test_exception.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_test_exception
{
  public $lime, $unit_msg, $expecting = true;

  public $regex = null;
  public $type = 'Exception';

  public function __construct(limeade_test $lime, $unit_msg, $expecting = true)
  {
    $this->lime = $lime;
    $this->unit_msg = $unit_msg;
    $this->expecting = $expecting;
  }

  public function type($type)
  {
    $this->type = $type;

    return $this;
  }

  public function regex($regex)
  {
    $this->regex = $regex;

    return $this;
  }

  public function caught(Exception $e)
  {
    // we certainly were not expecting our own exception
    if ($e instanceof limeade_problem)
    {
      throw $e;
    }

    if ($this->expecting)
    {
      if (!($e instanceof $this->type))
      {
        $this->lime->fail($this->unit_msg);
        $this->lime->diag_info('caught exception as expected, but expecting ' . $this->type . ' and got ' . get_class($e));

        return false;
      }
      elseif ($this->regex && !preg_match($this->regex, $e->getMessage()))
      {
        $this->lime->fail($this->unit_msg);
        $this->lime->diag_info('caught exception as expected, but message "' . $e->getMessage() . '" doesn\'t match "' . $this->regex . '"');

        return false;
      }
      else
      {
        $this->lime->pass($this->unit_msg);

        return true;
      }
    }
    else
    {
      $this->lime->fail($this->unit_msg);
      $this->lime->diag_info('not expecting exception, but caught exception ' . $e->getMessage());

      return false;
    }
  }

  public function no()
  {
    if ($this->expecting)
    {
      $this->lime->fail($this->unit_msg);
      $this->lime->diag_info('expecting exception, but none caught');

      return false;
    }
    else
    {
      $this->lime->pass($this->unit_msg);

      return true;
    }
  }
}