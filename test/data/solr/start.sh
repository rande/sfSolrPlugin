#!/bin/sh

# start solr

PLUGIN_PATH=/Users/thomas/Projects/swPluginDemo/trunk/plugins/sfLucenePlugin
EXAMPLE_PATH=${PLUGIN_PATH}/lib/vendor/Solr/example
SOLR_PATH=${PLUGIN_PATH}/test/data/solr
URL=http://localhost:8983/solr

cd $EXAMPLE_PATH;

java -Dsolr.solr.home=${SOLR_PATH}/config/ -Dsolr.data.dir=${SOLR_PATH}/index/ -jar start.jar