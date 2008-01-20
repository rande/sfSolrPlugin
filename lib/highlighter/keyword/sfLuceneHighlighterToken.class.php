<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A token that is typically used to represent matches from keywords ->tokenize()
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterToken
{
  protected $text, $keyword, $start, $end;

  public function __construct(sfLuceneHighlighterKeyword $keyword, $text, $start, $end)
  {
    if ($end <= $start)
    {
      throw new sfLuceneHighlighterException('A token\'s end point must be greater than the start point');
    }

    $this->text = $text;
    $this->keyword = $keyword;
    $this->start = $start;
    $this->end = $end;
  }

  public function getText()
  {
    return $this->text;
  }

  public function getKeyword()
  {
    return $this->keyword;
  }

  public function getStart()
  {
    return $this->start;
  }

  public function getEnd()
  {
    return $this->end;
  }

  public function getLength()
  {
    return $this->end - $this->start;
  }

  static public function prepareForHighlighting(self $a, self $b)
  {
    if ($a->getStart() < $b->getStart())
    {
      return 1;
    }
    elseif ($a->getStart() > $b->getStart())
    {
      return -1;
    }
    else
    {
      return 0;
    }
  }
}