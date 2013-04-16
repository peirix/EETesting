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
 * Backup Pro - FTP Library
 *
 * FTP Library class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/libraries/M62_backup_ftp.php
 */
class M62_backup_ftp
{
	public $config = array();
	
	public $settings = array();
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		$this->EE->load->library('ftp');
		$this->settings['ftp_username'] = $this->EE->encrypt->decode($this->settings['ftp_username']);
		$this->settings['ftp_password'] = $this->EE->encrypt->decode($this->settings['ftp_password']);
				
		$this->config['hostname'] = $this->settings['ftp_hostname'];
		$this->config['username'] = trim($this->settings['ftp_username']);
		$this->config['password'] = trim($this->settings['ftp_password']);
		$this->config['port']     = $this->settings['ftp_port'];
		$this->config['passive']  = $this->settings['ftp_passive'];
		$this->config['debug']    = TRUE;		
	}	
	
	public function connect()
	{
		$this->EE->ftp->connect($this->config);	
	}
	
	public function test_connection($config)
	{
		$config['hostname'] = $config['ftp_hostname'];
		$config['username'] = trim($config['ftp_username']);
		$config['password'] = trim($config['ftp_password']);
		$config['port'] 	= $config['ftp_port'];
		$config['passive']  = $config['ftp_passive'];		
		$config['debug'] = TRUE;

		$this->EE->ftp->connect($config);
			
		$paths = $this->EE->ftp->list_files($config['ftp_store_location']);
		if(count($paths) == '0')
		{
			show_error($this->EE->lang->line('ftp_directory_missing'));
		}
		$this->close();	
	}
	
	public function move_backup($local, $type)
	{
		if($this->settings['ftp_store_location'] == '')
		{
			return FALSE;
		}
		$this->connect();
		$paths = $this->EE->ftp->list_files($this->settings['ftp_store_location']);
		$store_path = $this->settings['ftp_store_location'].'/'.$type;
		
		if(!in_array($type, $paths) && !in_array($store_path, $paths))
		{
			$this->EE->ftp->mkdir($store_path);
		}
		$remote = $store_path. '/'.basename($local);
		$this->EE->ftp->upload($local, $remote); 
		$this->close();	
	}
	
	public function remove_backups(array $files)
	{
		$this->connect();
		foreach($files AS $file)
		{
			$parts = explode(DIRECTORY_SEPARATOR, $file);
			if(count($parts) >= '1')
			{
				$filename = end($parts);
				$type = prev($parts);
				$remove = $this->settings['ftp_store_location'].'/'.$type.'/'.$filename;
				$paths = $this->EE->ftp->list_files($this->settings['ftp_store_location'].'/'.$type.'/');
				if(in_array($filename, $paths))
				{
					$this->EE->ftp->delete_file($remove);	
				}				
			}
		}
		$this->close();
	}
	
	public function close()
	{
		$this->EE->ftp->close(); 
	}
}