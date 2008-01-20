<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a simple way for the sfLucene event dispatcher to communicate with
 * another dispatcher in a symfony application.
 *
 * @package    sfLucenePlugin
 * @subpackage Event
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneEventConnector
{
  protected $source, $target;

  protected $sourceName, $targetName;

  public function __construct(sfEventDispatcher $source, $sourceName, sfEventDispatcher $target, $targetName)
  {
    $this->source = $source;
    $this->target = $target;

    $this->sourceName = $sourceName;
    $this->targetName = $targetName;

    $this->link();
  }

  protected function link()
  {
    $this->source->connect($this->sourceName, array($this, 'passoff'));
  }

  public function passoff(sfEvent $event)
  {
    $this->target->notify(new sfEvent($event->getSubject(), $this->targetName, $event->getParameters()));
  }
}