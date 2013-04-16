<?php

/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0.2
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/add-ons/expressionengine/dukt-videos/
 *
 */
 
namespace DuktVideos;

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/config.php');

/* App Interface */

require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'interfaces/app.php');

class App implements iApp {
	
	var $version = '1.0.2';
	
	function __construct()
	{
	
		$this->EE =& get_instance();
		
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
		
		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$this->EE->lang->loadfile('dukt_videos');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Userdata
	 *
	 * @access	public
	 */
	public static function userdata($k)
	{
		$EE =& get_instance();
		return $EE->session->userdata[$k];
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Localized decode date
	 *
	 * @access	public
	 */
	public static function localize_decode_date($format, $stamp)
	{
		$EE =& get_instance();
		return $EE->localize->decode_date($format, $stamp);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Developer Log
	 *
	 * @access	public
	 */
	public static function developer_log($msg)
	{
		$EE =& get_instance();
		
		$EE->load->library('logger');
		
		$debug = \DuktVideos\Config::item('debug');
		
		if($debug)
		{
			$EE->logger->developer("Dukt Videos : ".$msg, TRUE);	
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Cache Path
	 *
	 * @access	public
	 */
	public static function cache_path()
	{
		$cache_path = APPPATH.'cache/dukt_videos/';
		
		return $cache_path;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Lang Line
	 *
	 * @access	public
	 */
	public static function lang_line($k)
	{
		$EE =& get_instance();
		
		return $EE->lang->line($k);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Current Language
	 *
	 * @access	public
	 */
	public static function current_language()
	{
		$EE =& get_instance();
		
		return $EE->lang->user_lang;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Display error message
	 *
	 * @access	public
	 */	
	public function problem($msg)
	{
		$this->EE->session->set_flashdata('message_failure', $msg);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Display success message
	 *
	 * @access	public
	 */	
	public function success($msg)
	{
		$this->EE->session->set_flashdata('message_success', $msg);
	}

	// --------------------------------------------------------------------

	/**
	 * Get a single service
	 *
	 * @access	public
	 */	
	public static function get_service($service_key=false)
	{
        $fn = array('self', 'get_services');
        
		$services = call_user_func($fn);
		
		if($service_key)
		{
			if(isset($services[$service_key]))
			{
				return $services[$service_key];
			}
		}
		
		return false;
	}
		
	// --------------------------------------------------------------------

	/**
	 * Get multiple services
	 *
	 * @access	public static
	 */	
	public static function get_services()
	{
		$EE =& get_instance();
		$EE->load->model('dukt_videos_model');
		
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
		
		$lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$lib->load_helper('directory');
		
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/service.php');
		
		$services = array();

		$map = directory_map(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/services/', 1);

		foreach($map as $service_key)
		{
			$service_key = substr($service_key, 0, -4);
			
			$service_class_file = DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/services/'.$service_key.'.php';

			if(file_exists($service_class_file))
			{			
				include_once($service_class_file);
				
				$service_class = '\\DuktVideos\\'.ucwords($service_key);


				$service_obj = new $service_class();
				
				
				// enabled
				
				$option_name = 'enabled';
				
				$db_option = self::get_option($service_key, $option_name);

				$service_obj->enabled = $db_option;
				
				
				// api options
				
				foreach($service_obj->api_options as $option_name => $option_value)
				{
					$db_option = self::get_option($service_key, $option_name);
					
					$service_obj->api_options[$option_name] = $db_option;
				}
				
				
				// token options
				
				foreach($service_obj->token_options as $option_name => $option_value)
				{
					$db_option = self::get_option($service_key, $option_name);
				
					$service_obj->token_options[$option_name] = $db_option;
				}
				
				
				// redirects urls
				
				$redirect_url = self::callback_url($service_key);
				
				
				// cp url
				
				$cp_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				
				
				// success url
				
				$success_url = parse_url($cp_url);
				
				if(isset($success_url['query']))
				{
					parse_str($success_url['query'], $success_query);

					if(isset($success_query['method']))
					{
						unset($success_query['method']);
					}
					
					if(isset($success_query['service']))
					{
						unset($success_query['service']);
					}
					
					$success_query = http_build_query($success_query);
					
					$success_url = $success_url['scheme'].'://'.$success_url['host'].$success_url['path'].'?'.$success_query;
				}
				else
				{
					$success_url = "";
				}
				
				
				// success url
				
				$problem_url = parse_url($cp_url);
				
				if(isset($problem_url['query']))
				{
					parse_str($problem_url['query'], $problem_query);
					
					$problem_query['method'] = 'configure';
					
					$problem_query = http_build_query($problem_query);
					
					$problem_url = $problem_url['scheme'].'://'.$problem_url['host'].$problem_url['path'].'?'.$problem_query;
				}
				else
				{
					$problem_url = "";
				}
				
				
				// assign variables

				$service_obj->redirect_url = $redirect_url;
				$service_obj->success_url = $success_url;
				$service_obj->problem_url = $problem_url;
				
				$services[$service_key] = $service_obj;	

			}
		}
		
		return $services;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get callback_url
	 *
	 * @access	public static
	 */	
	public static function callback_url($service_key)
	{
		$EE =& get_instance();
		$EE->db->where('class', 'Dukt_videos');
		$EE->db->where('method', 'callback');
		
		$query = $EE->db->get('actions');

		$act_id = "";

		if ($query->num_rows() > 0)
		{
		   $row = $query->row(); 
		
		   $act_id = $row->action_id;

		
		
			// redirect url
			
			$callback_url = $EE->functions->fetch_site_index(0, 0).'?'.'ACT='.$act_id.'&service='.$service_key;
			
			return $callback_url;
		}
		
		return false;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get video with cache layer or not
	 *
	 * @access	public static
	 */		
	public static function get_video($video_url, $cache = false)
	{
        $fn = array('self', 'get_services');
        
		$services = call_user_func($fn);
		
		foreach($services as $service)
		{
			$video = $service->get_video($video_url, $cache);

			if($video)
			{		
				return $video;	
			}
		}
		
		return false;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get option
	 *
	 * @access	public static
	 */	
	public static function get_option($service, $k, $default=false)
	{
		$EE =& get_instance();
		
		$EE->load->add_package_path(DUKT_VIDEOS_UNIVERSAL_PATH.'cms/expressionengine');
		
		$EE->load->model('dukt_videos_model');
		
		$v = $EE->dukt_videos_model->get_option($service, $k);
		
		$EE->load->remove_package_path(DUKT_VIDEOS_UNIVERSAL_PATH.'cms/expressionengine');
		
		return $v;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Set option
	 *
	 * @access	public
	 */	
	public static function set_option($service, $k, $v)
	{
		$EE =& get_instance();
		
		$EE->load->add_package_path(DUKT_VIDEOS_UNIVERSAL_PATH.'cms/expressionengine');
		
		$EE->load->model('dukt_videos_model');
		
		$EE->dukt_videos_model->set_option($service, $k, $v);
		
		$EE->load->remove_package_path(DUKT_VIDEOS_UNIVERSAL_PATH.'cms/expressionengine');
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * CP Link
	 *
	 * @access	public
	 */	
	public function cp_link($more = false)
	{
		$url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dukt_videos';
		
		if($more)
		{
			$url .= AMP.$more;
		}
		
		return $url;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Redirect
	 *
	 * @access	public static
	 */	
	public static function redirect($url)
	{
		$EE =& get_instance();
		
    	$EE->functions->redirect($url);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Insert JS code
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function insert_js($str)
	{
		$this->EE->cp->add_to_head('<script type="text/javascript">' . $str . '</script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Insert JS file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function insert_js_file($file)
	{
		$this->EE->cp->add_to_head('<script charset="utf-8" type="text/javascript" src="'.$this->_theme_url().$file.'?'.$this->version.'"></script>');
	}

	// --------------------------------------------------------------------

	/**
	 * Insert CSS file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function insert_css_file($file)
	{
		$this->EE->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->_theme_url().$file.'?'.$this->version.'" />');
	}

	// --------------------------------------------------------------------

	/**
	 * Load heading files once (load_head_files)
	 *
	 * @access	private
	 * @return	void
	 */
	public function include_resources()
	{
		$js = "	var Dukt_videos = Dukt_videos ? Dukt_videos : new Object();
				Dukt_videos.ajax_endpoint = '".$this->ajax_endpoint_url()."';
				Dukt_videos.site_id = '".$this->EE->config->item('site_id')."';
			";
			


		$debug = \DuktVideos\Config::item('debug');;
		
		if($debug)
		{
			$js .= 'var dukt_debug = true;';
		}

		$this->insert_js($js);

		$this->insert_css_file('universal/css/box.css');
		$this->insert_css_file('expressionengine/css/box.css');
		$this->insert_css_file('expressionengine/css/field.css');

		$this->insert_js_file('universal/js/jquery.easing.1.3.js');
		$this->insert_js_file('universal/js/dukt_log.js');
		$this->insert_js_file('universal/js/spin.min.js');
		$this->insert_js_file('universal/js/box.js');
		
		$this->insert_js_file('expressionengine/js/field.js');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Theme URL
	 *
	 * @access	private
	 * @return	string
	 */
	public function _theme_url()
	{
		$url = $this->EE->config->item('theme_folder_url')."third_party/dukt_videos/";
		return $url;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Ajax endpoint URL
	 *
	 * @access	public
	 * @return	void
	 */
	function ajax_endpoint_url()
	{
		$site_url = $this->EE->functions->fetch_site_index(0, 0);

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		{
			$site_url = str_replace('http://', 'https://', $site_url);
		}

		$action_id = $this->fetch_action_id('Dukt_videos', 'ajax');

		$url = $site_url.QUERY_MARKER.'ACT='.$action_id;

		return $url;
	}
	
	// --------------------------------------------------------------------

	/**
	 * A copy of the standard fetch_action_id method that was unavailable from here
	 *
	 * @access	private
	 * @return	void
	 */
	private function fetch_action_id($class, $method)
	{
		$this->EE->db->select('action_id');
		$this->EE->db->where('class', $class);
		$this->EE->db->where('method', $method);
		$query = $this->EE->db->get('actions');

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		return $query->row('action_id');
	}
}

// END App class

/* End of file app.php */
/* Location: ./system/expressionengine/libraries/app.php */