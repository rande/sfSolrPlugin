<?php
/*
 * This file is part of the sfLucenePLugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for sfLucene actions.
 *
 * @package    sfLucenePlugin
 * @subpackage Module
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
abstract class BasesfLuceneActions extends sfActions
{
  /**
   * For compatiability with default routing rules.
   */
  public function executeIndex($request)
  {
    $this->forward($this->getModuleName(), 'search');
  }

  /**
  * Executes the search action.  If there is a search query present in the request
  * parameters, then a search is executed and uses a paged result.  If not, then
  * the search box is displayed to prompt the user to enter controls.
  */
  public function executeSearch($request)
  {
    // determine if the user pressed the "Advanced"  button
    if ($request->getParameter('commit') == $this->translate('Advanced'))
    {
      // user did, so redirect to advanced search
      $this->redirect($this->getModuleName() . '/advanced');
    }

    $form = new sfLuceneSimpleForm();
    $this->configureCategories($form);
    $form->bind($request->getParameter($form->getName(), array()));

    $this->form = $form;

    if(!$form->isValid())
    {
      // display search controls
      $this->setTitleI18n('Search');
      $this->setTemplate('searchControls');
    }


    $values = $form->getValues();

    
    if (count($values) == 0)
    {
      // display error
      $this->setTitleI18n('No Results Found');

      $this->setTemplate('searchNoResults');

      return sfView::SUCCESS;
    }
    
    // build the criteria
    $query = new sfLuceneCriteria();
    $query->addSane($values['query']);

    if (sfConfig::get('app_lucene_categories', true) && isset($values['category']) && $values['category'] != $this->translate('All'))
    {
      $query->add('sfl_category: ' . $values['category']);
    }

    $pager = new sfLucenePager($this->getLuceneInstance()->friendlyFind($query));

    // were any results returned?
    if ($pager->getNbResults() == 0)
    {
            // display error
      $this->setTitleI18n('No Results Found');

      $this->setTemplate('searchNoResults');

      return sfView::SUCCESS;
    }

    // display results
    $pager = $this->configurePager($pager, $form);

    $this->pager = $pager;
    $this->query = $values['query'];

    $this->setTitleNumResults($pager);

    $this->setTemplate('searchResults');
  }

  /**
  * This action is a friendly advanced search interface.  It lets the
  * user use a form to control some of the advanced query syntaxes.
  */
  public function executeAdvanced($request)
  {
    // disable this action if advanced searching is disabled.
    $this->forward404Unless( sfConfig::get('app_lucene_advanced', true) == true, 'advanced support is disabled' );

    // determine if the "Basic" button was clicked
    if ($request->getParameter('commit') == $this->translate('Basic'))
    {
      
      $this->redirect($this->getModuleName() . '/search');
    }

    $form = new sfLuceneAdvancedForm();
    $this->configureCategories($form);

    $this->form = $form;

    // continue only if there was a submit
    if (!$request->getParameter($form->getName()))
    {
      return sfView::SUCCESS;
    }

    $form->bind($request->getParameter($form->getName()));
    
    $values = $form->getValues();

    // build the criteria
    $c = new sfLuceneCriteria();
    $c->addSane($values['keywords']);

    $query = $values['keywords'];
    
    // build the must have part
    $keywords = preg_split("/[\s,]+/", $values['musthave']);
    foreach($keywords as $keyword)
    {
      $c->add("+".sfLuceneCriteria::sanitize($keyword), sfLuceneCriteria::TYPE_NONE, true);
    }
    $query .= ' '.$values['musthave'];
    

    // build the must have part
    $keywords = preg_split("/[\s,]+/", $values['mustnothave']);
    foreach($keywords as $keyword)
    {
      $c->add("-".sfLuceneCriteria::sanitize($keyword), sfLuceneCriteria::TYPE_NONE, true);
    }
    $query .= ' '.$values['mustnothave'];

    // build the has pharse part
    $c->add("+".sfLuceneCriteria::sanitize($values['hasphrase']), sfLuceneCriteria::TYPE_NONE, true);
    $query .= ' '.$values['hasphrase'];

    if (sfConfig::get('app_lucene_categories', true) && isset($values['category']) && $values['category'] != $this->translate('All'))
    {
      $c->add('sfl_category: ' . $values['category']);
    }

    $pager = new sfLucenePager($this->getLuceneInstance()->friendlyFind($c));

    // display results
    $pager = $this->configurePager($pager, $form);

    $this->getContext()->getConfiguration()->loadHelpers('sfLucene');
    
    $this->pager = $pager;
    $this->query = $query;
    
    $this->setTitleI18n('Advanced Search');
  }

  /**
   * Returns an instance of sfLucene configured for this environment.
   */
  protected function getLuceneInstance()
  
  {
    
    throw new sfException('Implement this feature with an event dispatcher');
  }

  /**
   * Configures the form for categories
   */
  protected function configureCategories($form)
  {
    $categories = $this->getLuceneInstance()->getCategoriesHarness()->getAllCategories();

    if (!sfConfig::get('app_lucene_categories', true) || count($categories) == 0)
    {
      return;
    }

    $categories = array_merge(array($this->translate('All')), $categories);

    $categories = array_combine($categories, $categories);

    $form->setCategories($categories);

    return $form;
  }

  /**
  * Configures the pager according to the request parameters.
  */
  protected function configurePager($pager, $form)
  {
    $values = $form->getValues();
    $page = $values['page'];

    $pager->setMaxPerPage(sfConfig::get('app_lucene_per_page', 10));

    if ($page < 1)
    {
      $pager->setPage(1);
    }
    elseif ($page > $pager->getLastPage())
    {
      $pager->setPage($pager->getLastPage());
    }
    else
    {
      $pager->setPage($page);
    }

    return $pager;
  }

  /**
  * Sets the title depending on the number of results.
  */
  protected function setTitleNumResults($pager)
  {
    $first = $pager->getFirstIndice();
    $last = $pager->getLastIndice();

    if ($first < $last)
    {
      $title = 'Results %first% to %last%';

      if ($pager->haveToPaginate())
      {
        $title .= ' of %total%';
      }
    }
    else
    {
      $title = 'Results %first% of %total%';
    }

    $this->setTitleI18n($title, array('%first%' => $first, '%last%' => $last, '%total%' => $pager->getNbResults()));
  }

  /**
  * Wrapper function for setting the title.  Overload to append or prepend
  * something to the title specific to your application.
  */
  protected function setTitle($title)
  {
    $this->getResponse()->setTitle($title);
  }

  protected function setTitleI18n($title, $args = array(), $ns = 'sfLucene')
  {
    $this->setTitle( $this->translate($title, $args, $ns) );
  }

  protected function translate($text, $args = array(), $ns = 'sfLucene')
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');

    return __($text, $args, $ns);
  }
}
