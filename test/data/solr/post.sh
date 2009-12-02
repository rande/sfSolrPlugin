#!/bin/sh

PLUGIN_PATH=/Users/thomas/Projects/swPluginDemo/trunk/plugins/sfLucenePlugin
EXAMPLE_PATH=${PLUGIN_PATH}/lib/vendor/Solr/example
SOLR_PATH=${PLUGIN_PATH}/test/data/solr
URL=http://localhost:8983/solr

cd $EXAMPLE_PATH;

echo "Send profils.xml file to index_fr and commit changes"
curl $URL/index_fr/update --data-binary @${SOLR_PATH}/profils.xml -H 'Content-type:text/xml; charset=utf-8'
curl $URL/index_fr/update --data-binary '<commit/>' -H 'Content-type:text/xml; charset=utf-8'

echo "Send profils.xml file to index_en file and commit changes"
curl $URL/index_en/update --data-binary @${SOLR_PATH}/profils.xml -H 'Content-type:text/xml; charset=utf-8'
curl $URL/index_en/update --data-binary '<commit/>' -H 'Content-type:text/xml; charset=utf-8'

