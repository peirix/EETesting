<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Automat:ee
 *
 * @package		mithra62:Automatee
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/automat-ee/
 * @version		1.2
 * @filesource 	./system/expressionengine/third_party/automatee/
 */
 
 /**
 * Automat:ee - Update Class
 *
 * Update class
 *
 * @package 	mithra62:Automatee
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/automatee/upd.automatee.php
 */
class Automatee_upd 
{ 

    public $version = '1.2'; 
    
    public $name = 'Automatee';
    
    public $class = 'Automatee';
    
    public $settings_table = 'automatee_settings';
    
    public $crons_table = 'automatee_crons';
     
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
		
		$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'image_bug')";
		$this->EE->db->query($sql);		

		$this->add_settings_table();
		$this->add_crons_table();
		
		return TRUE;
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
											'null'			=> FALSE,
											'default'		=> ''
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
	
	private function add_crons_table()
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
						'name'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'active' => array(
											'type' => 'tinyint',
											'constraint' => 1,
											'null' => TRUE,
											'default' => '0'
										),
						'schedule'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),	
						'type'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),																																																												
						'command'  => array(
											'type' 			=> 'text',
											'null'			=> FALSE,
											'default'		=> ''
										),										
						'cron_plugin'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
										
						'cron_module'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'cron_method'	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'ran_at'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 11,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'total_runs'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 10,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'last_run_status'	=> array(
											'type' 			=> 'int',
											'constraint'	=> 1,
											'null'			=> FALSE,
											'default'		=> '0'
										),
						'last_modified'	=> array(
											'type' 			=> 'datetime'
										),
						'created_date'	=> array(
											'type' 			=> 'datetime'
						)										
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table($this->crons_table, TRUE);	

		$data = array(
					 'name' => 'Module Example Cron', 
					 'active' => '0', 
					 'schedule' => '0,30 * * * *', 
					 'type' => 'module', 
					 'command' => '', 
					 'cron_plugin' => '', 
					 'cron_module' => 'automatee', 
					 'cron_method' => 'example',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 	

		$data = array(
					 'name' => 'CLI Example and Custom Schedule', 
					 'active' => '0', 
					 'schedule' => '3 3,21-23,10 * * *', 
					 'type' => 'cli', 
					 'command' => '/path/to/shell/script.sh', 
					 'cron_plugin' => '', 
					 'cron_module' => '', 
					 'cron_method' => '',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 	

		$data = array(
					 'name' => 'Get URL Example', 
					 'active' => '0', 
					 'schedule' => '0,30 * * * *', 
					 'type' => 'url', 
					 'command' => 'http://google.com', 
					 'cron_plugin' => '', 
					 'cron_module' => '', 
					 'cron_method' => '',
					 'ran_at' => time(),
		   			 'last_modified' => date('Y-m-d H:i:s'),
					 'created_date' => date('Y-m-d H:i:s')
		);
		
		$this->EE->db->insert($this->crons_table, $data); 			
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
		$this->EE->dbforge->drop_table($this->crons_table);
	
		return TRUE;
	}

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}
		
		if($current < 1.2)
		{
			$sql = "INSERT INTO exp_actions (class, method) VALUES ('".$this->name."', 'test_cron')";
			$this->EE->db->query($sql);				
		}
		
		return TRUE;
	}	
    
}