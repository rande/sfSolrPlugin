<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Manages all of the highlighter markers
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterMarkerHarness
{
  protected $highlighters = array();
  protected $pointer = 0;
  protected $count = 0;

  public function __construct(array $highlighters)
  {
    $this->highlighters = $highlighters;
    $this->count = count($highlighters);

    if ($this->count == 0)
    {
      throw new sfLuceneHighlighterException('The highlighter array must contain at least one highlighter.');
    }
  }

  public function getHighlighter()
  {
    // returns the highlighter and the increments pointer by 1
    return $this->highlighters[$this->pointer++ % $this->count];
  }
}