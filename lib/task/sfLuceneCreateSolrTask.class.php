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

  protected function generateFieldXml($name, $options, $is_dynamic=false)
  {
    $extraOptions = array();
    if (!is_null($options['omitNorms']))
    {
      $extraOptions[] = sprintf('omitNorms="%s"', $options['omitNorms'] ? 'true' : 'false');
    }
    if (!is_null($options['default']))
    {
      $extraOptions[] = sprintf('default="%s"', $options['default']);
    }

    return sprintf('<%s name="%s" type="%s" stored="%s" multiValued="%s" required="%s"%s/>',
      $is_dynamic ? 'dynamicField' : 'field',
      $name,
      $options['type'],
      $options['stored'] ? 'true' : 'false',
      $options['multiValued'] ? 'true' : 'false',
      $options['required'] ? 'true' : 'false',
      (sizeof($extraOptions) > 0) ? ' ' . implode(' ', $extraOptions) : ''
    );
  }

  protected function generateCopyFieldXml($source, $dest)
  {
    return sprintf('<copyField source="%s" dest="%s"/>', $source, $dest);
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
        $dynamic_fields = array();
        
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
        foreach($options['models'] as $model_name => $model)
        {
          $this->logSection('lucene', '  > model name : '.$model_name);
          
          foreach($model['fields'] as $field_name => $field_option)
          {
            $schema_options[$field_name] = $this->generateFieldXml($field_name, $field_option);
            $copy_fields[$field_name] = $this->generateCopyFieldXml($field_name, 'sfl_all');
          }

          /**
           * Adds localsolr fields :
           *
           *  - lat :           used to store the latitude. may be customized in search.yml
           *  - lng :           used to store the longitude. may be customized in search.yml
           *  - geo_distance :  used for distributed searching
           *  - _local* :       used internally by solr
           *
           * lat & lng fields will have a 'tdouble' type. It WILL override the type you defined in search.yml.
           *  
           */
          if ($options['index']['localsolr']['enabled'])
          {
            // lat field
            $latitude_field = $options['index']['localsolr']['latitude_field'];
            $schema_options[$latitude_field] = $this->generateFieldXml($latitude_field, array(
              'type' => 'tdouble',
              'indexed' => true,
              'stored' => true,
              'default' => 0
            ));

            // lng field
            $longitude_field = $options['index']['localsolr']['longitude_field'];
            $schema_options[$longitude_field] = $this->generateFieldXml($longitude_field, array(
              'type' => 'tdouble',
              'indexed' => true,
              'stored' => true,
              'default' => 0
            ));

            // geo_distance field
            $schema_options['geo_distance'] = $this->generateFieldXml('geo_distance', array(
              'type' => 'sdouble'
            ));

            // _local* fields
            $dynamic_fields['_local*'] = $this->generateFieldXml('_local*', array(
              'type' => 'tdouble',
              'indexed' => true,
              'stored' => true
            ), true);
          }
        }

        $this->createSchemaFile($name, $config_dir, $schema_options, $copy_fields, $dynamic_fields, $culture, $config_dir);
        $this->createSolrConfigFile($name, $config_dir, $options['index']);
        $this->createSolrTxtFiles($config_dir);
      }
    }
    
    $this->createSolrFile($base_solr_config_dir, $core_options);
  }
  
  public function createSchemaFile($name, $data_dir, $schema_options, $copy_fields, $dynamic_fields, $culture, $config_dir)
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
    
    $constants = array(
      '%%COPY_FIELDS%%'       => implode("\n  ", $copy_fields),
      '%%DYNAMIC_FIELDS%%'       => implode("\n  ", $dynamic_fields),
      '%%INDEX_NAME%%'        => $name,
      '%%MERGE_CONFIG%%'      => implode("\n  ", $schema_options),
      '%%SNOWBALL_LANGUAGE%%' => $snowball_language
    );
    
    $solr_data_path = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/data/skeleton/project/config';

    $this->getFilesystem()->copy($solr_data_path.'/schema.xml', $config_dir.'/base_schema.xml', array(
      'override' => false
    ));

    $contents = file_get_contents($config_dir.'/base_schema.xml');
    
    $this->logSection('lucene', '  + write schema.xml in '.$data_dir);
    
    file_put_contents($data_dir.'/schema.xml', strtr($contents, $constants));
  }

  protected function generateSearchComponentXml($name, $class, $arguments=array())
  {
    if (sizeof($arguments) > 0)
    {
      $tmp = array();
      foreach($arguments as $argument)
      {
        $tmp[] = $this->generateSolrArgumentXml($argument);
      }

      return sprintf("<searchComponent name=\"%s\" class=\"%s\">\n%s\n</searchComponent>", $name, $class, implode("\n", $tmp));
    }

    return sprintf('<searchComponent name="%s" class="%s"/>', $name, $class);
  }

  protected function generateRequestProcessorFieldXml($name, $type, $value)
  {
    return sprintf('<%s name="%s">%s</%s>', $type, $name, $value, $type);
  }

  protected function generateRequestProcessorXml($class, $fields=array())
  {
    if (sizeof($fields) > 0)
    {
      $tmp = array();
      foreach($fields as $field)
      {
        $tmp[] = $this->generateSolrArgumentXml($field);
      }

      return sprintf("<processor class=\"%s\">\n%s\n</processor>", $class, implode("\n", $tmp));
    }

    return sprintf('<processor class="%s"/>', $class);
  }

  protected function generateRequestHandlerXml($name, $class, $arguments=array())
  {
    if (sizeof($arguments) > 0)
    {
      $tmp = array();
      foreach($arguments as $argument)
      {
        $tmp[] = $this->generateSolrArgumentXml($argument);
      }

      return sprintf("<requestHandler name=\"%s\" class=\"%s\">\n%s\n</requestHandler>", $name, $class, implode("\n", $tmp));
    }
    
    return sprintf("<requestHandler name=\"%s\" class=\"%s\"/>", $name, $class);
  }

  protected function generateSolrArgumentXml($argument)
  {
    switch($argument['type'])
    {
      case 'arr':
        $tmp = array();
        foreach($argument['value'] as $subargument)
        {
          $tmp[] = $this->generateSolrArgumentXml($subargument);
        }

        return sprintf("<arr name=\"%s\">\n%s\n</arr>", $argument['name'], implode("\n", $tmp));
        break;

      default:
        return sprintf("<%s%s>%s</%s>", $argument['type'], (isset($argument['name']) ? sprintf(' name="%s"', $argument['name']) : '') ,$argument['value'], $argument['type']);
        break;
    }
  }

  /**
   * Generates solrconfig.xml file
   *
   * @param   $name
   * @param   $data_dir
   * @param   array $options
   * @return  void
   */
  public function createSolrConfigFile($name, $data_dir, $options=array())
  {
    $search_components  = array();
    $request_processors = array();
    $request_handlers   = array();

    if ($options['localsolr']['enabled'])
    {
      // add search components
      $search_components[] = $this->generateSearchComponentXml('localsolr', 'com.pjaol.search.solr.component.LocalSolrQueryComponent', array(
        array('type' => 'str', 'name' => 'latField', 'value' => $options['localsolr']['latitude_field']),
        array('type' => 'str', 'name' => 'lngField', 'value' => $options['localsolr']['longitude_field']),
      ));
      $search_components[] = $this->generateSearchComponentXml('geofacet', 'com.pjaol.search.solr.component.LocalSolrFacetComponent');

      // add request processors
      $request_processors[] = $this->generateRequestProcessorXml('com.pjaol.search.solr.update.LocalUpdateProcessorFactory', array(
        array('type' => 'str', 'name' => 'latField', 'value' => $options['localsolr']['latitude_field']),
        array('type' => 'str', 'name' => 'lngField', 'value' => $options['localsolr']['longitude_field']),
        array('type' => 'int', 'name' => 'startTier', 'value' => 9),
        array('type' => 'int', 'name' => 'endTier', 'value' => 17)
      ));
      $request_processors[] = $this->generateRequestProcessorXml('solr.RunUpdateProcessorFactory');
      $request_processors[] = $this->generateRequestProcessorXml('solr.LogUpdateProcessorFactory');

      // add request handlers
      $request_handlers[] = $this->generateRequestHandlerXml('geo', 'org.apache.solr.handler.component.SearchHandler', array(
        array('type' => 'arr', 'name' => 'components', 'value' => array(
          array('type' => 'str', 'value' => 'localsolr'),
          array('type' => 'str', 'value' => 'geofacet'),
          array('type' => 'str', 'value' => 'mlt'),
          array('type' => 'str', 'value' => 'highlight'),
          array('type' => 'str', 'value' => 'debug')
        ))
      ));
    }

    $constants = array(
      '%%INDEX_NAME%%' => $name,
      '%%REQUEST_PROCESSORS%%' => (sizeof($request_processors)>0) ? sprintf("<updateRequestProcessorChain>\n%s\n</updateRequestProcessorChain>", implode("\n", $request_processors)) : '',
      '%%SEARCH_COMPONENTS%%' => implode("\n", $search_components),
      '%%REQUEST_HANDLERS%%' => implode("\n", $request_handlers)
    );

    $solr_data_path = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/data/skeleton/project/config';

    $this->getFilesystem()->copy($solr_data_path.'/solrconfig.xml', $data_dir.'/base_solrconfig.xml', array(
      'override' => false
    ));

    $contents = file_get_contents($data_dir.'/base_solrconfig.xml');

    $this->logSection('lucene', '  + write solrconfig.xml in '.$data_dir);
    file_put_contents($data_dir.'/solrconfig.xml', strtr($contents, $constants));
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
  
  public function createSolrTxtFiles($config_dir)
  {
    $files = array(
      'synonyms.txt',
      'spellings.txt',
      'protwords.txt',
      'stopwords.txt'
    );

    $solr_example_path = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/lib/vendor/Solr/example/solr/conf';
    
    foreach($files as $file)
    {
      $this->getFilesystem()->copy($solr_example_path.'/'.$file, $config_dir.'/'.$file, array(
        'override' => false
      ));
    }
    
  }
}