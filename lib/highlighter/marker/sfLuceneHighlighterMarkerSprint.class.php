<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Highlights a single string by running it through sprintf
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterMarkerSprint extends sfLuceneHighlighterMarker
{
  protected $pattern;

  public function __construct($pattern)
  {
    $this->pattern = $pattern;
  }

  public function highlight($input)
  {
    return sprintf($this->pattern, $input);
  }

  static public function generate(array $patterns)
  {
    $retval = array();

    foreach ($patterns as $pattern)
    {
      $retval[] = new self($pattern);
    }

    return new sfLuceneHighlighterMarkerHarness($retval);
  }
}