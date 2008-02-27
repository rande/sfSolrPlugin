<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_test provides some additional tests to the lime testing framework.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_test.php 6959 2008-01-06 03:42:17Z Carl.Vondrick $
  */
class limeade_test extends lime_test
{
  public $report_passes = true;

  public function ok($exp, $msg = '')
  {
    if (!$this->report_passes && $exp)
    {
      ++$this->passed;

      return true;
    }

    return parent::ok($exp, $msg);
  }

  public function exception($msg)
  {
    return new limeade_test_exception($this, $msg, true);
  }

  public function no_exception($msg)
  {
    return new limeade_test_exception($this, $msg, false);
  }

  public function instanceof_ok($obj, $expected, $msg = null)
  {
    if (!$result = $this->ok($obj instanceof $expected, $msg))
    {
      $type = is_object($obj) ? get_class($obj) : gettype($obj);
      $this->output->diag(sprintf("      instanceof_ok isn't a '%s' it's a '%s'", $expected, $type));
    }

    return $result;
  }

  /**
    * Tests to see if $test has the same elements as $expected, disregarding order
    */
  public function has_elements(array $test, array $expected, $msg = null, $strict = true)
  {
    foreach ($test as $test_value)
    {
      if (!in_array($test_value, $expected, $strict))
      {
        $result = $this->fail($msg);
        $this->diag_info(sprintf("      has_elements has alien value %s", $test_value));

        return $result;
      }
    }

    if (count($test) != count($expected))
    {
      // optimal to put this first, but it doesn't give us as nice debugging output
      $result = $this->fail($msg);
      $this->diag_info(sprintf("      has_elements got %d elements, expected %d", count($test), count($expected)));

      return $result;
    }

    return $this->pass($msg);
  }

  public function is_included($file, $msg = null)
  {
    return $this->ok(in_array(realpath($file), get_included_files()), $msg);
  }

  public function are_included($root, array $files)
  {
    $status = true;

    foreach ($files as $file)
    {
      if (!$this->is_included($root . DIRECTORY_SEPARATOR . $file, '"' . $file . '" is included'))
      {
        $status = false;
      }
    }

    return $status;
  }

  public function like_included($regex, $msg = null)
  {
    $files = get_included_files();
    $files = array_filter($files, create_function('$input', 'return preg_match("' . $regex . '", $input);'));

    return $this->ok(count($files) > 0, $msg);
  }

  public function not_like_included($regex, $msg = null)
  {
    $files = get_included_files();
    $files = array_filter($files, create_function('$input', 'return preg_match("' . $regex . '", $input);'));

    return $this->ok(count($files) == 0, $msg);
  }

  public function not_included($file, $msg = null)
  {
    return $this->not_ok(in_array(realpath($file), get_included_files()), $msg);
  }

  public function in_include_path($file, $msg = null)
  {
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
    {
      if (file_exists($path . '/' . $file))
      {
        return $this->pass($msg);
      }
    }

    return $this->fail($msg);
  }

  public function not_in_include_path($file, $msg = null)
  {
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
    {
      if (file_exists($path . '/' . $file))
      {
        return $this->fail($msg);
      }
    }

    return $this->pass($msg);
  }

  public function not_ok($test, $msg = null)
  {
    return $this->ok(!$test, $msg);
  }

  public function file_exists($file, $msg = null)
  {
    return $this->ok(file_exists($file), $msg);
  }

  public function diag_info($msg)
  {
    $this->output->diag('      ' . $msg);
  }
}
