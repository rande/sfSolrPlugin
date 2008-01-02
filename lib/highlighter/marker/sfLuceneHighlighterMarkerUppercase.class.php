<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Highlights a single string by making it uppercase.
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlighterMarkerUppercase extends sfLuceneHighlighterMarker
{
  public function highlight($input)
  {
    return strtoupper($input);
  }

  static public function generate()
  {
    return new sfLuceneHighlighterMarkerHarness(array(new self));
  }
}