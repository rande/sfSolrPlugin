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
* Start solr web server, use Jetty WebServer
*
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id: sfLuceneInitializeTask.class.php 12678 2008-11-06 09:23:10Z rande $
*/

class sfLuceneServiceTask extends sfLuceneBaseTask
{
  protected
    $nohup = false,
    $java  = false;
  
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'The action name')
    ));

    $results =  $output = false;
    exec('which java', $output, $results);
    $java = $results == 0 ? $output[0] : false;

    $results = $output = false;
    exec('which nohup', $output, $results);
    $nohup = $results == 0 ? $output[0] : false;

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('java', null, sfCommandOption::PARAMETER_REQUIRED, 'the java binary', $java),
      new sfCommandOption('nohup', null, sfCommandOption::PARAMETER_REQUIRED, 'the nohup binary', $nohup),
      new sfCommandOption('Xmx', null, sfCommandOption::PARAMETER_REQUIRED, 'maximum java heap size', '512m'),
      new sfCommandOption('Xms', null, sfCommandOption::PARAMETER_REQUIRED, 'initial java heap size', '256m'),
    ));

    $this->aliases = array('lucene-service');
    $this->namespace = 'lucene';
    $this->name = 'service';
    $this->briefDescription = 'start or stop the Solr server (only *nix plateform)';

    $this->detailedDescription = <<<EOF
The [lucene:service|INFO] start or stop the Solr server

The command use the Jetty WebServer

    [./symfony lucene:service myapp start|INFO]   start the Solr server
    [./symfony lucene:service myapp stop|INFO]    stop the Solr server
    [./symfony lucene:service myapp restart|INFO] restart the Solr server

You can also retrieve the status by calling :

    [./symfony lucene:service myapp status|INFO] restart the Solr server

Note about Jetty :

  Jetty is an open-source project providing a HTTP server, HTTP client and
  javax.servlet container. These 100% java components are full-featured,
  standards based, small foot print, embeddable, asynchronous and enterprise
  scalable. Jetty is dual licensed under the Apache Licence 2.0 and/or the
  Eclipse Public License 1.0. Jetty is free for commercial use and distribution
  under the terms of either of those licenses.

  more information about Jetty : http://www.mortbay.org/jetty/

EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $options['env'];

    if(!is_executable($options['java'] ))
    {

      throw new sfException('Please provide a valid java executable file');
    }

    $this->java = $options['java'];
    
    if(!is_executable($options['nohup'] ))
    {

      throw new sfException('Please provide a valid nohup executable file');
    }
    
    $this->nohup = $options['nohup'];


    $action = $arguments['action'];

    switch($action)
    {
      case 'start':
       $this->start($app, $env, $options);
       break;

     case 'stop':
       $this->stop($app, $env, $options);

       break;

     case 'restart':
       $this->stop($app, $env, $options);
       $this->start($app, $env, $options);
       break;

     case 'status':
       $this->status($app, $env, $options);
       break;
    }
  }

  public function isRunning($app, $env, $options = array())
  {
    
    return @file_exists($this->getPidFile($app, $env));
  }

  public function start($app, $env, $options = array())
  {
    if($this->isRunning($app, $env))
    {

      throw new sfException('Server is running, cannot start (pid file : '.$this->getPidFile($app, $env).')');
    }
    
    $instances = sfLucene::getAllInstances($this->configuration);
    
    if(count($instances) == 0)
    {
      
      throw new sfException('There is no Solr instance for the current application');
    }
    
    $host     = $instances[0]->getParameter('host');
    $port     = $instances[0]->getParameter('port');
    $base_url = $instances[0]->getParameter('base_url');
    
    // start the jetty built in server
    $command = sprintf('cd %s/plugins/sfSolrPlugin/lib/vendor/Solr/example; %s %s -Xmx%s -Xms%s -Dsolr.solr.home=%s/config/solr/ -Dsolr.data.dir=%s/data/solr_index -Dsolr.lib.dir=%s/plugins/sfSolrPlugin/lib/vendor/Solr/example/solr/lib -Djetty.port=%s -Djetty.logs=%s -jar start.jar > %s/solr_server_%s_%s.log 2>&1 & echo $!',
      sfConfig::get('sf_root_dir'),
      $this->nohup,
      $this->java,
      $options['Xmx'],
      $options['Xms'],
      sfConfig::get('sf_root_dir'),
      sfConfig::get('sf_root_dir'),
      sfConfig::get('sf_root_dir'),
      $port,
      sfConfig::get('sf_root_dir').'/log',
      sfConfig::get('sf_root_dir').'/log',
      $app,
      $env
    );

    $this->logSection('exec ', $command);
    exec($command ,$op);

    if(method_exists($this->getFilesystem(), 'execute')) // sf1.3 or greater
    {
      $this->getFilesystem()->execute(sprintf('cd %s',
        sfConfig::get('sf_root_dir')
      ));      
    }
    else
    {
      $this->getFilesystem()->sh(sprintf('cd %s',
        sfConfig::get('sf_root_dir')
      ));
    }


    $pid = (int)$op[0];
    file_put_contents($this->getPidFile($app, $env), $pid);

    $this->logSection("solr", "Server started with pid : ".$pid);
    $this->logSection("solr", "server started  : http://".$host.":".$port.$base_url);
  }

  public function stop($app, $env, $options = array())
  {
    if(!$this->isRunning($app, $env))
    {

      throw new sfException('Server is not running');
    }

    $pid = file_get_contents($this->getPidFile($app, $env));

    if(!($pid > 0))
    {
      
      throw new sfException('Invalid pid provided : '.$pid);
    }

    if(method_exists($this->getFilesystem(), 'execute')) // sf1.3 or greater
    {
      $this->getFilesystem()->execute("kill -15 ".$pid);
    }
    else
    {
      $this->getFilesystem()->sh("kill -15 ".$pid);
    }

    unlink($this->getPidFile($app, $env));
  }

  public function status($app, $env, $options = array())
  {

    if(!$this->isRunning($app, $env))
    {
      
      $this->log('pid file not presents');
      return;
    }

    $pid = file_get_contents($this->getPidFile($app, $env));

    if(!($pid > 0))
    {
      $this->log('pid file presents but invalid pid');
      return;
    }
    
    exec("ps ax | grep $pid 2>&1", $output);

    while( list(,$row) = each($output) ) {

      $row_array = explode(" ", $row);
      $check_pid = $row_array[0];

      if($pid == $check_pid) {
        $this->log('server running');
        return;
      }
    }

    $this->log('server is not running');
  }

  public function getPidFile($app, $env)
  {
    $file = sprintf('%s/solr_index/%s_%s.pid',
      sfConfig::get('sf_data_dir'),
      $app,
      $env
    );

    return $file;
  }
}