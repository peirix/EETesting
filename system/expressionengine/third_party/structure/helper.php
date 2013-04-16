<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Helper
{
  public static function remove_double_slashes($str)
  {
    return preg_replace("#(^|[^:])//+#", "\\1/", $str);
  }
}