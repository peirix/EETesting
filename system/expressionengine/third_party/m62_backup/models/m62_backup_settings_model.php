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
 * Backup Pro - Settings Model
 *
 * Settings Model class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/models/M62_backup_settings_model.php
 */
class M62_backup_settings_model extends CI_Model
{
	private $_table = 'm62_backup_settings';
	
	public $_defaults = array(
						'allowed_access_levels' => '',
						'auto_threshold' => '0',
						'auto_threshold_custom' => '',
						'exclude_paths' => '',
						'enable_cron' => '0',
						'cron_notify_emails' => '',
						'cron_attach_backups' => '0',
						'cron_attach_threshold' => '0',
						'ftp_hostname' => 'ftp.serverbart.com',
						'ftp_username' => 'backup@serverbart.com',
						'ftp_password' => 'bart1999',
						'ftp_debug' => '0',
						'ftp_port' => '21',
						'ftp_passive' => '0',
						'ftp_store_location' => '',
						'cron_attach_threshold' => '0',
						'license_number' => 'dffca26a-cbab-4694-92c7-f0b97f2e737f',
						'backup_store_location' => '',
						'backup_file_location' => '',
						's3_access_key' => '',
						's3_secret_key' => '',
						's3_bucket' => '',
						'cf_username' => '',
						'cf_api' => '',
						'cf_bucket' => '',
						'cf_location' => 'us',
						'max_file_backups' => '0',
						'max_db_backups' => '0',
						'date_format' => '%M %d, %Y, %h:%i:%s%A'
	);
	
	private $_serialized = array(
						'cron_notify_emails',
						'exclude_paths',
						'backup_file_location'
	);
	
	private $_encrypted = array(
						'ftp_username',
						'ftp_password',
						's3_access_key',
						's3_secret_key',
						'cf_username',
						'cf_api'
	);	
	
	/**
	 * The options available for scheduling
	 * @var array
	 */
	public $auto_threshold_options = array(
			'0' => 'Disabled',
			'104857600' => '100MB',
			'262144000' => '250MB',
			'524288000' => '500MB',
			'786432000' => '750MB',
			'1073741824' => '1GB',
			'5368709120' => '5GB',
			'10737418240' => '10GB',
			'custom' => 'Custom'
	);	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('encrypt');
		
		//check for Apache
		if(isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'] != '')
		{
			$this->_defaults['backup_file_location'] = realpath($_SERVER['DOCUMENT_ROOT']);
		}
		else //stupid IIS fucking things up for us again. 
		{
			if(isset($_SERVER['SCRIPT_FILENAME']))
			{
				$path = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
			}
			elseif(isset($_SERVER['PATH_TRANSLATED']))
			{
				$path = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
			}

			$this->_defaults['backup_file_location'] = realpath($path);
		}

		$this->_defaults['backup_store_location'] = realpath(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'backups');
	}
	
	/**
	 * Adds a setting to the databse
	 * @param string $setting
	 */
	public function add_setting($setting)
	{
		$data = array(
		   'setting_key' => $setting,
		   'setting_value' => ''
		);
		
		return $this->db->insert($this->_table, $data); 
	}	
	
	public function get_settings()
	{
		$this->db->select('setting_key, setting_value, `serialized`');
		$query = $this->db->get($this->_table);	
		$_settings = $query->result_array();
		$settings = array();	
		foreach($_settings AS $setting)
		{
			$settings[$setting['setting_key']] = ($setting['serialized'] == '1' ? unserialize($setting['setting_value']) : $setting['setting_value']);
		}
		
		//now check to make sure they're all there and set default values if not
		foreach ($this->_defaults as $key => $value)
		{	
			//setup the override check
			if(isset($this->config->config['m62_backup'][$key]))
			{
				$settings[$key] = $this->config->config['m62_backup'][$key];
				if(in_array($key, $this->_encrypted) && $settings[$key] != '')
				{
					$settings[$key] = $this->encrypt->encode($settings[$key]);
				}				
			}
						
			if(!isset($settings[$key]))
			{
				$settings[$key] = $value;
			}
		}		

		if($settings['backup_file_location'] == '')
		{
			$settings['backup_file_location'] = $this->_defaults['backup_file_location'];
		}
		
		if($settings['backup_store_location'] == '')
		{
			$settings['backup_store_location'] = $this->_defaults['backup_store_location'];
		}

		$settings['max_file_backups'] = (int)$settings['max_file_backups'];
		$settings['max_db_backups'] = (int)$settings['max_db_backups'];

		return $settings;
	}
	
	/**
	 * Returns the value straigt from the database
	 * @param string $setting
	 */
	public function get_setting($setting)
	{
		return $this->db->get_where($this->_table, array('setting_key' => $setting))->result_array();
	}	
	
	public function update_settings(array $data)
	{
		$this->load->library('encrypt');
		foreach($data AS $key => $value)
		{
			
			if(in_array($key, $this->_serialized))
			{
				$value = explode("\n", $value);
				
				//hack to remove bad email addresses from list
				if($key == 'cron_notify_emails')
				{
					$temp = array();
					foreach($value AS $email)
					{
						if($this->m62_backup_lib->check_email($email))
						{
							$temp[] = $email;
						}						
					}
					$value = $temp;
				}				
			}
			
			if(in_array($key, $this->_encrypted) && $value != '')
			{
				$value = $this->encrypt->encode($value);
			}
			
			$this->update_setting($key, $value);
		}
		
		return TRUE;
	}
	
	/**
	 * Updates the value of a setting
	 * @param string $key
	 * @param string $value
	 */
	public function update_setting($key, $value)
	{
		if(!$this->_check_setting($key))
		{
			return FALSE;
		}

		$data = array();
		if(is_array($value))
		{
			$value = serialize($value);
			$data['serialized '] = '1';
		}
		
		$data['setting_value'] = $value;
		$this->db->where('setting_key', $key);
		$this->db->update($this->_table, $data);
		
	}

	/**
	 * Verifies that a submitted setting is valid and exists. If it's valid but doesn't exist it is created.
	 * @param string $setting
	 */
	private function _check_setting($setting)
	{
		if(array_key_exists($setting, $this->_defaults))
		{
			if(!$this->get_setting($setting))
			{
				$this->add_setting($setting);
			}
			
			return TRUE;
		}		
	}	
	
	public function get_member_groups()
	{
		$this->db->select('group_title , group_id')->where('group_id != 1');
		$query = $this->db->get('member_groups');	
		$_groups = $query->result_array();	
		$groups = array();
		$groups[''] = '';
		foreach($_groups AS $group)
		{
			$groups[$group['group_id']] = $group['group_title'];
		}
		return $groups;
	}
	
}