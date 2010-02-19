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
            
            $schema_options[$field_name] = sprintf("<field name='%s' type='%s' stored='%s' multiValued='%s' required='%s' %s %s/>",
              $field_name,
              $field_option['type'],
              $field_option['stored'] ? 'true' : 'false',
              $field_option['multiValued'] ? 'true' : 'false',
              $field_option['required'] ? 'true' : 'false',
              !is_null($field_option['omitNorms']) ? 'omitNorms=\''.($field_option['omitNorms'] ? 'true' : 'false').'\'' : '',
              !is_null($field_option['default']) ? 'default=\''.$field_option['default'].'\'' : ''
            );
            
            $copy_fields[$field_name] = sprintf("<copyField source='%s' dest='%s' />",
              $field_name, 
              'sfl_all'
            );
          }
        }
        
        $this->createSchemaFile($name, $config_dir, $schema_options, $copy_fields, $culture, $config_dir);
        $this->createSolrConfigFile($name, $config_dir);
        $this->createSolrTxtFiles($config_dir);
      }
    }
    
    $this->createSolrFile($base_solr_config_dir, $core_options);
  }
  
  public function createSchemaFile($name, $data_dir, $schema_options, $copy_fields, $culture, $config_dir)
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
      '%%INDEX_NAME%%'        => $name,
      '%%MERGE_CONFIG%%'      => implode("\n  ", $schema_options),
      '%%SNOWBALL_LANGUAGE%%' => $snowball_language
    );
    
    $solr_data_path = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/data/skeleton/project/config';

    $this->getFilesystem()->copy($solr_data_path.'/schema.xml', $config_dir.'/base_schema.xml'.$file, array(
      'override' => false
    ));

    $contents = file_get_contents($config_dir.'/base_schema.xml');
    
    $this->logSection('lucene', '  + write schema.xml in '.$data_dir);
    
    file_put_contents($data_dir.'/schema.xml', strtr($contents, $constants));
  }
  
  public function createSolrConfigFile($name, $data_dir)
  {
    
    $constants = array(
      '%%INDEX_NAME%%' => $name,
    );

    $solr_data_path = sfConfig::get('sf_plugins_dir').'/sfSolrPlugin/data/skeleton/project/config';

    $this->getFilesystem()->copy($solr_data_path.'/solrconfig.xml', $data_dir.'/base_solrconfig.xml'.$file, array(
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