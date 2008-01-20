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

class sfLuceneEventConnectorLogger extends sfLuceneEventConnector
{
  protected $formatter, $section, $size;

  public function __construct(sfEventDispatcher $source, $sourceName, sfEventDispatcher $target, $targetName, sfFormatter $formatter, $section = null, $size = null)
  {
    $this->formatter = $formatter;
    $this->section = $section;
    $this->size = $size;

    parent::__construct($source, $sourceName, $target, $targetName);
  }

  public function passoff(sfEvent $event)
  {
    $params = $event->getParameters();

    $string = call_user_func_array('sprintf', $params);

    if ($this->section)
    {
      $string = $this->formatter->formatSection($this->section, $string, $this->size);
    }
    else
    {
      $string = $this->formatter->format($string);
    }

    $this->target->notify(new sfEvent($event->getSubject(), $this->targetName, array($string)));
  }
}