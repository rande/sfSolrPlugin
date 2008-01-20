<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Highlighter for XHTML data.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterXHTML extends sfLuceneHighlighterXML
{
  protected $xpathQuery = '/html/body';

  protected function prepare()
  {
    parent::prepare();

    $this->registerXpathNamespace();
  }

  protected function registerXpathNamespace()
  {
    if ($this->document->documentElement && $this->document->documentElement->namespaceURI)
    {
      $this->xpath->registerNamespace('x', $this->document->documentElement->namespaceURI);
      $ns = 'x:';
    }
    else
    {
      $ns = '';
    }

    $this->xpathQuery = '/' . $ns . 'html/' . $ns . 'body';
  }

  protected function ignoreNode(DOMNode $node)
  {
    if (!parent::ignoreNode($node))
    {
      return ($node->nodeName == 'script' || $node->nodeName == 'style' || $node->nodeName == 'textarea');
    }

    return true;
  }
}