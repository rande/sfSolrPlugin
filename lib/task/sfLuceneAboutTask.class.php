<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfLuceneBaseTask.class.php');

/**
* Task that dumps information about sfLucene
*
* @author Carl Vondrick <carl@carlsoft.net>
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
* @package sfLucenePlugin
* @subpackage Tasks
* @version SVN: $Id$
*/

class sfLuceneAboutTask extends sfLuceneBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The application name')
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'search')
    ));

    $this->aliases = array('lucene-about');
    $this->namespace = 'lucene';
    $this->name = 'about';
    $this->briefDescription = 'Dumps information about sfLucene';

    $this->detailedDescription = <<<EOF
The [lucene:about|INFO] task dumps information about your indexes.

If you provide an application, this task becomes much more useful by describing
all of the indexes defined.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];

    $this->notifyListRow('Plugin Version', 'sfLucene ' . sfLucene::VERSION);

    if ($app)
    {
      $this->checkAppExists($app);
      $this->standardBootstrap($app, $options['env']);

      foreach (sfLucene::getAllInstances() as $search)
      {
        $this->dispatcher->notify(new sfEvent($this, 'command.log', array( $this->formatter->format(sprintf('For %s/%s:', $search->getParameter('name'), $search->getParameter('culture')), array('fg' => 'red', 'bold' => true)) )));

        $segments = $search->segmentCount();

        $this->notifyListRow('Document Count', $search->numDocs(), 3);
        $this->notifyListRow('Segment Count', $segments, 3);

        $rawSize = $search->byteSize();
        $size = $rawSize / 1024 > 1024 ? (number_format($rawSize / pow(1024, 2), 3) . 'MB') : (number_format($rawSize / 1024, 3) . ' KB');
        $this->notifyListRow('Index Size', $size, 3);

        if ($segments == 0)
        {
          $condition = 'Empty: Perhaps you should rebuild the index?';
        }
        elseif ($segments == 1)
        {
          $condition = 'Great: No optimization neccessary';
        }
        elseif ($segments <= 10)
        {
          $condition = 'Good: Consider optimizing for full performance';
        }
        elseif ($segments <= 20)
        {
          $condition = 'Bad: Optimization is recommended';
        }
        else
        {
          $condition = 'Terrible: Immediate optimization neccessary!';
        }

        $this->notifyListRow('Index Condition', $condition, 3);
      }
    }
  }

  protected function notifyListRow($section, $message, $indent = 0)
  {
    $padding = str_repeat(' ', $indent);
    $spacing = 35 - $indent;

    $message = sprintf("%s%-{$spacing}s %s", $padding, $this->formatter->format($section . ':', 'INFO'), $message);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($message)));
  }
}