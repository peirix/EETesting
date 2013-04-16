<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Backup Pro
 *
 * @package		mithra62:M62_backup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/backup-pro/
 * @version		1.8.6
 * @filesource 	./system/expressionengine/third_party/m62_backup/
 */
 
 /**
 * Backup Pro - Helper Functions
 *
 * Helper Functions
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/helpers/utilities_helper.php
 */
if ( ! function_exists('m62_convert_timestamp'))
{
	function m62_convert_timestamp($date, $format = FALSE)
	{
		$EE =& get_instance();
		$EE->load->helper('date');
		if(!$format)
		{
			$format = $EE->m62_backup_lib->settings['date_format'];
		}
		
		return mdate($format, $date);		
	}
}