<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLuceneHighlightFilter automatically highlights the content according to the highlight parameter
 * and adds a notice to the user that the highlighting was preformed.
 *
 * Note: The highlight filter assumes valid X/HTML.  If it is not, unexpected highlighting may occur!
 *
 * @package sfLucenePlugin
 * @subpackage Filter
 * @author Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneHighlightFilter extends sfFilter
{
  /**
   * Initializes this Filter.
   *
   * @param sfContext The current application context
   * @param array   An associative array of initialization parameters
   *
   * @return boolean true, if initialization completes successfully, otherwise false
   */
  public function initialize($context, $parameters = array())
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();

    // add default options
    $this->parameterHolder->add(
      array(
        'check_referer'             => true,
        'highlight_qs'              => 'sf_highlight',
        'notice_tag'                => '<!--[HIGHLIGHTER_NOTICE]-->',
        'highlight_strings'         => array(
                                        '<strong class="highlight hcolor1">%s</strong>',
                                        '<strong class="highlight hcolor2">%s</strong>',
                                        '<strong class="highlight hcolor3">%s</strong>',
                                        '<strong class="highlight hcolor4">%s</strong>',
                                        '<strong class="highlight hcolor5">%s</strong>'
                                      ),
        'notice_referer_string'     => '<div>Welcome from <strong>%from%</strong>!  The following keywords were automatically highlighted: %keywords% %remove%</div>',
        'notice_string'             => '<div>The following keywords were automatically highlighted: %keywords% %remove%</div>',
        'remove_string'             => '[<a href="%url%">remove highlighting</a>]',
        'css'                       => '../sfLucenePlugin/css/search.css',
        'possible_refers'           => array(
                                        'google'  => array('qs' => 'q',         'name' => 'Google'),
                                        'yahoo'   => array('qs' => 'p',         'name' => 'Yahoo!'),
                                        'msn'     => array('qs' => 'q',         'name' => 'MSN'),
                                        'live'    => array('qs' => 'q',         'name' => 'Live'),
                                        'ask'     => array('qs' => 'q',         'name' => 'Ask'),
                                        'a9'      => array('qs' => 'query',     'name' => 'A9'),
                                      )
      )
    );

    // add custom options
    $this->parameterHolder->add($parameters);

    return true;
  }

  /**
   * Executes the filter
   */
  public function execute($filterChain)
  {
    $filterChain->execute();

    $response   = $this->getContext()->getResponse();
    $request    = $this->getContext()->getRequest();
    $controller = $this->getContext()->getController();

    // don't highlight:
    // * for XHR requests
    // * if 304
    // * if not rendering to the client
    // * if HTTP headers only
    if (
      $request->isXmlHttpRequest()                          ||
      strpos($response->getContentType(), 'html') === false ||
      $response->getStatusCode() == 304                     ||
      $controller->getRenderMode() != sfView::RENDER_CLIENT ||
      $response->isHeaderOnly()
    )
    {
      return;
    }

    $timer = sfTimerManager::getTimer('Highlight Filter');

    try
    {
      if (!$this->highlight())
      {
        // highlighting did not occur, so remove notice
        $this->removeNotice();
      }
    }
    catch (Exception $e)
    {
      $timer->addTime();
      throw $e;
    }

    $timer->addTime();
  }

  /**
   * Attempt to highlight the page
   * @return bool True if highlighting occured, false otherwise
   */
  protected function highlight()
  {
    $terms = $this->getContext()->getRequest()->getParameter($this->getParameter('highlight_qs'));
    $terms = $this->prepareTerms($terms);

    // attempt to highlight from sfLucene
    if (count($terms))
    {
      $this->addNotice($terms);
      $this->addCss();
      $this->doHighlight($terms);

      return true;
    }
    // attempt to highlight from referer (ie, google)
    elseif ($this->getParameter('check_referer'))
    {
      $referer = $this->getContext()->getRequest()->getReferer();

      // continue only if we have a referer
      if ($referer)
      {
        // go through each referer and stop once we have a match
        foreach ($this->getParameter('possible_refers') as $domain => $value)
        {
          $regex = '#^https?://(?:\w+\.)*' . preg_quote($domain, '#') . '(?:\.[a-z]+)+.*' . preg_quote($value['qs'], '#') . '=(.*?)(&|$)#';

          // valid referer?
          if (preg_match($regex, $referer, $matches))
          {
            // referer match.  highlight!

            $terms = $this->prepareTerms($matches[1]);

            $this->addNotice($terms, $value['name']);
            $this->addCss();
            $this->doHighlight($terms);

            // stop looking for referers now.

            return true;
          }
        }
      }
    }

    // we failed to do anything, so return false
    return false;
  }

  /**
   * Highlights the content for $terms
   */
  protected function doHighlight(array $terms)
  {
    $content = $this->getContext()->getResponse()->getContent();

    // configure highlighter
    $lighter = new sfLuceneHighlighter($content);
    $lighter->addKeywords($terms);
    $lighter->addHighlighters($this->getParameter('highlight_strings'));
    $lighter->hasBody(true);

    $this->getContext()->getResponse()->setContent($lighter->highlight());
  }

  /**
   * Add the neccessary CSS for the response
   */
  protected function addCss()
  {
    $content = $this->getContext()->getResponse()->getContent();

    $css = $this->getParameter('css');

    if ($css && false !== ($pos = stripos($content, '</head>')))
    {

      sfLoader::loadHelpers(array('Tag', 'Asset'));

      $css = stylesheet_tag($css);

      $this->getContext()->getResponse()->setContent(substr($content, 0, $pos) . $css . substr($content, $pos));
    }
  }

  /**
   * Prepares terms by exploding them out
   */
  protected function prepareTerms($terms)
  {
    $terms = preg_split('/\W+/', trim($terms), -1, PREG_SPLIT_NO_EMPTY);

    $terms = array_unique($terms);

    return $terms;
  }

  /**
   * Removes the notice token from the content because highlighting didn't happen
   */
  protected function removeNotice()
  {
    $this->getContext()->getResponse()->setContent(
      str_replace($this->getParameter('notice_tag'), '', $this->getContext()->getResponse()->getContent())
    );
  }

  /**
   * Replace the notice with a message that highlighting did occur
   */
  protected function addNotice($terms, $from = null)
  {
    $content = $this->getContext()->getResponse()->getContent();

    $term_string = implode($terms, ', ');

    $route = $route = $this->getContext()->getRouting()->getCurrentInternalUri();
    $route = preg_replace('/(\?|&)' . $this->getParameter('highlight_qs') . '=.*?(&|$)/', '$1', $route);
    $route = $this->getContext()->getController()->genUrl($route);

    $remove_string = $this->translate($this->getParameter('remove_string'), array('%url%' => $route));

    if ($from)
    {
      $message = $this->translate($this->getParameter('notice_referer_string'), array('%from%' => $from, '%keywords%' => $term_string, '%remove%' => $remove_string));
    }
    else
    {
      $message = $this->translate($this->getParameter('notice_string'), array('%keywords%' => $term_string, '%remove%' => $remove_string));
    }

    $content = str_replace($this->getParameter('notice_tag'), $message, $content);

    $this->getContext()->getResponse()->setContent($content);
  }

  /**
   * Helper function to do translations
   */
  protected function translate($text, $args)
  {
    if (sfConfig::get('sf_i18n', false) && $this->getContext()->getI18N())
    {
      return $this->getContext()->getI18N()->__($text, $args, 'messages');
    }
    else
    {
      return str_replace(array_keys($args), array_values($args), $text);
    }
  }
}
