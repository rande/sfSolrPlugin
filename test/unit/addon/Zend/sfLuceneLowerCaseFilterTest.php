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

require dirname(__FILE__) . '/../../../bootstrap/unit.php';

$t = new lime_test(3, new lime_output_color());

$filter = new sfLuceneLowerCaseFilter(false);
$token = new Zend_Search_Lucene_Analysis_Token('This is a SIMPLE tEsT!', 0, 10);
$t->is($filter->normalize($token)->getTermText(), 'this is a simple test!', '->normalize() converts string to lower case with mb_string off');

$filter = new sfLuceneLowerCaseFilter(true);

$token = new Zend_Search_Lucene_Analysis_Token('This is a SIMPLE tEsT!', 0, 10);
$t->is($filter->normalize($token)->getTermText(), 'this is a simple test!', '->normalize() converts regular string to lower case with mb_string on');

$token = new Zend_Search_Lucene_Analysis_Token('çĎĤŃ', 0, 10);
$t->is($filter->normalize($token)->getTermText(), 'çďĥń', '->normalize() converts UTF8 string to lower case with mb_string on');