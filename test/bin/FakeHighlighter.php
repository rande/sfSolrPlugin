<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Carl Vondrick
  * @version SVN: $Id$
  */

class FakeHighlighter extends sfLuceneHighlightFilter
{
  public function __construct()
  {
    $this->initialize(sfContext::getInstance());

    $this->setParameter('check_referer', true);
    $this->setParameter('highlight_qs', 'h');
    $this->setParameter('notice_tag', '~notice~');
    $this->setParameter('highlight_strings', array('<highlighted>%s</highlighted>', '<highlighted2>%s</highlighted2>'));
    $this->setParameter('notice_referer_string', '<from>%from%</from><keywords>%keywords%</keywords><remove>%remove%</remove>');
    $this->setParameter('notice_string', '<keywords>%keywords%</keywords><remove>%remove%</remove>');
    $this->setParameter('remove_string', '~remove~');
    $this->setParameter('css', 'search.css');
  }
}