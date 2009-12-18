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

    $this->createConfigFiles(sfLucene::getConfig($this->configuration));
  }


  protected function createConfigFiles($config)
  {
    
    $core_options = array();
    
    $fs = $this->getFilesystem();
    
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
            
            $schema_options[$field_name] = sprintf("<field name='%s' type='%s' stored='%s' multiValued='%s' required='%s' %s/>",
              $field_name,
              $field_option['type'],
              $field_option['stored'] ? 'true' : 'false',
              $field_option['multiValued'] ? 'true' : 'false',
              $field_option['required'] ? 'true' : 'false',
              !is_null($field_option['default']) ? 'default=\''.$field_option['default'].'\'' : ''
            );
            
            $copy_fields[$field_name] = sprintf("<copyField source='%s' dest='%s' />",
              $field_name, 
              'sfl_all'
            );
          }
        }
        
        $this->createSchemaFile($name, $config_dir, $schema_options, $copy_fields, $culture);
        $this->createSolrConfigFile($name, $config_dir);
        $this->createSolrTxtFiles($config_dir, $fs);
      }
    }
    
    $this->createSolrFile($base_solr_config_dir, $core_options);
  }
  
  public function createSchemaFile($name, $data_dir, $schema_options, $copy_fields, $culture)
  {
    // TODO : refactor this into a more clever way. maybe set tokeniser and filter into a yml file ...
    // or add the ability to include a file
    $culture_codes = array(
      'da' => 'Danish',
      'nl' => 'Dutch',
      'en' => 'English',
      'fi' => 'Finnish',
      'fr' => 'French',
      'de' => 'German',
      'it' => 'Italian',
      'kp' => 'Kp',
      'no' => 'Norwegian',
      'pt' => 'Portuguese',
      'ru' => 'Russian',
      'es' => 'Spanish',
      'se' => 'Swedish',
    );

    $snowball_language = array_key_exists($culture, $culture_codes) ? $culture_codes[$culture] : 'English';

    $field = implode("\n  ", $schema_options);
    $copy_fields = implode("\n  ", $copy_fields);
    
    $content =<<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<schema name="Index : $name - created by sfLucene - Solr version" version="1.1">
  <types>
   <fieldtype name="string"  class="solr.StrField" sortMissingLast="true" omitNorms="true"/>
   
   <fieldType name="text" class="solr.TextField" positionIncrementGap="100">
      <analyzer type="index">
        <!-- Documentation references : http://wiki.apache.org/solr/AnalyzersTokenizersTokenFilters -->
        
        <tokenizer class="solr.WhitespaceTokenizerFactory"
                   />
        <!-- in this example, we will only use synonyms at query time
        <filter class="solr.SynonymFilterFactory" synonyms="index_synonyms.txt" ignoreCase="true" expand="false"/>
        -->
        <!-- Case insensitive stop word removal.
          add enablePositionIncrements=true in both the index and query
          analyzers to leave a 'gap' for more accurate phrase queries.
        -->
        <filter class="solr.ISOLatin1AccentFilterFactory"
                />
              
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="stopwords.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" 
                generateWordParts="1" 
                generateNumberParts="1" 
                splitOnNumerics="0"
                catenateWords="1" 
                catenateNumbers="1" 
                catenateAll="0" 
                splitOnCaseChange="1"
                preserveOriginal="1"
                />

        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.SnowballPorterFilterFactory" 
                language="$snowball_language" 
                protected="protwords.txt"
                />
        <filter class="solr.RemoveDuplicatesTokenFilterFactory"
                />
      </analyzer>
      <analyzer type="query">
        <tokenizer class="solr.WhitespaceTokenizerFactory"
                />
        <filter class="solr.ISOLatin1AccentFilterFactory"
                />
        <filter class="solr.SynonymFilterFactory" 
                synonyms="synonyms.txt" 
                ignoreCase="true" 
                expand="true"
                />
        <filter class="solr.StopFilterFactory"
                ignoreCase="true"
                words="stopwords.txt"
                enablePositionIncrements="true"
                />
        <filter class="solr.WordDelimiterFilterFactory" 
                generateWordParts="1" 
                generateNumberParts="1" 
                splitOnNumerics="0"
                catenateWords="1" 
                catenateNumbers="1" 
                catenateAll="0" 
                splitOnCaseChange="1"
                preserveOriginal="1"
                />
        <filter class="solr.LowerCaseFilterFactory"/>
        <filter class="solr.SnowballPorterFilterFactory" 
                language="$snowball_language" 
                protected="protwords.txt"
                />
        <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
      </analyzer>
    </fieldType>

    <!-- field type definitions. The "name" attribute is
       just a label to be used by field definitions.  The "class"
       attribute and any other attributes determine the real
       behavior of the fieldType.
         Class names starting with "solr" refer to java classes in the
       org.apache.solr.analysis package.
    -->

    <!-- The StrField type is not analyzed, but indexed/stored verbatim.
       - StrField and TextField support an optional compressThreshold which
       limits compression (if enabled in the derived fields) to values which
       exceed a certain size (in characters).
    -->
    <fieldType name="string" class="solr.StrField" sortMissingLast="true" omitNorms="true"/>

    <!-- boolean type: "true" or "false" -->
    <fieldType name="boolean" class="solr.BoolField" sortMissingLast="true" omitNorms="true"/>
    <!--Binary data type. The data should be sent/retrieved in as Base64 encoded Strings -->
    <fieldtype name="binary" class="solr.BinaryField"/>

    <!-- The optional sortMissingLast and sortMissingFirst attributes are
         currently supported on types that are sorted internally as strings.
	       This includes "string","boolean","sint","slong","sfloat","sdouble","pdate"
       - If sortMissingLast="true", then a sort on this field will cause documents
         without the field to come after documents with the field,
         regardless of the requested sort order (asc or desc).
       - If sortMissingFirst="true", then a sort on this field will cause documents
         without the field to come before documents with the field,
         regardless of the requested sort order.
       - If sortMissingLast="false" and sortMissingFirst="false" (the default),
         then default lucene sorting will be used which places docs without the
         field first in an ascending sort and last in a descending sort.
    -->

    <!--
      Default numeric field types. For faster range queries, consider the tint/tfloat/tlong/tdouble types.
    -->
    <fieldType name="int" class="solr.TrieIntField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="float" class="solr.TrieFloatField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="long" class="solr.TrieLongField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="double" class="solr.TrieDoubleField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>

    <!--
     Numeric field types that index each value at various levels of precision
     to accelerate range queries when the number of values between the range
     endpoints is large. See the javadoc for NumericRangeQuery for internal
     implementation details.

     Smaller precisionStep values (specified in bits) will lead to more tokens
     indexed per value, slightly larger index size, and faster range queries.
     A precisionStep of 0 disables indexing at different precision levels.
    -->
    <fieldType name="tint" class="solr.TrieIntField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="tfloat" class="solr.TrieFloatField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="tlong" class="solr.TrieLongField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
    <fieldType name="tdouble" class="solr.TrieDoubleField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>

    <!-- The format for this date field is of the form 1995-12-31T23:59:59Z, and
         is a more restricted form of the canonical representation of dateTime
         http://www.w3.org/TR/xmlschema-2/#dateTime
         The trailing "Z" designates UTC time and is mandatory.
         Optional fractional seconds are allowed: 1995-12-31T23:59:59.999Z
         All other components are mandatory.

         Expressions can also be used to denote calculations that should be
         performed relative to "NOW" to determine the value, ie...

               NOW/HOUR
                  ... Round to the start of the current hour
               NOW-1DAY
                  ... Exactly 1 day prior to now
               NOW/DAY+6MONTHS+3DAYS
                  ... 6 months and 3 days in the future from the start of
                      the current day

         Consult the DateField javadocs for more information.

         Note: For faster range queries, consider the tdate type
      -->
    <fieldType name="date" class="solr.TrieDateField" omitNorms="true" precisionStep="0" positionIncrementGap="0"/>

    <!-- A Trie based date field for faster date range queries and date faceting. -->
    <fieldType name="tdate" class="solr.TrieDateField" omitNorms="true" precisionStep="6" positionIncrementGap="0"/>


    <!--
      Note:
      These should only be used for compatibility with existing indexes (created with older Solr versions)
      or if "sortMissingFirst" or "sortMissingLast" functionality is needed. Use Trie based fields instead.

      Plain numeric field types that store and index the text
      value verbatim (and hence don't support range queries, since the
      lexicographic ordering isn't equal to the numeric ordering)
    -->
    <fieldType name="pint" class="solr.IntField" omitNorms="true"/>
    <fieldType name="plong" class="solr.LongField" omitNorms="true"/>
    <fieldType name="pfloat" class="solr.FloatField" omitNorms="true"/>
    <fieldType name="pdouble" class="solr.DoubleField" omitNorms="true"/>
    <fieldType name="pdate" class="solr.DateField" sortMissingLast="true" omitNorms="true"/>


    <!--
      Note:
      These should only be used for compatibility with existing indexes (created with older Solr versions)
      or if "sortMissingFirst" or "sortMissingLast" functionality is needed. Use Trie based fields instead.

      Numeric field types that manipulate the value into
      a string value that isn't human-readable in its internal form,
      but with a lexicographic ordering the same as the numeric ordering,
      so that range queries work correctly.
    -->
    <fieldType name="sint" class="solr.SortableIntField" sortMissingLast="true" omitNorms="true"/>
    <fieldType name="slong" class="solr.SortableLongField" sortMissingLast="true" omitNorms="true"/>
    <fieldType name="sfloat" class="solr.SortableFloatField" sortMissingLast="true" omitNorms="true"/>
    <fieldType name="sdouble" class="solr.SortableDoubleField" sortMissingLast="true" omitNorms="true"/>


    <!-- The "RandomSortField" is not used to store or search any
         data.  You can declare fields of this type it in your schema
         to generate pseudo-random orderings of your docs for sorting
         purposes.  The ordering is generated based on the field name
         and the version of the index, As long as the index version
         remains unchanged, and the same field name is reused,
         the ordering of the docs will be consistent.
         If you want different psuedo-random orderings of documents,
         for the same version of the index, use a dynamicField and
         change the name
     -->
    <fieldType name="random" class="solr.RandomSortField" indexed="true" />

    <!-- solr.TextField allows the specification of custom text analyzers
         specified as a tokenizer and a list of token filters. Different
         analyzers may be specified for indexing and querying.

         The optional positionIncrementGap puts space between multiple fields of
         this type on the same document, with the purpose of preventing false phrase
         matching across fields.

         For more info on customizing your analyzer chain, please see
         http://wiki.apache.org/solr/AnalyzersTokenizersTokenFilters
     -->

    <!-- One can also specify an existing Analyzer class that has a
         default constructor via the class attribute on the analyzer element
    <fieldType name="text_greek" class="solr.TextField">
      <analyzer class="org.apache.lucene.analysis.el.GreekAnalyzer"/>
    </fieldType>
    -->

    <!-- A text field that only splits on whitespace for exact matching of words -->
    <fieldType name="text_ws" class="solr.TextField" positionIncrementGap="100">
      <analyzer>
        <tokenizer class="solr.WhitespaceTokenizerFactory"/>
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
    
  <!-- WARNING: this <indexDefaults> section only provides defaults for index writers
       in general. See also the <mainIndex> section after that when changing parameters
       for Solr's main Lucene index. -->
  <indexDefaults>
   <!-- Values here affect all index writers and act as a default unless overridden. -->
    <useCompoundFile>false</useCompoundFile>

    <mergeFactor>10</mergeFactor>
    <!-- If both ramBufferSizeMB and maxBufferedDocs is set, then Lucene will flush
     based on whichever limit is hit first.  -->
    <!--<maxBufferedDocs>1000</maxBufferedDocs>-->

    <!-- Sets the amount of RAM that may be used by Lucene indexing
      for buffering added documents and deletions before they are
      flushed to the Directory.  -->
    <ramBufferSizeMB>32</ramBufferSizeMB>
    <!-- <maxMergeDocs>2147483647</maxMergeDocs> -->
    <maxFieldLength>10000</maxFieldLength>
    <writeLockTimeout>1000</writeLockTimeout>
    <commitLockTimeout>10000</commitLockTimeout>

    <!--
     Expert: Turn on Lucene's auto commit capability.  This causes intermediate
     segment flushes to write a new lucene index descriptor, enabling it to be
     opened by an external IndexReader.  This can greatly slow down indexing
     speed.  NOTE: Despite the name, this value does not have any relation to
     Solr's autoCommit functionality
     -->
    <!--<luceneAutoCommit>false</luceneAutoCommit>-->

    <!--
     Expert: The Merge Policy in Lucene controls how merging is handled by
     Lucene.  The default in 2.3 is the LogByteSizeMergePolicy, previous
     versions used LogDocMergePolicy.

     LogByteSizeMergePolicy chooses segments to merge based on their size.  The
     Lucene 2.2 default, LogDocMergePolicy chose when to merge based on number
     of documents

     Other implementations of MergePolicy must have a no-argument constructor
     -->
    <!--<mergePolicy class="org.apache.lucene.index.LogByteSizeMergePolicy"/>-->

    <!--
     Expert:
     The Merge Scheduler in Lucene controls how merges are performed.  The
     ConcurrentMergeScheduler (Lucene 2.3 default) can perform merges in the
     background using separate threads.  The SerialMergeScheduler (Lucene 2.2
     default) does not.
     -->
    <!--<mergeScheduler class="org.apache.lucene.index.ConcurrentMergeScheduler"/>-->

	  
    <!--
      This option specifies which Lucene LockFactory implementation to use.
      
      single = SingleInstanceLockFactory - suggested for a read-only index
               or when there is no possibility of another process trying
               to modify the index.
      native = NativeFSLockFactory  - uses OS native file locking
      simple = SimpleFSLockFactory  - uses a plain file for locking

      (For backwards compatibility with Solr 1.2, 'simple' is the default
       if not specified.)
    -->
    <lockType>single</lockType>
    <!--
     Expert:
    Controls how often Lucene loads terms into memory -->
    <!--<termIndexInterval>256</termIndexInterval>-->
  </indexDefaults>
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