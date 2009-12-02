<?php

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(3, limeade_output::get());


$val = "this is my+ es\caped ?*string";
$escaped = Apache_Solr_Service::escape($val);
$expected = 'this is my\\+ es\\\\caped \\?\\*string';

$t->cmp_ok($escaped, '===', $expected, "::escape  ok");


$val = "this is \"my escaped\" phrase";
$escaped = Apache_Solr_Service::escapePhrase($val);
$expected = 'this is \\"my escaped\\" phrase';

$t->cmp_ok($escaped, '===', $expected, "::escapePhrase  ok");

$escaped = Apache_Solr_Service::phrase($val);
$expected = '"this is \\"my escaped\\" phrase"';

$t->cmp_ok($escaped, '===', $expected, "::phrase  ok");

