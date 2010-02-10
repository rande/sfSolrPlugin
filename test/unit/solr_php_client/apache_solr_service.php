<?php

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(null, limeade_output::get());

$service = new sfLuceneService('127.0.0.1', '8983');

if(!$service->ping())
{
//  $t->fail('Solr is not running');  die();
}

$t->is($service->getHost(), '127.0.0.1', '->getHost() ok');
$t->is($service->getPort(), '8983', '->getPort() ok');
$t->is($service->getPath(), '/solr/', '->getPath() ok');

$service->setPath('/solr/index_fr/');
$t->is($service->getPath(), '/solr/index_fr/', '->setPath() ok');

try
{
  $response = $service->deleteByQuery('non_existent_field:asd');
  $t->fail('::deleteByQuery refers to a non existent field');
}
catch(Exception $e)
{
  $t->pass('::deleteByQuery raise an error on non existent field');
}

$t->diag("search for rande, limit:2, offset:0");
$response = $service->search('rande', 0, 2);
$t->ok($response instanceof sfLuceneDocument, '::search returns Apache_Solr_Response object');
$t->cmp_ok($response->getHttpStatusMessage(), '===', 'OK', '::getHttpStatusMessage return OK');
$t->cmp_ok($response->getHttpStatus(), '===', '200', '::getHttpStatus return code 200');
$t->cmp_ok($response->response->numFound, '===', 3, '->response->numFound return 3 entries');
$t->cmp_ok(count($response->response->docs), '===', 2, '->response->numFound return 2 entries');
$t->ok($response->response->docs[0] instanceof sfLuceneDocument, '->response->docs[0] return an instance sfLuceneDocument');
$t->cmp_ok($response->response->docs[0]->sfl_guid, '===', 'GUID_1', '->response->docs[0]->sfl_guid ok');
$t->cmp_ok($response->response->docs[1]->sfl_guid, '===', 'GUID_2', '->response->docs[1]->sfl_guid ok');

//
$t->diag("search for rande, limit:1, offset:2");
$response = $service->search('rande', 2, 1);
$t->ok($response instanceof sfLuceneDocument, '::search returns Apache_Solr_Response object');
$t->cmp_ok($response->getHttpStatusMessage(), '===', 'OK', '::getHttpStatusMessage return OK');
$t->cmp_ok($response->getHttpStatus(), '===', '200', '::getHttpStatus return code 200');
$t->cmp_ok($response->response->numFound,  '===', 3, '->response->numFound return 3 entries');
$t->cmp_ok(count($response->response->docs),  '===', 1, '->response->numFound return 2 entries');
$t->ok($response->response->docs[0] instanceof sfLuceneDocument, '->response->docs[0] return an instance sfLuceneDocument');
$t->cmp_ok($response->response->docs[0]->sfl_guid, '===', 'GUID_3', '->response->docs[0]->sfl_guid ok');

