<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

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

class Dukt_videos_model extends CI_Model {

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->site_id = $this->config->item('site_id');
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get option
	 *
	 * @access	public
	 */
	public function get_option($service, $k)
	{
		$this->db->where('site_id', $this->site_id);
		$this->db->where('service', $service);
		$this->db->where('option_key', $k);
		
		$query = $this->db->get('dukt_videos_options');
		
		if ($query->num_rows() > 0)
		{
		   $row = $query->row(); 
		   
		   return $row->option_value;
		}
		
		return false;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set option
	 *
	 * @access	public
	 */
	public function set_option($service, $k, $v)
	{
		$data = array(
			'option_value' => $v
		);
		
		if($this->get_option($service, $k) !== false)
		{			
			$this->db->where('site_id', $this->site_id);
			$this->db->where('service', $service);
			$this->db->where('option_key', $k);

			$this->db->update('dukt_videos_options', $data);	
			
		}
		else
		{
			$data['site_id'] = $this->site_id;
			$data['service'] = $service;
			$data['option_key'] = $k;
			
			$this->db->insert('dukt_videos_options', $data);
		}
	}
	
	// --------------------------------------------------------------------
}

// END Videoplayer_model class

/* End of file videoplayer_model.php */
/* Location: ./system/expressionengine/third_party/videoplayer/models/videoplayer_model.php */