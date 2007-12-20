<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    sfLucenePlugin
 * @subpackage Helper
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */

function include_search_result($result, $query = null)
{
  include_partial($result->getInternalPartial(), array('result' => $result, 'query' => $query));
}

function include_search_controls($form)
{
  include_partial('sfLucene/controls', array('form' => $form));
}

function include_search_pager($pager, $form, $radius = 5)
{
  include_partial('sfLucene/pagerNavigation', array('pager' => $pager, 'form' => $form, 'radius' => $radius));
}

function highlight_result_text($text, $query, $size = 200, $highlighter = '<strong class="highlight">%s</strong>')
{
  $h = new sfLuceneHighlighter($text);
  $h->addKeywordSlug($query);
  $h->addHighlighter($highlighter);
  $h->hasBody(false);
  $h->densityCrop($size);
  return $h->highlight();
}

function highlight_keywords($text, $keywords, $highlighter = '<strong class="highlight">%s</strong>')
{
  $h = new sfLuceneHighlighter($text);
  $h->addKeywordSlug($keywords);
  $h->addHighlighter($highlighter);
  $h->hasBody(false);

  return $h->highlight();
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