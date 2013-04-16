<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class Dukt_videos_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> DUKT_VIDEOS_NAME,
		'version'	=> DUKT_VIDEOS_VERSION
	);

	var $has_array_data = TRUE;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		parent::EE_Fieldtype();


		// load video player package

		$this->EE->load->add_package_path(PATH_THIRD . 'dukt_videos/');


		// prepare cache for head files

		if (! isset($this->EE->session->cache['dukt_videos']['head_files']))
		{
			$this->EE->session->cache['dukt_videos']['head_files'] = false;
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Loader for app, lib, services
	 *
	 * @access	public
	 * @return	array
	 */
	private function load_dukt_videos()
	{
		// load dukt videos
		
		require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/lib.php');
		require_once(DUKT_VIDEOS_PATH.'libraries/app.php');
		
		$this->lib = new \DuktVideos\Lib(array('basepath' => DUKT_VIDEOS_UNIVERSAL_PATH));
		
		$this->EE->load->helper('url');

		
		$this->app = new \DuktVideos\App;
		
		$this->services = $this->app->get_services();	
	}
	
	// --------------------------------------------------------------------

	/**
	 * Install Function
	 *
	 * @access	public
	 * @return	array
	 */
	public function install()
	{
		return array(
			'video_url'	=> '',
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Display Field
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function display_field($data)
	{
		return $this->_display_field($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Matrix Compatibility
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function display_cell($data)
	{
		return $this->_display_field($data, 'matrix');
	}

	// --------------------------------------------------------------------

	/**
	 * Common function for native and Matrix display field
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @return	string
	 */
	private function _display_field($data, $type=false)
	{
		$this->load_dukt_videos();
		
		$vars['lib'] = $this->lib;
		$vars['app'] = $this->app;
		$vars['services'] = $this->services;
		$vars['manage_link'] = false;
		
		$return = "";
		
		
		// include resources once
		
		if (!$this->EE->session->cache['dukt_videos']['head_files'])
		{
			$this->app->include_resources();

			$this->EE->session->cache['dukt_videos']['head_files'] = true;
		}
		
		
		// hidden field
		
		if($type == "matrix")
		{
			$vars['hidden_input'] = form_hidden($this->cell_name, $data);
		}
		else
		{
			$vars['hidden_input'] = form_hidden($this->field_name, $data);
		}
		
		
		// account exists
		
		$vars['any_account_exists'] = false;
		
		foreach($vars['services'] as $service)
		{
			if($service->enabled)
			{
				$vars['any_account_exists'] = true;
			}
		}
		
		
		// field view
		
		$return .= $this->EE->load->view('field/field', $vars, true);
		

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * {video} - Rendering Tag & Tag Pair
	 *
	 * @access	public
	 * @return	string
	 */
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if(empty($data))
		{
			return "";
		}
		
		$this->load_dukt_videos();
		
		
		// get video from url
		
		$video_url = $data;
		
		$video = $this->app->get_video($video_url, true);
		
		if(!$video)
		{
			$this->EE->TMPL->log_item("Dukt Videos : Couldn't get video");
			
			return false;
		}
		
		// get service

		$service = $this->services[$video['service_key']];
		
		if(!$service)
		{
			$this->EE->TMPL->log_item("Dukt Videos : Couln't get service");
			
			return false;
		}
		
		
		// get embed

		$embed = $service->get_embed($video['id'], $params);
		
		$video['embed'] = $embed;
		
		
		if($tagdata)
		{
			// rendering {video}{/video} pair

			if($video)
			{
				// date format
				
				if(isset($video['date']))
				{
					$local_date = $video['date'];
	
					if (preg_match_all("#".LD."date format=[\"|'](.+?)[\"|']".RD."#", $tagdata, $matches))
					{
						foreach ($matches['1'] as $match)
						{
							$tagdata = preg_replace("#".LD."date format=.+?".RD."#", $this->EE->localize->decode_date($match, $local_date), $tagdata, 1);
						}
					}
				}
			}

			$conditionals = $this->EE->functions->prep_conditionals($tagdata, $video);

			$out = $this->EE->functions->var_swap($conditionals, $video);
		}
		else
		{
			// rendering {video}

			if($video)
			{
				$out = $video['embed'];
			}
			else
			{
				$out = $video['error'];
			}
		}

		return $out;
	}
	
	// --------------------------------------------------------------------

	/**
	 * {video:single} Alias for replace_tag()
	 *
	 * @access	public
	 * @return	bool
	 */
	public function replace_single($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_tag($data, $params, $tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * {video:details} Alias for replace_tag()
	 *
	 * @access	public
	 * @return	bool
	 */
	public function replace_details($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_tag($data, $params, $tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * {video:pair} Alias for replace_tag()
	 *
	 * @access	public
	 * @return	bool
	 */
	public function replace_pair($data, $params = array(), $tagdata = FALSE)
	{
		return $this->replace_tag($data, $params, $tagdata);
	}

	// --------------------------------------------------------------------

}

// END Dukt_videos_ft class

/* End of file ft.dukt_videos.php */
/* Location: ./system/expressionengine/third_party/dukt_videos/ft.dukt_videos.php */