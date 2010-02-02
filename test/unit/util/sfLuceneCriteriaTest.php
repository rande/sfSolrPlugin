<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
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

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(34, limeade_output::get());

class Foo { }

function inst()
{
  return sfLuceneCriteria::newInstance();
}

$t->isa_ok(sfLuceneCriteria::newInstance(), 'sfLuceneCriteria', '::newInstance() returns an sfLuceneCriteria object');

$criteria = inst();

$t->diag('testing ->getQuery()');
$t->ok(is_string($criteria->getQuery()), '->getQuery() returns an instance a string');


$t->diag('testing ->add()');
$criteria->add('test', sfLuceneCriteria::TYPE_AND);
$t->pass('->add() accepts a string');

$queries = inst()->add('foo')->add('bar')->getQuery();
$t->cmp_ok($queries, '===', 'foo AND bar', '->add() correctly parses and adds text queries');

$queries = inst()->add('foo')->add('bar', sfLuceneCriteria::TYPE_OR)->getQuery();
$t->cmp_ok($queries, '===', 'foo OR bar', '->add() correctly parses and adds text queries');

$query = inst();
$query->add('foo');

$criteria->add($query, null);
$t->pass('->add() accepts sfLuceneCriteria');

$luceneQuery = inst()->add($query);
$luceneQuery->add('bar', sfLuceneCriteria::TYPE_OR);
$subqueries = inst()->add($luceneQuery)->getQuery();

$t->cmp_ok($subqueries, '==', '((foo) OR bar)', '->getQuery() correctly combines sfLuceneCriteria queries');

try {
  $criteria->add($criteria, true);
  $t->fail('->add() rejects itself');
} catch (Exception $e) {
  $t->pass('->add() rejects itself');
}

try {
  $criteria->add(new Foo());
  $t->fail('->add() rejects invalid queries');
} catch (Exception $e) {
  $t->pass('->add() rejects invalid queries');
}

$t->diag('testing ->addString()');

$criteria->add('test');

$queries = inst()->addString('foobar')->getQuery();
$t->cmp_ok($queries, '===', $queries , '->addString() correctly parses and adds string queries');

$t->diag('testing ->addSane()');

$criteria = inst($app_application);
$criteria->addSane('test'); 
$s = $criteria->getQuery();
$t->cmp_ok($s, '===', '("test")', '::addSane() with standard string');

$criteria->addSane('&" ? \unsafe'); 
$s = $criteria->getQuery();

$t->cmp_ok($s, '===', '("test") AND ("&" OR "?" OR "\\\\unsafe")', '::addSane() with standard string');

try {
  $criteria->add('carl!');
  $t->fail('->add() rejects an illegal query');
} catch (Exception $e) {
  $t->pass('->add() rejects an illegal query');
}

try {
  $criteria->addSane('carl!');
  $t->pass('->addSane() accepts an illegal query');
} catch (Exception $e) {
  $t->fail('->addSane() accepts an illegal query');
  $t->skip('->addSane() correctly adds an illegal query');
}

$t->diag('testing addWildcard()');

$s = inst()->addWildcard('foo*')->getQuery();
$t->cmp_ok($s, '===', '"foo*"', '->addWildcard() registers the correct query with mutlitple character wildcards');

$s = inst()->addWildcard('f?o')->getQuery();
$t->cmp_ok($s, '===', '"f?o"', '->addWildcard() registers the correct query with single character wildcards');

$s = inst()->addWildcard('foo* baz?')->getQuery();
$t->cmp_ok($s, '===', '"foo* baz?"', '->addWildcard() registers the correct query with mixing character wildcards');

$t->diag('testing addPhrase()');
$s = inst()->addPhrase("foo bar")->getQuery();

$t->cmp_ok($s, '==', '"foo bar"', '->addPhrase() registers the correct simple phrase query');

$t->diag('testing addRange()');

$s = inst()->addRange('a', 'b')->getQuery();
$t->cmp_ok($s, '===', '[a TO b]', '->addRange() registers a simple, two-way range');

$s = inst()->addRange('a')->getQuery();
$t->cmp_ok($s, '===', '[a TO *]', '->addRange() registers a simple, one-way forward range');

$s = inst()->addRange(null, 'b')->getQuery();
$t->cmp_ok($s, '===', '[* TO b]', '->addRange() registers a simple, one-way backward range');

try {
  $s = inst()->addRange(null, null);
  $t->fail('->addRange() rejects a query with no range');
} catch (Exception $e) {
  $t->pass('->addRange() rejects a query with no range');
}

$t->diag('testing addProximity()');

try {
  inst()->addProximity(37.7752, -122.4192, 0);
  $t->fail('->addProximity() rejects a zero proximity');
} catch (Exception $e) {
  $t->pass('->addProximity() rejects a zero proximity');
}

try {
  inst()->addProximity(37.7752, -122.4192, 90, 0);
  $t->fail('->addProximity() rejects a zero radius');
} catch (Exception $e) {
  $t->pass('->addProximity() rejects a zero radius');
}

// not sure this test is fine ... float computation might differ from cpu to cpu
$s = inst()->addProximity(37.7752, -122.4192, 200)->getQuery();
$expected = '(latitude:[35.9785590093 TO 39.5718409907] AND longitude:[-124.6922199992 TO -120.1461800008])';
$t->cmp_ok($s, '===', $expected, '->addProximity()');


$t->diag('testing getNewCriteria()');

$t->isa_ok(inst()->getNewCriteria(), 'sfLuceneCriteria', '->getNewCriteria() returns a new instance of sfLuceneCriteria');

$s = inst()->add('toto')->add(' ')->add('pipop')->getQuery();

$expected = 'toto AND pipop';
$t->cmp_ok($s, '===', $expected, '->add() with empty string');

$t->diag('testing addPhraseGuess()');

$s = inst()->addPhraseGuess('Thomas -"zend framework"')->getQuery();
$expected = '(-("zend framework")) AND (("Thomas"))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

$s = inst()->addPhraseGuess('"Thomas"   -"zend framework"')->getQuery();
$expected = '(-("zend framework")) AND (("Thomas"))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

$s = inst()->addPhraseGuess('"Thomas"   -.zend')->getQuery();
$expected = '(-(".zend")) AND (("Thomas"))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

$s = inst()->addPhraseGuess('Thomas Rabaix +"symfony expert" -"zend framework" +javascript -.net')->getQuery();
$expected = '(+("symfony expert") AND +("javascript")) AND (-("zend framework") AND -(".net")) AND (("Thomas") OR ("Rabaix"))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

$s = inst()->addPhraseGuess('Thomas Rabaix +"sym"fony expert" -"zen-d framework" +javascript -.net')->getQuery();
$expected = '(+("sym") AND +("javascript")) AND (-("zen-d framework") AND -(".net")) AND (("Thomas") OR ("Rabaix") OR ("fony") OR ("expert"))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

$s = inst()->addPhraseFieldGuess('name', 'Thomas Rabaix +"sym"fony expert" -"zen-d framework" +javascript -.net')->getQuery();
$expected = 'name:(((+("sym") AND +("javascript")) AND (-("zen-d framework") AND -(".net")) AND (("Thomas") OR ("Rabaix") OR ("fony") OR ("expert"))))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');


$s = inst()->addPhraseFieldGuess('name', 'poulet -sel -chasseur')->getQuery();
$expected = 'name:(((-("sel") AND -("chasseur")) AND (("poulet"))))';
$t->cmp_ok($s, '===', $expected, '->addPhraseGuess()');

