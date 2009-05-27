<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfLuceneBaseTask.class.php');

/**
 * Task that initializes all the configuration files.
 *
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @package sfLucenePlugin
 * @subpackage Tasks
 * @version SVN: $Id: sfLuceneInitializeTask.class.php 12678 2008-11-06 09:23:10Z rande $
 */

class sfLuceneCreateSolrTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name')
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
    ));

    $this->aliases = array('lucene-create-solr-config');
    $this->namespace = 'lucene';
    $this->name = 'create-solr-config';
    $this->briefDescription = 'Initializes solar multicore config file';

    $this->detailedDescription = <<<EOF
The [lucene:create-solr-config|INFO] initializes the configuration files for solr.

EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->checkAppExists($app);
    $this->standardBootstrap($app, $options['env']);

    $this->createConfigFiles(sfLucene::getConfig());

  }


  protected function createConfigFiles($config)
  {
    
    $core_options = array();
    
    $fs = new sfFilesystem($this->dispatcher, $this->formatter);
    
    $base_solr_config_dir = sfConfig::get('sf_config_dir').'/solr';
    
    foreach($config as $name_main_index => $options)
    {

      
      foreach($options['index']['cultures'] as $culture)
      {
        $schema_options = array();
        $copy_fields    = array();
        
        $name       = $name_main_index.'_'.$culture;
        $data_dir   = sfConfig::get('sf_data_dir').'/solr_index/'.$name;
        $config_dir = $base_solr_config_dir.'/'.$name.'/conf';

        $this->logSection('lucene', 'init '.$name);
      
        
        // create default folder
        $fs->mkdirs($data_dir);
        $fs->mkdirs($config_dir);
        
        // options required in solr.xml file
        $core_options[] = sprintf("<core name='%s' instanceDir='%s'></core>", 
          $name, 
          $name, 
          $data_dir 
        );
        
        // options required in $name => schema.xml
        foreach($options['models'] as $model_name =>$model)
        {
          
          $this->logSection('lucene', '  > model name : '.$model_name);
          
          foreach($model['fields'] as $field_name => $field_option)
          {
            $schema_options[$field_name] = sprintf("<field name='%s' type='%s' stored='%s' multiValued='%s' required='%s' />",
              $field_name,
              $field_option['type'],
              $field_option['stored'] ? 'true' : 'false',
              $field_option['multiValued'] ? 'true' : 'false',
              $field_option['required'] ? 'true' : 'false'
            );
            
            $copy_fields[$field_name] = sprintf("<copyField source='%s' dest='%s' />",
              $field_name, 
              'sfl_all'
            );
          }
        }
        
        $this->createSchemaFile($name, $config_dir, $schema_options, $copy_fields);
        $this->createSolrConfigFile($name, $config_dir);
        $this->createSolrTxtFiles($config_dir, $fs);
      }
    }
    
    $this->createSolrFile($base_solr_config_dir, $core_options);
  }
  
  public function createSchemaFile($name, $data_dir, $schema_options, $copy_fields)
  {
    
    $field = implode("\n  ", $schema_options);
    $copy_fields = implode("\n  ", $copy_fields);
    
    $content =<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<schema name="Index : $name - created by sfLucene - Solr version" version="1.1">
  <types>
   <fieldtype name="string"  class="solr.StrField" sortMissingLast="true" omitNorms="true"/>
   
   <fieldType name="text" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <!-- in this example, we will only use synonyms at query time
        <filter class="solr.SynonymFilterFactory" synonyms="index_synonyms.txt" ignoreCase="true" expand="false"/>
        -->
        <!-- Case insensitive stop word removal.
          add enablePositionIncrements=true in both the index and query
          analyzers to leave a 'gap' for more accurate phrase queries.
        -->
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="stopwords.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="1" catenateNumbers="1" catenateAll="0" splitOnCaseChange="1"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.SnowballPorterFilterFactory" language="English" protected="protwords.txt"/>
        <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
        <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="stopwords.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1" catenateWords="0" catenateNumbers="0" catenateAll="0" splitOnCaseChange="1"/>
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.SnowballPorterFilterFactory" language="English" protected="protwords.txt"/>
        <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
      </analyzer>
    </fieldType>
   
  </types>

 <fields>   
  <!-- general, field use by sfLucenePlugin -->
  <field name="sfl_guid"      type="string"   indexed="true"  stored="true"  multiValued="false" required="true"/>
  <field name="sfl_title"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_description"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_type"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_uri"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_category"      type="text"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_categories_cache"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_model"      type="string"   indexed="true"  stored="true"  multiValued="false" required="false"/>
  <field name="sfl_all"      type="text"   indexed="true"  stored="false"  multiValued="true" required="false"/>
  
  <!-- merged model information into one document -->
  <field name="$name"   type="string"   indexed="true"  stored="true"  multiValued="false" />
  $field
 </fields>

 <!-- field to use to determine and enforce document uniqueness. -->
 <uniqueKey>sfl_guid</uniqueKey>

 <!-- field for the QueryParser to use when an explicit fieldname is absent -->
 <defaultSearchField>sfl_all</defaultSearchField>

 <!-- 
      copyField commands copy one field to another at the time a document
      is added to the index.  It's used either to index the same field differently,
      or to add multiple fields to the same field for easier/faster searching.  
 -->
  <solrQueryParser defaultOperator="OR"/>
 
  $copy_fields
 
</schema>
XML;

    $this->logSection('lucene', '  + write schema.xml in '.$data_dir);
    file_put_contents($data_dir.'/schema.xml', $content);
  }
  
  public function createSolrConfigFile($name, $data_dir)
  {
    
    $content =<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<!--
 This is a stripped down config file used for a simple example...  
 It is *not* a good example to work from. 
-->
<config>
  <updateHandler class="solr.DirectUpdateHandler2" />

  <requestDispatcher handleSelect="true" >
    <requestParsers enableRemoteStreaming="false" multipartUploadLimitInKB="2048" />
  </requestDispatcher>
  
  <requestHandler name="standard" class="solr.StandardRequestHandler" default="true" />
  <requestHandler name="/update" class="solr.XmlUpdateRequestHandler" />
  <requestHandler name="/admin/" class="org.apache.solr.handler.admin.AdminHandlers" />
      
  <!-- config for the admin interface --> 
  <admin>
    <defaultQuery>solr</defaultQuery>
    <pingQuery>q=solr&amp;version=2.0&amp;start=0&amp;rows=0</pingQuery>
  </admin>

  <dataDir>\${solr.data.dir}/$name</dataDir>
</config>
XML;

    $this->logSection('lucene', '  + write solrconfig.xml in '.$data_dir);
    file_put_contents($data_dir.'/solrconfig.xml', $content);
  }
  
  public function createSolrFile($base_solr_config_dir, $core_options)
  {

    $core = implode("\n    ", $core_options);
    
    $content =<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<solr persistent="false">
  <cores adminPath="/admin/cores">
    $core
  </cores>
</solr>
XML;

    $this->logSection('lucene', '  + write solr.xml in '.$base_solr_config_dir);
    
    file_put_contents($base_solr_config_dir.'/solr.xml', $content);
  }
  
  public function createSolrTxtFiles($config_dir, sfFilesystem $fs)
  {
    $files = array(
      'synonyms.txt',
      'spellings.txt',
      'protwords.txt',
      'stopwords.txt'
    );

    $solr_example_path = sfConfig::get('sf_plugins_dir').'/sfLucenePlugin/lib/vendor/Solr/example/solr/conf';
    
    foreach($files as $file)
    {
      $fs->copy($solr_example_path.'/'.$file, $config_dir.'/'.$file);
    }
    
  }
}