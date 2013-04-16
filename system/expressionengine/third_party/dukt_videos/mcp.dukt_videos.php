<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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



require_once PATH_THIRD.'dukt_videos/config.php';

class Dukt_videos_mcp {

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object

		$this->EE =& get_instance();
		
		
		$this->site_id = $this->EE->config->item('site_id');
		
		
		// load dukt videos

		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');

		require_once(DUKT_VIDEOS_PATH.'libraries/app.php');

		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$this->EE->load->helper('url');
		

		
		// $this->lib->lang_load('dukt_videos');
		
		$this->app = new \DuktVideos\App;
		
		$this->services = $this->app->get_services();
	}

	// --------------------------------------------------------------------

	/**
	 * List accounts
	 *
	 * @access	public
	 * @return	string
	 */
	function index()
	{
		// page settings
		
		$this->app->insert_css_file('universal/css/mcp.css');

		$this->EE->cp->set_variable('cp_page_title', DUKT_VIDEOS_NAME);
	
	
		// build links
		
		$links = array();
		
		foreach($this->services as $service)
		{
			$links[$service->service_key]['enable'] = $this->app->cp_link('method=enable'.AMP.'service='.$service->service_key);
			$links[$service->service_key]['disable'] = $this->app->cp_link('method=disable'.AMP.'service='.$service->service_key);
			$links[$service->service_key]['configure'] = $this->app->cp_link('method=configure'.AMP.'service='.$service->service_key);
		}
		
		
		// assign variables for the view
		
		$vars['services'] = $this->services;
		
		$vars['links'] = $links;
		
		$vars['app'] = $this->app;
		
		return $this->EE->load->view('account/index', $vars, true);
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Configure
	 *
	 * @access	public
	 * @return	string
	 */
	public function configure()
	{
		$service_key = $this->lib->input_get('service');
		
		$service = $this->services[$service_key];
		
		
		$this->EE->cp->set_variable('cp_page_title', $this->app->lang_line('configure')." ".$service->service_name);
		$this->EE->cp->set_breadcrumb($this->app->cp_link(), DUKT_VIDEOS_NAME);
		

		
		
		// form open
		$form_open = form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dukt_videos'.AMP.'method=configure_save'.AMP.'service='.$service->service_key);
		$form_close = form_close();
		
		
		// build links
		
		$links = array();
		$links['back'] = $this->app->cp_link();
		$links['reset'] = $this->app->cp_link('method=reset'.AMP.'service='.$service->service_key);
		
		
		// assign variables for the view
		
		$vars['links'] = $links;
		$vars['services'] = $this->services;
		$vars['service'] = $service;
		$vars['dukt_videos'] = $this->app;
		$vars['form_open'] = $form_open;
		$vars['form_close'] = $form_close;
		$vars['lib'] = $this->lib;
		$vars['app'] = $this->app;
		
		
		// endpoint_url		
		
		$vars['endpoint_url'] = \DuktVideos\App::callback_url($service_key);;

		return $this->EE->load->view('account/configure', $vars, true);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Configure Save
	 *
	 * @access	public
	 * @return	string
	 */
	public function configure_save()
	{
		$services = $this->services;
		
		$service_key = $this->lib->input_get('service');
		
		$service = $this->services[$service_key];
		
		foreach($service->api_options as $k => $v)
		{
			if($this->lib->input_post($k) !== false)
			{
				
				$this->app->set_option($service_key, $k, $this->lib->input_post($k));
			}
		}
		
		$connect_url = $this->app->cp_link('method=connect'.AMP.'service='.$service->service_key);

		$this->EE->functions->redirect($connect_url);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Connect
	 *
	 * @access	public
	 * @return	string
	 */
	public function connect()
	{
		$service_key = $this->lib->input_get('service');
		
		$service = $this->services[$service_key];
		
		$service->connect($this->lib, $this->app);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Reset
	 *
	 * @access	public
	 * @return	string
	 */
	public function reset()
	{
		$services = $this->services;
		
		$service_key = $this->lib->input_get('service');
		
		$service = $this->services[$service_key];
		
		foreach($service->token_options as $k => $v)
		{		
			$this->app->set_option($service_key, $k, '');
		}
		
	    $redirect = $this->app->cp_link('method=configure'.AMP.'service='.$service->service_key);
		
		$this->EE->functions->redirect($redirect);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Enable
	 *
	 * @access	public
	 * @return	string
	 */
	public function enable()
	{
		$service_key = $this->lib->input_get('service');
		
		$this->app->set_option($service_key, 'enabled', 1);
		
		$redirect = $this->app->cp_link();
		
		$this->EE->functions->redirect($redirect);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Disable
	 *
	 * @access	public
	 * @return	string
	 */
	public function disable()
	{
		$service_key = $this->lib->input_get('service');
		
		$this->app->set_option($service_key, 'enabled', 0);

		$redirect = $this->app->cp_link();
		
		$this->EE->functions->redirect($redirect);
	}
	
	// --------------------------------------------------------------------
}

/* END Dukt_videos_mcp Class */

/* End of file mcp.dukt_videos.php */
/* Location: ./system/expressionengine/third_party/dukt_videos/mcp.dukt_videos.php */