<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_output is a utility class.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_output.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_output
{
  /**
   * Get the best output
   */
  static public function get()
  {
    if (0 == strncasecmp(PHP_SAPI, 'cli', 3))
    {
      return new lime_output_color();
    }
    else
    {
      return new limeade_output_html();
    }
  }
}