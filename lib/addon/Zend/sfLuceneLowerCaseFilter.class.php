<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

sfLuceneToolkit::loadZend();

/**
 * Same as Zend LowerCase filter, but has an optional ability to use mb_* functions.
 * @package sfLucenePlugin
 * @subpackage Addon
 * @version SVN: $Id$
 */
class sfLuceneLowerCaseFilter extends Zend_Search_Lucene_Analysis_TokenFilter_LowerCase
{
  protected $mbString = false;

  public function __construct($mbString = false)
  {
    $this->mbString = $mbString;
  }

  /**
   * Normalize Token or remove it (if null is returned)
   *
   * @param Zend_Search_Lucene_Analysis_Token $srcToken
   * @return Zend_Search_Lucene_Analysis_Token
   */
  public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
  {
    if ($this->mbString)
    {
      $value = mb_strtolower( $srcToken->getTermText(), 'utf8');
    }
    else
    {
      $value = strtolower( $srcToken->getTermText() );
    }

    $newToken = new Zend_Search_Lucene_Analysis_Token(
                                 $value,
                                 $srcToken->getStartOffset(),
                                 $srcToken->getEndOffset());

    $newToken->setPositionIncrement($srcToken->getPositionIncrement());

    return $newToken;
  }
}

