<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Forms Helper File
 *
 * @package			DevDemon_Forms
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Updater_helper
{

	private $package_name = 'updater';


	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->site_id = $this->EE->config->item('site_id');
		$this->AJAX = NULL;

		// Set the EE Cache Path? (hell you can override that)
		$this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';
	}

	// ********************************************************************************* //

	public function define_theme_url()
	{
		if (defined('URL_THIRD_THEMES') === TRUE)
		{
			$theme_url = URL_THIRD_THEMES;
		}
		else
		{
			$theme_url = $this->EE->config->item('theme_folder_url').'third_party/';
		}

		// Are we working on SSL?
		if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE)
		{
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}
		elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
		{
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}

		$theme_url = str_replace(array('https://', 'http://'), '//', $theme_url);

		if (! defined('UPGRADER_THEME_URL')) define('UPGRADER_THEME_URL', $theme_url . 'updater/');

		return UPGRADER_THEME_URL;
	}

	// ********************************************************************************* //

	function get_thirdparty_path()
	{
		if (defined('PATH_THIRD') === TRUE)
		{
			$path = PATH_THIRD;
		}
		else
		{
			$path = APPPATH.'third_party/';
		}

		return $path;
	}

	// ********************************************************************************* //

	function get_theme_path()
	{
		if (defined('PATH_THEMES') === TRUE)
		{
			$theme_path = PATH_THEMES;
		}
		else
		{
			$theme_path = $this->EE->config->item('theme_folder_path');
		}

		return $theme_path;
	}

	// ********************************************************************************* //

	function get_thirdparty_theme_path()
	{
		if (defined('PATH_THIRD_THEMES') === TRUE)
		{
			$theme_path = PATH_THIRD_THEMES;
		}
		else
		{
			$theme_path = $this->EE->config->item('theme_folder_path').'third_party/';
		}

		return $theme_path;
	}

	// ********************************************************************************* //

	function get_router_url($type='url')
	{
		$this->EE->db->select('action_id');
		$this->EE->db->where('class', 'Updater');
		$this->EE->db->where('method', 'ACT_general_router');
		$query = $this->EE->db->get('exp_actions');
		$ACT_ID = $query->row('action_id');

		// Grab Site URL
		$url = $this->EE->functions->fetch_site_index(0, 0);

		if (defined('MASKED_CP') == FALSE OR MASKED_CP == FALSE)
		{
			// Replace site url domain with current working domain
			$server_host = (isset($_SERVER['HTTP_HOST']) == TRUE && $_SERVER['HTTP_HOST'] != FALSE) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			$url = preg_replace('#http\://(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?\/#', "http://{$server_host}/", $url);
		}

		// Create new URL
		$ajax_url = $url.QUERY_MARKER.'ACT=' . $ACT_ID;
		$ajax_url = str_replace(array('https://', 'http://'), '//', $ajax_url);

		return $ajax_url;
	}

	// ********************************************************************************* //

	/**
	 * Grab File Module Settings
	 * @return array
	 */
	function grab_settings($site_id=FALSE)
	{
		$settings = array();

		if (isset($this->EE->session->cache['updater']['module_settings']) == TRUE)
		{
			$settings = $this->EE->session->cache['updater']['module_settings'];
		}
		else
		{
			$this->EE->db->select('settings');
			$this->EE->db->where('module_name', 'Updater');
			$query = $this->EE->db->get('exp_modules');
			if ($query->num_rows() > 0) $settings = unserialize($query->row('settings'));
			if ($settings == FALSE) $settings = array();
		}

		$conf = $this->EE->config->item('updater_module_defaults');
		$override_conf = $this->EE->config->item('updater');
		if (is_array($override_conf) == FALSE) $override_conf = array();

		$settings = $this->array_extend($conf, $settings);

		if (!empty($override_conf))	$settings = $this->array_extend($settings, $override_conf);

		$this->EE->session->cache['updater']['module_settings'] = $settings;

		return $settings;
	}

	// ********************************************************************************* //

	public function is_ajax()
	{
		if ($this->AJAX === NULL OR defined('IS_AJAX') == FALSE)
		{
			if ( $this->EE->input->get_post('ajax') != FALSE OR (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') )
			{
				if (defined('IS_AJAX') == FALSE) define('IS_AJAX', TRUE);
				$this->AJAX = TRUE;
			}
			else
			{
				if (defined('IS_AJAX') == FALSE) define('IS_AJAX', FALSE);
				$this->AJAX = FALSE;
			}
		}

		return $this->AJAX;
	}

	// ********************************************************************************* //

	public function generate_json($obj)
	{
		if (function_exists('json_encode') === FALSE)
		{
			if (class_exists('Services_JSON') === FALSE) include 'JSON.php';
			$JSON = new Services_JSON();
			return $JSON->encode($obj);
		}
		else
		{
			return json_encode($obj);
		}
	}

	// ********************************************************************************* //

	public function decode_json($obj)
	{
		if (function_exists('json_decode') === FALSE)
		{
			if (class_exists('Services_JSON') === FALSE) include 'JSON.php';
			$JSON = new Services_JSON();
			return $JSON->decode($obj);
		}
		else
		{
			return json_decode($obj);
		}
	}

	// ********************************************************************************* //

	public function recurse_copy($src,$dst)
	{
		$dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) )
	    {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir( $src . '/' . $file ) ) {
	                $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
	            }
	            else {
	            	copy($src.'/'.$file, $dst.'/'.$file);
	            }
	        }
	    }
	    closedir($dir);
	}

	// ********************************************************************************* //

	public function delete_files($path, $del_dir = FALSE, $level = 0)
	{
		// Trim the trailing slash
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		if ( ! $current_dir = @opendir($path))
		{
			return FALSE;
		}

		while(FALSE !== ($filename = @readdir($current_dir)))
		{
			if ($filename != "." and $filename != "..")
			{
				if (is_dir($path.DIRECTORY_SEPARATOR.$filename))
				{
					$this->delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
				}
				else
				{
					unlink($path.DIRECTORY_SEPARATOR.$filename);
				}
			}
		}
		@closedir($current_dir);

		if ($del_dir == TRUE AND $level > 0)
		{
			return @rmdir($path);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Array Extend
	 * "Extend" recursively array $a with array $b values (no deletion in $a, just added and updated values)
	 * @param array $a
	 * @param array $b
	 */
	public function array_extend($a, $b) {
		foreach($b as $k=>$v) {
			if( is_array($v) ) {
				if( !isset($a[$k]) ) {
					$a[$k] = $v;
				} else {
					$a[$k] = $this->array_extend($a[$k], $v);
				}
			} else {
				$a[$k] = $v;
			}
		}
		return $a;
	}

	// ********************************************************************************* //

	/**
	 * Fetch URL with file_get_contents or with CURL
	 *
	 * @param string $url
	 * @return mixed
	 */
	function fetch_url_file($url, $user=false, $pass=false)
	{
		$data = '';

		/** --------------------------------------------
		/**  file_get_contents()
		/** --------------------------------------------*/

		if ((bool) @ini_get('allow_url_fopen') !== FALSE && $user == FALSE)
		{
			if ($data = @file_get_contents($url))
			{
				return $data;
			}
		}

		/** --------------------------------------------
		/**  cURL
		/** --------------------------------------------*/

		if (function_exists('curl_init') === TRUE && ($ch = @curl_init()) !== FALSE)
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 (.NET CLR 3.5.30729)');

			if ($user != FALSE)
			{
				curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
				if (defined('CURLOPT_HTTPAUTH')) curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			}

			$data = curl_exec($ch);
			curl_close($ch);

			if ($data !== FALSE)
			{
				return $data;
			}
		}

		/** --------------------------------------------
        /**  fsockopen() - Last but only slightly least...
        /** --------------------------------------------*/

		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';

		if (isset($parts['query']) && $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 7);

		if (is_resource($fp))
		{
			fputs ($fp, "GET ".$path." HTTP/1.0\r\n" );
			fputs ($fp, "Host: ".$host . "\r\n" );
			fputs ($fp, "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1)\r\n");

			if ($user != FALSE)
			{
				fputs ($fp, "Authorization: Basic ".base64_encode("$user:$pass")."\r\n");
			}

			fputs ($fp, "Connection: close\r\n\r\n");

			$header = '';
			$body   = '';

			/* ------------------------------
			/*  This error suppression has to do with a PHP bug involving
			/*  SSL connections: http://bugs.php.net/bug.php?id=23220
			/* ------------------------------*/

			$old_level = error_reporting(0);

			/*
			while ( ! feof($fp))
			{
				$data .= trim(fgets($fp, 128));
			}
			*/

			// put the header in variable $header
			do // loop until the end of the header
			{
				$header .= fgets ( $fp, 128 );

			} while ( strpos ( $header, "\r\n\r\n" ) === false );

			// now put the body in variable $body
			while ( ! feof ( $fp ) )
			{
				$body .= fgets ( $fp, 128 );
			}

			error_reporting($old_level);

			$data = $body;

			fclose($fp);
		}

		return $data;
	}

	// ********************************************************************************* //

   	public function mcp_meta_parser($type='js', $url, $name, $package='')
	{
		// -----------------------------------------
		// CSS
		// -----------------------------------------
		if ($type == 'css')
		{
			if ( isset($this->EE->session->cache['DevDemon']['CSS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $url . '" type="text/css" media="print, projection, screen" />');
				$this->EE->session->cache['DevDemon']['CSS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Javascript
		// -----------------------------------------
		if ($type == 'js')
		{
			if ( isset($this->EE->session->cache['DevDemon']['JS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<script src="' . $url . '" type="text/javascript"></script>');
				$this->EE->session->cache['DevDemon']['JS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Global Inline Javascript
		// -----------------------------------------
		if ($type == 'gjs')
		{
			if ( isset($this->EE->session->cache['DevDemon']['GJS'][$name]) == FALSE )
			{
				$AJAX_url = $this->get_router_url();
				$THEME_url = $this->define_theme_url();
				$MCP_BASE = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=upgrader';

				$js = "	var Upgrader = Upgrader ? Upgrader : new Object();
						Upgrader.AJAX_URL = '{$AJAX_url}';
						Upgrader.THEME_URL = '{$THEME_url}';
						Upgrader.site_id = '{$this->site_id}';
						Upgrader.MCP_BASE = '{$MCP_BASE}';
					";

				$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
				$this->EE->session->cache['DevDemon']['GJS'][$name] = TRUE;
			}
		}
	}

} // END CLASS

/* End of file forms_helper.php  */
/* Location: ./system/expressionengine/third_party/forms/libraries/forms_helper.php */
