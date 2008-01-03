<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides an XML-based highlighter for highlighting XML documents.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterXML extends sfLuceneHighlighter
{
  protected $version;
  protected $encoding;
  protected $document;
  protected $xpath;
  protected $xpathQuery = '/';

  /**
   * Constructor.
   * @param string $data The well-formed XML string
   * @param string $version The XML version of the string
   * @param string $encoding The encoding of the string
   */
  public function __construct($data, $version = null, $encoding = null)
  {
    $this->version = $version;
    $this->encoding = $encoding;

    parent::__construct($data);
  }

  /**
   * Prepares a DOMDocument to begin highlighting.
   */
  protected function prepare()
  {
    libxml_clear_errors();
    $oldXmlError = libxml_use_internal_errors(true);

    $this->document = new DomDocument($this->version, $this->encoding);
    $this->document->resolveExternals = true;
    $this->document->substituteEntities = true;

    if (!$this->document->loadXML($this->data))
    {
      $errors = libxml_get_errors();

      libxml_clear_errors(); // free memory
      libxml_use_internal_errors($oldXmlError); // restore error reporting

      throw new sfLuceneHighlighterXMLException('XML document failed to parse correctly, aborting highlighting', $errors);
    }

    libxml_clear_errors(); // free memory
    libxml_use_internal_errors($oldXmlError); // restore error reporting

    $this->xpath = new DOMXPath($this->document);
  }

  /**
   * Exports the DOMDocument as XML.
   */
  protected function cleanup()
  {
    $this->data = $this->document->saveXML();
  }

  /**
   * Launches the highlight procedure.
   */
  protected function doHighlight()
  {
    foreach ($this->xpath->query($this->xpathQuery) as $node)
    {
      $this->doHighlightNode($node);
    }
  }

  /**
   * Highlights a DOM node and all its children.
   *
   * @param DOMNode $node The node to highlight
   */
  protected function doHighlightNode(DOMNode $node)
  {
    $texts = array();

    if (!$node->hasChildNodes())
    {
      return;
    }

    foreach ($node->childNodes as $child)
    {
      if ($child->nodeType == XML_TEXT_NODE)
      {
        $texts[] = $child;
      }
      else
      {
        $this->doHighlightNode($child);
      }
    }

    foreach ($texts as $textNode)
    {
      $this->doHighlightTextNode($textNode);
    }
  }

  /**
   * Highlights a text node
   *
   * @param DOMNode $node The text node to highlight
   */
  protected function doHighlightTextNode(DOMNode $node)
  {
    foreach ($this->tokenize($node->nodeValue) as $token)
    {
      $node->splitText($token->getEnd());
      $matched = $node->splitText($token->getStart());

      $highlighted = $this->document->createDocumentFragment();
      $highlighted->appendXML($token->getKeyword()->getHighlighter()->highlight($token->getText()));

      $node->parentNode->replaceChild($highlighted, $matched);
    }
  }
}