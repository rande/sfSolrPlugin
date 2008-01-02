<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Highlights a single string
 *
 * @package    sfLucenePlugin
 * @subpackage Highlighter
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
abstract class sfLuceneHighlighterMarker
{
  abstract public function highlight($input);
}