<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides a universal highligher for the plugin that can automatically work
 * around HTML code.  This highlighter will only highlight text in the body,
 * not inside an HTML tag, and not inside a text area.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
abstract class sfLuceneHighlighter
{
  protected $data = null;

  protected $keywords = array();

  protected $highlighted = false;

  public function __construct($data)
  {
    $this->data = $data;
  }

  public function highlight()
  {
    $this->prepare();
    $this->doHighlight();
    $this->cleanup();

    return $this;
  }

  abstract protected function doHighlight();

  /**
   * Does any preparation on the data for highlighting
   */
  protected function prepare()
  {
  }

  /**
   * Cleans up the data after highlighting
   */
  protected function cleanup()
  {
  }

  public function export()
  {
    return $this->data;
  }

  public function __toString()
  {
    return $this->export();
  }

  protected function tokenize($input)
  {
    $tokens = array();

    foreach ($this->keywords as $keyword)
    {
      $tokens = array_merge($tokens, $keyword->tokenize($input));
    }

    usort($tokens, array('sfLuceneHighlighterToken', 'prepareForHighlighting'));

    return $tokens;
  }

  /**
   * Queues keywords to be potentially highlighted.
   * @param array $keywords Array of sfLuceneHighlighterKeyword instances
   */
  public function addKeywords(array $keywords)
  {
    $this->keywords = array_merge($this->keywords, $keywords);

    return $this;
  }

  public function getKeywords()
  {
    return $this->keywords;
  }
}