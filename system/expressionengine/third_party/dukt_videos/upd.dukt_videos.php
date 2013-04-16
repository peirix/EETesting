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

class Dukt_videos_upd {

	var $version = DUKT_VIDEOS_VERSION;

	/**
	 * Construct
	 *
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{
		$this->EE->load->dbforge();
		

		// module infos

		$data = array(
			'module_name' => 'Dukt_videos',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);

		$this->EE->db->insert('modules', $data);


		// action : ajax

		$data = array(
			'class'		=> 'Dukt_videos' ,
			'method'	=> 'ajax'
		);

		$this->EE->db->insert('actions', $data);
		
		
		// action : youtube_callback

		$data = array(
			'class'		=> 'Dukt_videos' ,
			'method'	=> 'callback'
		);

		$this->EE->db->insert('actions', $data);
		
		
		// create add-on tables
		
		$this->create_tables();

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');

		$query = $this->EE->db->get_where('modules', array('module_name' => 'Dukt_videos'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');


		// remove actions

		$this->EE->db->where('class', 'Dukt_videos');
		$this->EE->db->delete('actions');


		// remove module

		$this->EE->db->where('module_name', 'Dukt_videos');
		$this->EE->db->delete('modules');
		
		
		// destroy add-on tables
		
		$this->destroy_tables();

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current='')
	{	
		// is current
		
		if ($current == $this->version)
		{
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create add-on tables
	 *
	 * @access	private
	 * @return	void
	 */
	private function create_tables()
	{
		$this->EE->load->dbforge();

		$fields = array(
            'id' => array(
							'type' => 'INT',
							'constraint' => 5,
							'unsigned' => TRUE,
							'auto_increment' => TRUE
						),
            'site_id' => array(
							'type' => 'INT',
							'constraint' => 5,
							'unsigned' => TRUE
						),
			'service' => array(
								'type' => 'varchar',
								'constraint' => 30,
								'null' => TRUE,
								'default' => NULL
							),
			'option_key' => array(
								'type' => 'varchar',
								'constraint' => 200,
								'null' => TRUE,
								'default' => NULL
							),
			'option_value' => array(
								'type' => 'text'
							)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('dukt_videos_options');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Destroy tables
	 *
	 * @access	private
	 * @return	void
	 */
	private function destroy_tables()
	{
		if($this->EE->db->table_exists('dukt_videos_options'))
		{
			$this->EE->dbforge->drop_table('dukt_videos_options');	
		}
	}
	
}
/* END Dukt_videos_upd Class */

/* End of file upd.dukt_videos.php */
/* Location: ./system/expressionengine/third_party/dukt_videos/upd.dukt_videos.php */