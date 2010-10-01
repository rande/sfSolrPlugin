<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A named keyword that is case insensitive.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterKeywordNamedInsensitive extends sfLuceneHighlighterKeywordNamed
{
  protected $strpos = 'mb_stripos';

  static public function generate(sfLuceneHighlighterMarkerHarness $highlighters, array $keywords)
  {
    $retval = array();

    $keywords  = array_unique($keywords);

    foreach ($keywords as $keyword)
    {
      $retval[] = new self($highlighters->getHighlighter(), $keyword);
    }

    return $retval;
  }

  static public function explode(sfLuceneHighlighterMarkerHarness $highlighters, $keywords)
  {
    return self::generate($highlighters, array_unique(preg_split(self::SPLIT_REGEX, $keywords)));
  }
}