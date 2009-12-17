<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    sfLucenePlugin
 * @subpackage Helper
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

function highlight_result_text($text, $query, $size = 200, $sprint = '<strong class="highlight">%s</strong>')
{

  $highlighter = new sfLuceneHighlighterString($text);

  $marker = new sfLuceneHighlighterMarkerSprint($sprint);
  $harness = new sfLuceneHighlighterMarkerHarness(array($marker));

  $keywords = sfLuceneHighlighterKeywordNamedInsensitive::explode($harness, $query);

  $highlighter->addKeywords($keywords);
  $highlighter->strip()->crop($size);

  return $highlighter->highlight()->export();
}

function highlight_keywords($text, $keywords, $sprint = '<strong class="highlight">%s</strong>')
{
  $highlighter = new sfLuceneHighlighterXHTMLPart($text);
  $highlighter->setMasterDtd(sfConfig::get('app_lucene_xhtml_dtd', 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'));

  $marker = new sfLuceneHighlighterMarkerSprint($sprint);
  $harness = new sfLuceneHighlighterMarkerHarness(array($marker));

  $keywords = sfLuceneHighlighterKeywordNamedInsensitive::explode($harness, $keywords);
  
  $highlighter->addKeywords($keywords);

  return $highlighter->highlight()->export();
}

function add_highlight_qs($query, $keywords)
{
  $keywords = preg_split('/\W+/', $keywords, -1, PREG_SPLIT_NO_EMPTY);

  $suffix = '';

  if (preg_match('/(#\w+)$/', $query, $matches, PREG_OFFSET_CAPTURE))
  {
    $query = substr($query, 0, $matches[0][1]);

    $suffix = $matches[0][0];
  }

  if (false === stripos($query, '?'))
  {
    $query .= '?';
  }
  else
  {
    $query .= '&';
  }

  $query .= 'sf_highlight=' . implode($keywords, ' ') . $suffix;

  return $query;
}
