<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A very simple that does straight replacements + other string manipulations.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterString extends sfLuceneHighlighter
{
  protected function doHighlight()
  {
    foreach ($this->keywords as $keyword)
    {
      // we reverse because we are probably changing the content length
      foreach (array_reverse($keyword->tokenize($this->data)) as $token)
      {
        $replacement = $keyword->getHighlighter()->highlight($token->getText());

        $this->data = mb_substr($this->data, 0, $token->getStart())
                    . $replacement
                    . mb_substr($this->data, $token->getEnd());
      }
    }
  }

  public function strip()
  {
    $this->data = strip_tags($this->data);

    return $this;
  }

  /**
   * Zooms in on the response where there is most keyword activity
   * This will only crop with what the current keywords are, so make sure that
   * they are all configured before this is run!
   *
   * @todo Find more efficient method (currently O(n^2))
   * @todo Crop only at word boundries (efficiently)
   * @param int $size The size
   */
  public function crop($size, $dots = '...')
  {
    $tokens = array_reverse($this->tokenize($this->data));
    $bestDensity = 0;
    $bestLeft = 0;
    $radius = floor($size / 2);

    foreach ($tokens as $token)
    {
      if ($token->getStart() < $radius)
      {
        $left = 0;
      }
      else
      {
        $left = $token->getStart() - $radius + floor($token->getLength() / 2);
      }

      $currentDensity = 0;

      foreach ($tokens as $token)
      {
        if ($token->getStart() > $left && $token->getEnd() < $left + $size)
        {
          ++$currentDensity;
        }
      }

      if ($currentDensity > $bestDensity)
      {
        $bestDensity = $currentDensity;
        $bestLeft = $left;
      }
    }

    $data = trim(substr($this->data, $bestLeft, $size));

    if ($bestLeft > 0)
    {
      $data = $dots . $data;
    }
    if ($bestLeft + $size < mb_strlen($this->data))
    {
      $data = $data . $dots;
    }

    $this->data = $data;

    return $this;
  }
}