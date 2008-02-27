<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf_event provides a way to test the event dispatcher.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf_event.php 6959 2008-01-06 03:42:17Z Carl.Vondrick $
  */
class limeade_sf_event
{
  public $limeade, $dispatcher, $listen_to, $unit_msg, $num = 1, $callback = null;

  protected $received = array();

  /**
   * Constructor.  If $unit_msg is false, then this will not report.
   */
  public function __construct(limeade_sf $limeade, sfEventDispatcher $dispatcher, $listen_to, $unit_msg, $num = 1)
  {
    $this->limeade = $limeade;
    $this->dispatcher = $dispatcher;
    $this->listen_to = $listen_to;
    $this->unit_msg = $unit_msg;
    $this->num = $num;
  }

  public function connect()
  {
    $this->dispatcher->connect($this->listen_to, array($this, 'listener'));

    return $this;
  }

  public function disconnect()
  {
    $this->dispatcher->disconnect($this->listen_to, array($this, 'listener'));

    return $this;
  }

  public function listener(sfEvent $event)
  {
    $this->received[] = $event;

    if ($this->callback)
    {
      return call_user_func($this->callback, $event);
    }
  }

  public function callback($callback)
  {
    $this->callback = $callback;

    return $this;
  }

  public function count()
  {
    return count($this->received);
  }

  public function get()
  {
    return $this->received;
  }

  public function getLast()
  {
    if ($this->count() == 0)
    {
      return null;
    }

    return $this->received[$this->count() - 1];
  }

  public function done()
  {
    $retval = null;

    if (($this->unit_msg))
    {
      if ($this->count() == $this->num)
      {
        $this->limeade->lime->pass($this->unit_msg);

        $retval = true;
      }
      else
      {
        $this->limeade->lime->fail($this->unit_msg);
        $this->limeade->lime->diag_info('Expected to hear ' . $this->num . ' events, but heard ' . $this->count());

        $retval = false;
      }
    }

    $this->disconnect();

    return $retval;
  }
}