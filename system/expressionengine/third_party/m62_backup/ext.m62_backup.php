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
 * Backup Pro - Extension
 *
 * Extension class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/ext.m62_backup.php
 */
class M62_backup_ext 
{

	public $settings = array();
	
	public $name = 'Backup Pro';
	
	public $version = '1.8.5';
	
	public $description	= 'Extensions for Backup Pro';
	
	public $settings_exist	= 'y';
	
	public $docs_url = ''; 
	
	public $required_by = array('module');	
		
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct()
	{
		$this->EE =& get_instance();	
		$this->EE->lang->loadfile('m62_backup');		
	}
	
	public function settings_form()
	{
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=m62_backup'.AMP.'method=settings');
	}
	
	public function get_backups($api, $method = FALSE)
	{
		//this is very important!! 
		//without the extension gets ran on every call
		if(__FUNCTION__ != $api->method)
		{
			return;
		}
		
		//$this->EE->load->add_package_path(PATH_THIRD.'export_it/'); 
		$this->EE->load->model('m62_backup_settings_model', 'm62_backup_settings', TRUE);
		
		$this->EE->load->library('m62_backup_lib', 'm62_backup');
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		$this->EE->m62_backup_lib->set_backup_dir($this->settings['backup_store_location']);
		
		echo json_encode($this->EE->m62_backup_lib->get_backups());
		$this->EE->extensions->end_script = TRUE;
	}

	public function activate_extension() 
	{
		return TRUE;

	}
	
	public function update_extension($current = '')
	{
		return TRUE;
	}

	public function disable_extension()
	{
		return TRUE;

	}
}