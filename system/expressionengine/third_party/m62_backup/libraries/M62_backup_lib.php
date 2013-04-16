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

if(!class_exists('PclZip62'))
{
	/**
	 * Setup M62_Pclzip
	 */
	include_once 'pclzip.lib.php';
}

 /**
 * Backup Pro - Base Library
 *
 * Base Library class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/libraries/M62_backup_lib.php
 */
class M62_backup_lib
{
	/**
	 * Preceeds URLs 
	 * @var mixed
	 */
	private $url_base = FALSE;
	
	/**
	 * String to seperate database filenames between parts
	 * @var string
	 */
	private $name_sep = '@@';
	
	/**
	 * The full path to the main backup directory
	 * @var string
	 */
	public $backup_dir;
	
	/**
	 * The full path to the database backup directory
	 * @var string
	 */
	public $backup_db_dir;
	
	/**
	 * The full path to the database files directory
	 * @var string
	 */
	public $backup_files_dir;
	
	/**
	 * The full path to the log file for the progress bar
	 * @var string
	 */
	public $progress_log_file;
	
	
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		$this->EE->load->helper('utilities');
	}
	
	public function get_threshold_options()
	{
		return $this->EE->m62_backup_settings->auto_threshold_options;
	}
	
	/**
	 * Sets up the right menu options
	 * @return multitype:string
	 */
	public function get_right_menu()
	{
		$menu = array(
				'index'			=> $this->url_base.'index',
				'backup_db'		=> $this->url_base.'backup&type=backup_db',
				'backup_files'	=> $this->url_base.'backup&type=backup_files'
		);
		
		if($this->EE->session->userdata('group_id') == '1' || (isset($this->settings['allowed_access_levels']) && is_array($this->settings['allowed_access_levels'])))
		{
			if($this->EE->session->userdata('group_id') == '1' || in_array($this->EE->session->userdata('group_id'), $this->settings['allowed_access_levels']))
			{
				$menu['settings'] = $this->url_base.'settings';
			}
		}
		return $menu;
	}
	
	/**
	 * Sets the backup directories using $path as a seed
	 * @param string $path
	 */
	public function set_backup_dir($path)
	{
		$this->backup_dir = $path;
		$this->backup_db_dir = $path.DIRECTORY_SEPARATOR .'database';
		$this->backup_files_dir = $path.DIRECTORY_SEPARATOR .'files';
	}
	
	/**
	 * Wrapper that runs all the tests to ensure system stability
	 * @return array;
	 */
	public function error_check()
	{
		$errors = $this->check_backup_dirs();
		if($this->settings['license_number'] == '')
		{
			$errors['license_number'] = 'missing_license_number';
		}
		return $errors;
	}
	
	/**
	 * Runs the tests to make sure the backup directories exist and are writable
	 * @return array;
	 */
	public function check_backup_dirs()
	{
		$errors = array();
		$index = dirname(__FILE__).'/../index.html';
		if(!file_exists($this->backup_db_dir))
		{
			if(!mkdir($this->backup_db_dir))
			{
				$errors[] = 'db_dir_missing';
			}
			else
			{
				@copy($index, $this->backup_db_dir.'/index.html');
			}			
		}
		elseif(!is_writable($this->backup_db_dir))
		{
			$errors[] = 'db_dir_not_writable';
		}
		
		if(!file_exists($this->backup_files_dir))
		{
			if(!mkdir($this->backup_files_dir))
			{
				$errors[] = 'files_dir_missing';
			}
			else
			{
				@copy($index, $this->backup_files_dir.'/index.html');
			}
		}
		elseif(!is_writable($this->backup_files_dir))
		{
			$errors[] = 'files_dir_not_writable';
		}		
		
		return $errors;
	}	
	
	/**
	 * Returns all the existing backups on the file system
	 * @return array
	 */
	public function get_backups()
	{
		$data = array('database' => array(), 'files' => array());
		$ignore = array('.svn', 'index.html', 'tmp', '..', '.');
		
		if(file_exists($this->backup_db_dir))
		{		
			$d = dir($this->backup_db_dir);
			while (false !== ($entry = $d->read())) 
			{
				if(!in_array($entry, $ignore))
				{
					$file_data = $this->parse_filename($entry, 'database');
					$data['database'][$file_data['file_date_raw']] = $file_data;
				}
			}
			krsort($data['database'], SORT_NUMERIC);
		}
		
		if(file_exists($this->backup_files_dir))
		{			
			$d = dir($this->backup_files_dir);
			while (false !== ($entry = $d->read())) 
			{
				if(!in_array($entry, $ignore))
				{
					$file_data = $this->parse_filename($entry, 'files');
					$data['files'][$file_data['file_date']] = $file_data;
				}
			}
			krsort($data['files'], SORT_NUMERIC);
		}

		return $data;
	}
	
	public function get_ignore_files()
	{
		$paths = array($this->backup_files_dir);
		$total = count($paths);
		for($i = 0; $i < $total; $i++)
		{
			$paths[$i] = str_replace("\\", "/", $paths[$i]);
		}
		return $paths;
	}
	public function directory_to_array($directory, $recursive) 
	{
		$array_items = array();
		$directory = preg_replace("/\/\//si", "/", $directory);
		$ignore = $this->get_ignore_files();
		
		if ($handle = opendir($directory)) 
		{
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != "..") 
				{
					if(in_array($file, $ignore) || in_array($directory. '/' . $file, $ignore)  || in_array($directory, $ignore))
					{
						continue;
					}
					if (is_dir($directory. '/' . $file)) 
					{
						if($recursive) 
						{
							$array_items = array_merge($array_items, $this->directory_to_array($directory. '/' . $file, $recursive));
						}
						$file = $directory . '/' . $file;
						$array_items[] = preg_replace("/\/\//si", "/", $file);
					} 
				}
			}
			closedir($handle);
		}
		return $array_items;
	}
	
	public function backup_files()
	{
		$path = $this->make_file_filename();
		$zip = new PclZip62($path);
		$total_items = 1;
		if($this->settings['s3_access_key'] != '' && $this->settings['s3_secret_key'] != '')
		{
			$total_items++;
		}

		if($this->settings['cf_api'] != '' && $this->settings['cf_username'] != '')
		{
			$total_items++;
		}		
		
		if($this->settings['ftp_hostname'] != '')
		{
			$total_items++;
		}			
		$this->write_progress_log(lang('backup_progress_bar_start'), $total_items, 0);
		
		$this->settings['exclude_paths'][] = $this->backup_files_dir;		
		$zip->set_exclude($this->settings['exclude_paths']);
		$zip->total_files = $total_items;
		
		if ($this->EE->extensions->active_hook('m62_backup_file_backup_start') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_file_backup_start', $this->settings['backup_file_location']);
			if ($this->EE->extensions->end_script === TRUE) return;
		}
					
		if ($zip->create($this->settings['backup_file_location'], PCLZIP_OPT_REMOVE_PATH, realpath($_SERVER['DOCUMENT_ROOT'].'/../').'/') == 0) 
		{
			return FALSE;
		}
		
		if ($this->EE->extensions->active_hook('m62_backup_file_backup_stop') === TRUE)
		{
			$this->EE->extensions->call('m62_backup_file_backup_stop', $path);
			if ($this->EE->extensions->end_script === TRUE) return;
		}		
		
		$total_items = $zip->total_files;
	    if($this->settings['s3_access_key'] != '' && $this->settings['s3_secret_key'] != '')
		{
			$zip->total_files++;
			$this->write_progress_log(lang('backup_progress_bar_start_s3'), $zip->total_files, $total_items);
			$this->EE->load->library('m62_backup_s3');
			$this->EE->m62_backup_s3->move_backup($path, 'files');
			$this->write_progress_log(lang('backup_progress_bar_stop_s3'), $zip->total_files, $total_items);
			$total_items++;
		}	
		
		if($this->settings['cf_api'] != '' && $this->settings['cf_username'] != '')
		{
			$zip->total_files++;
			$this->write_progress_log(lang('backup_progress_bar_start_cf'), $zip->total_files, $total_items);
			$this->EE->load->library('m62_backup_cf');
			$this->EE->m62_backup_cf->move_backup($path, 'files');
			$this->write_progress_log(lang('backup_progress_bar_stop_cf'), $zip->total_files, $total_items);
			$total_items++;
		}

		if($this->settings['ftp_hostname'] != '')
		{
			$zip->total_files++;
			//$total_items++;
			$this->write_progress_log(lang('backup_progress_bar_start_ftp'), $zip->total_files, $total_items);			
			$this->EE->load->library('m62_backup_ftp');
			$this->EE->m62_backup_ftp->move_backup($path, 'files');		
			$this->write_progress_log(lang('backup_progress_bar_stop_ftp'), $zip->total_files, $total_items);	
			$total_items++;		
		}		
		
		$this->write_progress_log(lang('backup_progress_bar_stop'), $zip->total_files, $zip->total_files);
		return $path;	
	}
	
	public function get_space_used()
	{
		$amount = 0;
		$backups = $this->get_backups();		
		foreach($backups AS $type => $_backup)
		{
			if(is_array($_backup))
			{
				foreach($_backup AS $backup)
				{
					if(isset($backup['file_size_raw']))
					{
						$amount = ($amount+(int)$backup['file_size_raw']);
					}
				}
			}
		}
		
		if($amount > 0)
		{
			return $amount;
		}
	}
	
	public function parse_filename($name, $type)
	{
		$path = ($type == 'files' ? $this->backup_files_dir : $this->backup_db_dir).'/'.$name;
		if($type == 'files')
		{
			$data['file_date_raw'] = (int)$name;
			$data['file_date'] = m62_convert_timestamp($data['file_date_raw']);
		}
		else
		{
			$parts = explode($this->name_sep, $name);
			$data = array();
			$data['file_date_raw'] = $parts['0'];
			$data['file_date'] = m62_convert_timestamp($data['file_date_raw']);
		}
		$data['file_name'] = $name;
		$data['file_size'] = $this->filesize_format(filesize($path));	
		$data['file_size_raw'] = filesize($path);
		return $data;
	}
	

	/**
	 * Creates the full path to store the backup for the database.
	 * @param mixed $filename
	 */
	public function make_db_filename($filename = FALSE)
	{
		if(!$filename)
		{
			return $this->backup_dir.'/database/'.mktime().$this->name_sep.$this->db_info['db_name'].'.sql';
		}
		else
		{
			return $this->backup_dir.'/database/'.$filename;
		}
	}
	
	/**
	 * Creates the full path to store the backup for the filesystem
	 * @param $filename
	 */
	public function make_file_filename($filename = FALSE)
	{
		if(!$filename)
		{
			return $this->backup_dir.'/files/'.mktime().'.zip';
		}
		else
		{
			return $this->backup_dir.'/files/'.$filename;
		}
	}
	
	public function make_pretty_filename($file, $type)
	{
		$name = utf8_decode($this->EE->config->config['site_name']);
		$parts = $this->parse_filename($file, $type);
		switch($type)
		{
			case 'files':
				$name .= ' File Backup ';
			break;
			
			case 'db':
			default:
				$name .= ' Database Backup ';
			break;
		}
		
		$name .= date('YmdHis', $parts['file_date_raw']);
		return str_replace(' ', '_', strtolower($name)).'.zip';
	}		
	
	public function get_cron_commands($module_name)
	{
		$action_id = $this->get_cron_action($module_name);
		$url = $this->EE->config->config['site_url'].'?ACT='.$action_id;
		return array(
					 'file_backup' => 'wget "'.$url.AMP.'type=files"',
					 'db_backup' => 'wget "'.$url.AMP.'type=db"',
					 'combined' => 'wget "'.$url.AMP.'type=combined"',
		);
	}
	
	public function get_cron_action($module_name)
	{
		$this->EE->load->dbforge();
		$this->EE->db->select('action_id');
		$query = $this->EE->db->get_where('actions', array('class' => $module_name, 'method' => 'cron'));		
		return $query->row('action_id');
	}
	
	/**
	 * Wrapper to handle CP URL creation
	 * @param string $method
	 */
	public function _create_url($method)
	{
		return $this->url_base.$method;
	}

	/**
	 * Creates the value for $url_base
	 * @param string $url_base
	 */
	public function set_url_base($url_base)
	{
		$this->url_base = $url_base;
	}
	
	public function delete_backups(array $backups, $remove_ftp = TRUE, $remove_s3 = TRUE, $remove_cf = TRUE)
	{
		$removed = array();
		foreach($backups AS $backup)
		{
			$file = $this->backup_dir.'/'.$backup;
			if(file_exists($file))
			{				
				$removed[] = realpath($file);
				unlink($file);
			}
		}	
		
		if(count($removed) >= 1 && $this->settings['cf_api'] != '' && $this->settings['cf_username'] != '' && $remove_cf)
		{
			$this->EE->load->library('m62_backup_cf');
			$this->EE->m62_backup_cf->remove_backups($removed);				
		}

		if(count($removed) >= 1 && $this->settings['s3_access_key'] != '' && $this->settings['s3_secret_key'] != '' && $remove_s3)
		{
			$this->EE->load->library('m62_backup_s3');
			$this->EE->m62_backup_s3->remove_backups($removed);				
		}
				
		if(count($removed) >= 1 && $this->settings['ftp_hostname'] != '' && $remove_ftp)
		{
			$this->EE->load->library('m62_backup_ftp');
			$this->EE->m62_backup_ftp->remove_backups($removed);				
		}
		
		return TRUE;
	}

	public function valid_license($license)
	{
		return preg_match("/^([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})$/", $license);
	}
	
	/**
	 * Creates the value for $db_info
	 * @param string $db_info
	 */
	public function set_db_info($info)
	{
		$this->db_info = $info;
	}
	
	public function unzip_db_backup($path, $save)
	{
		$zip = zip_open($path);
		if ($zip) {
		  while ($zip_entry = zip_read($zip)) 
		  {
		  	$name = zip_entry_name($zip_entry);
		    $fp = fopen($save."/".zip_entry_name($zip_entry), "w");
		    if (zip_entry_open($zip, $zip_entry, "r")) 
		    {
		      $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
		      fwrite($fp,"$buf");
		      zip_entry_close($zip_entry);
		      fclose($fp);
		    }
		  }
		  zip_close($zip);
		  return $save.'/'.$name;
		}	
	}
	
	/**
	 * Removes the oldest backups to keep the space under $max_size
	 * @param int $max_size
	 * @param int $used_size
	 */
	public function cleanup_auto_threshold_backups($max_size, $used_size)
	{
		if($max_size == '0')
		{
			return FALSE;
		}
		
		if($max_size == 'custom')
		{
			$max_size = $this->settings['auto_threshold_custom'];
		}
		
		$backups = $this->get_backups();
		$arr = array();
		if(count($backups) >= '1')
		{
			foreach($backups AS $type => $items)
			{
				$path = ($type == 'database' ? $this->backup_db_dir : $this->backup_files_dir);
				foreach($items AS $backup)
				{
					$arr[$backup['file_date_raw']] = $type.'/'.$backup['file_name'];
				}
			}
		}

		ksort($arr);
		$i = 0;
		$remove = array();
		while($this->get_space_used() > $max_size)
		{
			$file = array_shift($arr);
			if($file != '')
			{
				$remove = array();
				$remove[] = $file;
				$this->delete_backups($remove, TRUE, TRUE, TRUE);
			}
			//unlink($remove);
			$i++;
			if($i > 10) //just a little sanity check :)
			{
				break;
			}
		}	
	}
	
	public function cleanup_backup_count($type = 'database', $total = '0')
	{
		$total = (int)$total;
		if($total == '0')
		{
			return FALSE;
		}
		
		//get the backups and clean things up for processing
		$backups = $this->get_backups();
		$arr = array();
		if(count($backups[$type]) >= '1')
		{
			$path = ($type == 'database' ? $this->backup_db_dir : $this->backup_files_dir);
			foreach($backups[$type] AS $backup)
			{
				$arr[$backup['file_date_raw']] = $type.'/'.$backup['file_name'];
			}			
		}

		
		if(count($arr) < $total)
		{
			return;
		}
		
		//check if we need to remove any for 
		$count = (count($arr)-$total);
		$i = 1;
		ksort($arr);
		$remove = array();
		foreach($arr AS $backup)
		{
			if($count >= $i)
			{
				$remove[] = $backup;
			}
			else
			{
				break;
			}
			$i++;
		}
		
		if(count($remove) >= '1')
		{
			$this->delete_backups($remove, TRUE, TRUE, TRUE);
		}
	}

	public function send_notification($backup_paths)
	{
		$to = array();
		$this->EE->lang->loadfile('m62_backup');
		foreach($this->settings['cron_notify_emails'] AS $email)
		{
			if($this->check_email($email))
			{
				$to[] = $email;
			}
		}
		
		if(count($to) == '0')
		{
			return FALSE;
		}
		

		$this->EE->email->from($this->EE->config->config['webmaster_email'], $this->EE->config->config['site_name']);
		$this->EE->email->to($to);
		$this->EE->email->subject($this->EE->config->config['site_name'].' '.lang('cron_notification'));

		$attachments = array();
		if($this->settings['cron_attach_backups'] == '1')
		{
			foreach($backup_paths AS $key => $path)
			{
				if(file_exists($path) && ((filesize($path) < $this->settings['cron_attach_threshold']) || $this->settings['cron_attach_threshold'] == '0'))
				{
					//make pretty files
					if($key == 'db_backup')
					{
						$type = 'db';
					}
					else
					{
						$type = 'files';
					}
					
					$new_name = dirname($path).'/../'.$this->make_pretty_filename(basename($path), $type);
					if(copy($path, $new_name))
					{
						$this->EE->email->attach($new_name);
						$attachments[] = $new_name;
					}
				}
			}
		}
		
		$message = lang('cron_message');
		$this->EE->email->message($message);
		
		$this->EE->email->send();
		if(count($attachments) >= '1')
		{
			foreach($attachments AS $file)
			{
				if(file_exists($file))
				{
					@unlink($file);
				}
			}
		}		
	}
	
	/**
	 * Writes out the progress log for the progress bar status updates
	 * @param string $msg
	 * @param int $total_items
	 * @param int $item_number
	 */
	public function write_progress_log($msg, $total_items = 0, $item_number = 0)
	{
		if($item_number > $total_items)
		{
			$item_number = $total_items;
		}
		
		$log = array('total_items' => $total_items, 'item_number' => $item_number, 'msg' => $msg);
		write_file($this->progress_log_file, $this->EE->javascript->generate_json($log));
	}
	
	/**
	 * Removes the progress log
	 */
	public function remove_progress_log()
	{	
		delete_files($this->progress_log_file);
	}	

	/**
	 * Format a number of bytes into a human readable format.
	 * Optionally choose the output format and/or force a particular unit
	 *
	 * @param   int     $bytes      The number of bytes to format. Must be positive
	 * @param   string  $format     Optional. The output format for the string
	 * @param   string  $force      Optional. Force a certain unit. B|KB|MB|GB|TB
	 * @return  string              The formatted file size
	 */
	public function filesize_format($val, $digits = 3, $mode = "SI", $bB = "B"){ //$mode == "SI"|"IEC", $bB == "b"|"B"
	
		$si = array("", "k", "M", "G", "T", "P", "E", "Z", "Y");
		$iec = array("", "Ki", "Mi", "Gi", "Ti", "Pi", "Ei", "Zi", "Yi");
		switch(strtoupper($mode)) {
			case "SI" : 
				$factor = 1000; 
				$symbols = $si; 
			break;
			case "IEC" : 
				$factor = 1024; 
				$symbols = $iec; 
			break;
			default : 
				$factor = 1000; 
				$symbols = $si; 
			break;
		}
		switch($bB) {
			case "b" : 
				$val *= 8; 
			break;
			default : 
				$bB = "B"; 
			break;
		}
		for($i=0;$i<count($symbols)-1 && $val>=$factor;$i++) {
			$val /= $factor;
		}
		$p = strpos($val, ".");
		if($p !== false && $p > $digits) {
			$val = round($val);
		} elseif($p !== false) { 
			$val = round($val, $digits-$p);
		}
		
		return round($val, $digits) . " " . $symbols[$i] . $bB;
	}	

	/**
	 * Given the full system path to a file it will force the "Save As" dialogue of browsers
	 *
	 * @param   string  $filename	Path to file
	 */
	public function file_download($filename, $force_name = FALSE)
	{
		// required for IE, otherwise Content-disposition is ignored
		if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');
	
		// addition by Jorg Weske
		$file_extension = strtolower(substr(strrchr($filename,"."),1));
	
		if( $filename == "" )
		{
			echo "<html><body>ERROR: download file NOT SPECIFIED.</body></html>";
			exit;
		} elseif ( ! file_exists( $filename ) )
		{
			echo "<html><body>ERROR: File not found. </body></html>";
			exit;
		};
		switch( $file_extension )
		{
			case "pdf": $ctype="application/pdf"; break;
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "doc": $ctype="application/msword"; break;
			case "xls": $ctype="application/vnd.ms-excel"; break;
			case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
			case "gif": $ctype="image/gif"; break;
			case "png": $ctype="image/png"; break;
			case "rtf": $ctype="text/rtf"; break;
			case "jpeg":
			case "jpg": $ctype="image/jpg"; break;
			default: $ctype="application/zip";
		}

		$filesize = filesize($filename);	
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
		header("Content-Type: $ctype");	
		// change, added quotes to allow spaces in filenames, by Rajkumar Singh
		header("Content-Disposition: attachment; filename=\"".($force_name ? $force_name : basename($filename))."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$filesize);

		if ($fd = fopen ($filename, "r")) 
		{
		    while(!feof($fd)) {
		        $buffer = fread($fd, 1024*8);
		        echo $buffer;
		    }
		}
		fclose ($fd);
		exit();
	}	
	
	public function check_email($email)
	{
		if(function_exists('filter_var'))
		{
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}
		else
		{
			return $this->valid_email($email);
		}
	}
	
	public function valid_email($email)
	{
		$this->EE->load->helper('email');
		return valid_email($email);
	}	

	/**
	 * Deletes a directory with all of its contents
	 * Works recursively to remove additional directories
	 *
	 * @param   string	$dirName	The directory to remove
	 * @return  bool
	 */
	function delete_dir($dirName) 
	{

		if(empty($dirName)) 
		{
			return FALSE;
		}

		
		if(file_exists($dirName)) 
		{
			$dir = dir($dirName);
			while($file = $dir->read()) 
			{
				if($file != '.' && $file != '..' ) 
				{
					if(is_dir($dirName.'/'.$file)) 
					{
						$this->delete_dir($dirName.'/'.$file);
					} 
					else 
					{
						unlink($dirName.'/'.$file);
					}
				}
			}
			rmdir($dirName.'/'.$file);
		} 
		else
		{
			return FALSE;
		}
		
		return TRUE;		
	}	
}