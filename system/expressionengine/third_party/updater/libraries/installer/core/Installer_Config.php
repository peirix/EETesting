<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------


// Some of the functions we need - such as updating
// new config files are already in the main app.
// Instead of reimplementing those methods, we'll
// include that file and subclass it again.

require_once(EE_APPPATH.'core/EE_Config'.EXT);

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Config Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Installer_Config Extends EE_Config {

	var $config_path 		= ''; // Set in the constructor below
	var $database_path		= ''; // Set in the constructor below
	var $exceptions	 		= array();	 // path.php exceptions

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->config_path		= EE_APPPATH.'/config/config'.EXT;
		$this->database_path	= EE_APPPATH.'/config/database'.EXT;

		$this->_initialize();
	}

	// --------------------------------------------------------------------

	/**
	 * Load the EE config file and set the initial values
	 *
	 * @access	private
	 * @return	void
	 */
	function _initialize()
	{
		// Fetch the config file
		if ( ! @include($this->config_path))
		{
			show_error('Unable to locate your config file (expressionengine/config/config.php)');
		}

		// Prior to 2.0 the config array was named $conf.  This has changed to $config for 2.0
		if (isset($conf))
		{
			$config = $conf;
		}

		// Is the config file blank?  If not, we bail out since EE hasn't been installed
		if ( ! isset($config) OR count($config) == 0)
		{
			return FALSE;
		}

		$config['enable_query_strings'] = FALSE;
		$config['controller_trigger'] = '';
		$config['function_trigger'] = '';
		$config['directory_trigger'] = '';

		// Add the EE config data to the master CI config array
		foreach ($config as $key => $val)
		{
			$this->set_item($key, $val);
		}
		unset($config);

		// Set any config overrides.  These are the items that used to be in
		// the path.php file, which are now located in the main index file
		$this->_set_overrides($this->config);

		$this->set_item('enable_query_strings', FALSE);
		$this->set_item('controller_trigger', '');
		$this->set_item('function_trigger', '');
		$this->set_item('directory_trigger', '');
	}

	// --------------------------------------------------------------------

	/**
	 * Set configuration overrides
	 *
	 * 	These are configuration exceptions.  In some cases a user might want
	 * 	to manually override a config file setting by adding a variable in
	 * 	the index.php or path.php file.  This loop permits this to happen.
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_overrides($params = array())
	{
		if ( ! is_array($params) OR count($params) == 0)
		{
			return;
		}

		// Assign global variables if they exist
		$this->_global_vars = ( ! isset($params['global_vars']) OR ! is_array($params['global_vars'])) ? array() : $params['global_vars'];

		$exceptions = array();
		foreach (array('site_url', 'site_index', 'site_404', 'template_group', 'template') as $exception)
		{
			if (isset($params[$exception]) AND $params[$exception] != '')
			{
				if ( ! defined('REQ') OR REQ != 'CP')
				{
					$this->config[$exception] = $params[$exception]; // User/Action
				}
				else
				{
					$exceptions[$exception] = $params[$exception];  // CP
				}
			}
		}

		$this->exceptions = $exceptions;

		unset($params);
		unset($exceptions);
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file Installer_Config.php */
/* Location: ./system/expressionengine/installer/libraries/Installer_Config.php */
