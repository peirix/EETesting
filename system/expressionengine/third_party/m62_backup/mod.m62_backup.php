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
 * Backup Pro - Mod
 *
 * Module class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/mcp.m62_backup.php
 */
class M62_backup {

	public $return_data	= '';
	
	public function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		
		$this->db_conf = array(
					 'user' => $this->EE->db->username, 
					 'pass' => $this->EE->db->password,
					 'db_name' => $this->EE->db->database, 
					 'host' => $this->EE->db->hostname
		);
		
		$this->EE->load->model('m62_backup_settings_model', 'm62_backup_settings', TRUE);
		$this->EE->load->library('m62_backup_lib');
		$this->EE->load->library('m62_sql_backup');	
		$this->EE->load->library('logger');
		$this->EE->load->library('email');	
		$this->EE->load->helper('file');
		$this->EE->load->library('encrypt');
		$this->EE->load->library('javascript');
			
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		$this->EE->m62_backup_lib->set_backup_dir($this->settings['backup_store_location']);

		if($this->settings['max_db_backups'] > '0')
		{
			$this->EE->m62_backup_lib->cleanup_backup_count('database', $this->settings['max_db_backups']);
		}
		
		if($this->settings['max_file_backups'] > '0')
		{
			$this->EE->m62_backup_lib->cleanup_backup_count('files', $this->settings['max_file_backups']);
		}
				
		$this->total_space_used = $this->EE->m62_backup_lib->get_space_used();
		if($this->total_space_used > $this->settings['auto_threshold'])
		{
			$this->EE->m62_backup_lib->cleanup_auto_threshold_backups($this->settings['auto_threshold'], $this->total_space_used);
		}	
	}
	
	public function void()
	{
		
	}
	
	public function cron()
	{
		ini_set('memory_limit', -1);
		set_time_limit(3600); //limit the time to 1 hours

		$type = $this->EE->input->get_post('type');
		$this->EE->m62_backup_lib->set_db_info($this->db_conf);
		$path = $this->EE->m62_backup_lib->make_db_filename();	
		$backup_paths = array();
		switch($type)
		{
			case 'db':
			default:
				$backup_paths['db_backup'] = $this->EE->m62_sql_backup->backup($path, $this->db_conf);
			break;
			
			case 'files':
				$backup_paths['files_backup'] = $this->EE->m62_backup_lib->backup_files();
			break;
			
			case 'combined':
				$backup_paths['db_backup'] = $this->EE->m62_sql_backup->backup($path, $this->db_conf);
				$backup_paths['files_backup'] = $this->EE->m62_backup_lib->backup_files();
			break;
		}
		
		if(count($backup_paths) >= 1 && count($this->settings['cron_notify_emails']) >= 1)
		{
			$this->EE->m62_backup_lib->send_notification($backup_paths);
		}
	}
}