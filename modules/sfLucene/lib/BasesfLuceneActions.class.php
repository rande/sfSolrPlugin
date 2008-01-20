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
    $form->bind($request->getParameter('form', array()));

    // do we have a query?
    if ($form->isValid())
    {
      $values = $form->getValues();

      // get results
      $pager = $this->getResults($form);

      $num = $pager->getNbResults();

      // were any results returned?
      if ($num > 0)
      {
        // display results
        $pager = $this->configurePager($pager, $form);

        $this->num = $num;
        $this->pager = $pager;
        $this->query = $values['query'];
        $this->form = $form;

        $this->setTitleNumResults($pager);

        return 'Results';
      }
      else
      {
        // display error
        $this->form = $form;
        $this->setTitleI18n('No Results Found');

        return 'NoResults';
      }
    }
    else
    {
      // display search controls
      $this->form = $form;
      $this->setTitleI18n('Search');

      return 'Controls';
    }
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

    // continue only if there was a submit
    if ($request->getParameter('commit'))
    {
      $form->bind($request->getParameter('form'));

        // is the form valid?
      if ($form->isValid())
      {
        $values = $form->getValues();

        // base quey
        $query = $values['keywords'];

        // build the must have part
        $musthave = preg_split('/ +/', $values['musthave'], -1, PREG_SPLIT_NO_EMPTY);

        if (count($musthave))
        {
          $query .= ' +' . implode($musthave, ' +');
        }

        // build the must not have
        $mustnothave = preg_split('/ +/', $values['mustnothave'], -1, PREG_SPLIT_NO_EMPTY);

        if (count($mustnothave))
        {
          $query .= ' -' . implode($mustnothave, ' -');
        }

        // build the has pharse part
        if ($values['hasphrase'] != '')
        {
          $query .= ' "' . str_replace('"', '', $values['hasphrase']) . '"';
        }

        $query = trim($query);

        // is there a query?
        if ($query)
        {
          // yes, so search

          $requestParam = array('query' => $query);

          if (isset($values['category']))
          {
            $requestParam['category'] = $values['category'];
          }

          $request->setParameter('form', $requestParam);

          $this->forward($this->getModuleName(), 'search');
        }
      }
    }

    $this->form = $form;

    $this->setTitleI18n('Advanced Search');

    return 'Controls';
  }

  /**
  * Wrapper function for getting the results.
  */
  protected function getResults($form)
  {
    $data = $form->getValues();

    $query = new sfLuceneCriteria($this->getLuceneInstance());
    $query->addSane($data['query']);

    if (sfConfig::get('app_lucene_categories', true) && isset($data['category']) && $data['category'] != $this->translate('All'))
    {
      $query->add('sfl_category: ' . $data['category']);
    }

    return new sfLucenePager( $this->getLuceneInstance()->friendlyFind($query) );
  }

  /**
   * Returns an instance of sfLucene configured for this environment.
   */
  protected function getLuceneInstance()
  {
    return sfLuceneToolkit::getApplicationInstance();
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

  protected function setTitleI18n($title, $args = array(), $ns = 'messages')
  {
    $this->setTitle( $this->translate($title, $args, $ns) );
  }

  protected function translate($text, $args = array(), $ns = 'messages')
  {
    sfLoader::loadHelpers('I18N');

    return __($text, $args, $ns);
  }
}
