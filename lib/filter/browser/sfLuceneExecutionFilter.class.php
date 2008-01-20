<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package sfLucenePlugin
 * @subpackage Filter
 * @author Carl Vondrick
 * @version SVN: $Id$
 */
class sfLuceneExecutionFilter extends sfExecutionFilter
{
  /**
   * Executes and renders the view.
   *
   * The behavior of this method depends on the controller render mode:
   *
   *   - sfView::NONE: Nothing happens.
   *   - sfView::RENDER_CLIENT: View data populates the response content.
   *   - sfView::RENDER_DATA: View data populates the data presentation variable.
   *
   * @param  string The module name
   * @param  string The action name
   * @param  string The view name
   * @param  array  An array of view attributes
   *
   * @return string The view data
   */
  protected function executeView($moduleName, $actionName, $viewName, $viewAttributes)
  {
    $controller = $this->context->getController();

    // get the view instance
    $view = $controller->getView($moduleName, $actionName, $viewName);

    $view->setDecorator(false);

    // execute the view
    $view->execute();

    // pass attributes to the view
    $view->getAttributeHolder()->add($viewAttributes);

    // render the view
    switch ($controller->getRenderMode())
    {
      case sfView::RENDER_NONE:
        break;

      case sfView::RENDER_CLIENT:
        $viewData = $view->render();
        $this->context->getResponse()->setContent($viewData);
        break;

      case sfView::RENDER_VAR:
        $viewData = $view->render();
        $controller->getActionStack()->getLastEntry()->setPresentation($viewData);
        break;
    }
  }
}