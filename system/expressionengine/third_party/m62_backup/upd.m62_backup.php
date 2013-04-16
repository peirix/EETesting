<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Backup Pro
 *
 * @package		mithra62:m62_backup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/backup-pro/
 * @version		1.8.2
 * @filesource 	./system/expressionengine/third_party/m62_backup/
 */
 
 /**
 * Backup Pro - Update Class
 *
 * Update class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/upd.m62_backup.php
 */
class M62_backup_upd { 

    public $version = '1.8.6'; 
    
    public $name = 'M62_backup';
    
    public $class = 'M62_backup';
    
    public $settings_table = 'm62_backup_settings';
     
    public function __construct() 
    { 
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
    } 
    
	public function install() 
	{
		$this->EE->load->dbforge();
	
		$data = array(
			'module_name' => $this->name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
	
		$this->EE->db->insert('modules', $data);
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'cron')";
		$this->EE->db->query($sql);

		$this->add_settings_table();
		$this->activate_extension();
		
		return TRUE;
	} 
	
	public function activate_extension()
	{
		$data = array();
		$data[] = array(
					'class'      => 'M62_backup_ext',
					'method'    => 'get_backups',
					'hook'  => 'export_it_api_start',
				
					'settings'    => '',
					'priority'    => 1,
					'version'    => $this->version,
					'enabled'    => 'y'
		);
	
		foreach($data AS $ex)
		{
			$this->EE->db->insert('extensions', $ex);	
		}		
	}
	
	
	private function add_settings_table()
	{
		$this->EE->load->dbforge();
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'	=> 10,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'setting_key'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '30',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'setting_value'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),
						'serialized' => array(
											'type' => 'int',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->settings_table, TRUE);		
	}

	public function uninstall()
	{
		$this->EE->load->dbforge();
	
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->class));
	
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
	
		$this->EE->db->where('module_name', $this->class);
		$this->EE->db->delete('modules');
	
		$this->EE->db->where('class', $this->class);
		$this->EE->db->delete('actions');
		
		$this->EE->dbforge->drop_table($this->settings_table);
		
		$this->disable_extension();
	
		return TRUE;
	}
	
	public function disable_extension()
	{
		$this->EE->db->where('class', 'M62_backup_ext');
		$this->EE->db->delete('extensions');
	}	

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}	
		
		if($current >= '1.8.1')
		{
			$this->update_extension();
		}

		if ($current < 1.3)
		{
			$this->add_settings_table();	
			$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'cron')";
			$this->EE->db->query($sql);			
		}
		
		return TRUE;
	}

	public function update_extension()
	{
		$this->activate_extension();
	}
    
}