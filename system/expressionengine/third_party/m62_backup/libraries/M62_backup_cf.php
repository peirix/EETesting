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
 * Backup Pro - Cloud Files Library
 *
 * Cloud Files Library class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/libraries/M62_backup_cf.php
 */
class M62_backup_cf
{
	public $config = array();
	
	public $settings = array();
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		$this->EE->load->library('cf/cfiles');
		$this->settings['cf_username'] = $this->EE->encrypt->decode($this->settings['cf_username']);
		$this->settings['cf_api'] = $this->EE->encrypt->decode($this->settings['cf_api']);
		$this->settings['cf_location'] = $this->settings['cf_location'];
	}	
	
	public function connect()
	{
		return $this->EE->cfiles->initialize(array('cf_username' => $this->settings['cf_username'], 'cf_api' => $this->settings['cf_api']));
	}
	
	public function test_connection(array $config)
	{
		$this->EE->cfiles->api_location = $config['cf_location'];
		try
		{
			$this->EE->cfiles->initialize(array('cf_username' => $config['cf_username'], 'cf_api' => $config['cf_api']));
		}
		catch (Exception $e)
		{
			show_error($this->EE->lang->line('cf_connect_fail'));
		}
	}
	
	public function make_bucket_name()
	{
		return str_replace(' ', '_', strtolower($this->EE->config->config['site_label'].' Backup'));
	}
	
	public function move_backup($local, $type)
	{
		$this->EE->cfiles->api_location = $this->settings['cf_location'];
		$this->connect();
		if($this->settings['cf_bucket'] == '')
		{
			$this->settings['cf_bucket'] = $this->make_bucket_name();
		}
		
		//check for bucket
		try 
		{
			$this->EE->cfiles->cf_container = $this->settings['cf_bucket'];
			$bucket_data = $this->EE->cfiles->container_info();
		}
		catch(Exception $e)
		{
			//add bucket
			$bucket_data = array();
			$this->EE->cfiles->cf_container = $this->settings['cf_bucket'];
			$this->EE->cfiles->do_container('a');
		}

		$file_name = basename($local);
		$file_location = dirname($local).'/';
		$this->EE->cfiles->cf_folder = $type.'/';
		$this->EE->cfiles->do_object('a', $file_name, $file_location);
	}
	
	public function remove_backups(array $files)
	{
		$this->EE->cfiles->api_location = $this->settings['cf_location'];
		$this->connect();
		if($this->settings['cf_bucket'] == '')
		{
			$this->settings['cf_bucket'] = $this->make_bucket_name();
		}

		try 
		{
			$this->EE->cfiles->cf_container = $this->settings['cf_bucket'];
			$bucket_data = $this->EE->cfiles->container_info();
		}
		catch(Exception $e)
		{
			//add bucket
			$bucket_data = array();
			$this->EE->cfiles->cf_container = $this->settings['cf_bucket'];
			$this->EE->cfiles->do_container('a');
		}
				
		foreach($files AS $file)
		{
			$parts = explode(DIRECTORY_SEPARATOR, $file);
			if(count($parts) >= '1')
			{
				$filename = end($parts);
				$type = prev($parts);
				$remove = $type.'/'.$filename;
				$this->EE->cfiles->do_object('d', $remove);			
			}
		}
	}
}