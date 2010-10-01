<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A named keyword that is case sensitive.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterKeywordNamed extends sfLuceneHighlighterKeyword
{
  protected $name, $length;

  const SPLIT_REGEX = '/[\p{Z}\p{P}]+/u';
  const RANGE_REGEX = '/[\t\r\n\p{Z}\p{P}]/u';

  protected $strpos = 'mb_strpos';

  public function __construct(sfLuceneHighlighterMarker $highlighter, $name)
  {
    $this->name = $name;
    $this->length = mb_strlen($name);

    parent::__construct($highlighter);
  }

  public function getName()
  {
    return $this->name;
  }

  public function getLength()
  {
    return $this->length;
  }

  public function tokenize($input)
  {
    $tokens = array();
    $length = mb_strlen($input);

    $strpos = $this->strpos;

    for ($i = 0; $i < $length; $i = $position + $this->length)
    {
      $position = $strpos($input, $this->name, $i);

      if ($position === false)
      {
        break;
      }

      if (
          (
            $position + $this->length >= $length // pass if we are the end of the string
            || preg_match(self::RANGE_REGEX, mb_substr($input, $position + $this->length, 1)) // or if the next character is not a word
          )
          && // in addition to that that we only
          (
            $position - 1 <= 0 // pass if we are at the start of the string
            || preg_match(self::RANGE_REGEX, mb_substr($input, $position - 1, 1)) // or if the previous character is not a word
          )
      )
      {
        $tokens[] = new sfLuceneHighlighterToken($this, mb_substr($input, $position, $this->length), $position, $position + $this->length);
      }
    }

    return $tokens;
  }

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