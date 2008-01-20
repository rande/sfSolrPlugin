<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides an XML-based highlighter for highlighting XML documents.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
abstract class sfLuceneHighlighterKeyword
{
  protected $highlighter;

  public function __construct(sfLuceneHighlighterMarker $highlighter)
  {
    $this->highlighter = $highlighter;
  }

  public function getHighlighter()
  {
    return $this->highlighter;
  }

  abstract public function tokenize($input);
}