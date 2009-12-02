<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Highlighter for HTML data.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterHTML extends sfLuceneHighlighterXHTML
{
  protected function prepare()
  {
    
    $this->document = new DomDocument($this->version, $this->encoding);
    
    $this->document->loadHTML($this->data);

    $this->xpath = new DOMXPath($this->document);

    $this->registerXpathNamespace();
  }

  protected function cleanup()
  {
    $this->data = $this->document->saveHTML();
  }
}