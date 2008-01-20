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
 * @subpackage Widget
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneWidgetFormatterAdvanced extends sfLuceneWidgetFormatter
{
  protected
    $rowFormat       = "<tr>\n  <th>%label%</th>\n  <td>%error%%field%%help%%hidden_fields%</td>\n</tr>\n",
    $errorRowFormat  = "<tr><td colspan=\"2\">\n%errors%</td></tr>\n",
    $helpFormat      = '<br />%help%',
    $decoratorFormat = "<table>\n  %content%</table>";
}