<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Backup Pro
 *
 * @package		mithra62:m62_backup
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/backup-pro/
 * @version		1.8.4
 * @filesource 	./system/expressionengine/third_party/m62_backup/
 */
 
 /**
 * Backup Pro - CP
 *
 * Control Panel class
 *
 * @package 	mithra62:m62_backup
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/m62_backup/mcp.m62_backup.php
 */
class M62_backup_mcp 
{
	public $url_base = '';
	
	/**
	 * The amount of pagination items per page
	 * @var int
	 */
	public $perpage = 10;
	
	/**
	 * The delimiter for the datatables jquery
	 * @var stirng
	 */
	public $pipe_length = 1;
	
	/**
	 * The name of the module; used for links and whatnots
	 * @var string
	 */
	private $mod_name = 'm62_backup';
	
	/**
	 * The name of the class for the module references
	 * @var string
	 */
	public $class = 'M62_backup';
	
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->db_conf = array(
					 'user' => $this->EE->db->username, 
					 'pass' => $this->EE->db->password,
					 'db_name' => $this->EE->db->database, 
					 'host' => $this->EE->db->hostname
		);

		//load EE stuff
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->library('encrypt');
		$this->EE->load->helper('form');
		$this->EE->load->library('logger');
		$this->EE->load->helper('file');
		
		$this->EE->load->model('m62_backup_settings_model', 'm62_backup_settings', TRUE);	
		$this->EE->load->library('m62_backup_js');
		$this->EE->load->library('m62_backup_lib', 'm62_backup');
		$this->EE->load->library('m62_sql_backup');	
		$this->settings = $this->EE->m62_backup_settings->get_settings();
		
		$this->query_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name.AMP.'method=';
		$this->url_base = BASE.AMP.$this->query_base;
		
		$this->EE->m62_backup_lib->set_url_base($this->url_base);
		$this->EE->m62_backup_lib->set_backup_dir($this->settings['backup_store_location']);
		$this->EE->m62_backup_lib->set_db_info($this->db_conf);
		$this->backup_location = $this->settings['backup_store_location'];
		$this->progress_tmp = $this->EE->m62_backup_lib->progress_log_file = $this->settings['backup_store_location'].'/progress.data';
		
		$this->EE->cp->set_variable('url_base', $this->url_base);
		$this->EE->cp->set_variable('query_base', $this->query_base);
		
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->mod_name, $this->EE->lang->line('m62_backup_module_name'));
		$this->EE->cp->set_right_nav($this->EE->m62_backup_lib->get_right_menu());	
		
		$method = $this->EE->input->get_post('method', TRUE);
		if($this->settings['max_db_backups'] > '0' && $method != 'progress')
		{
			$this->EE->m62_backup_lib->cleanup_backup_count('database', $this->settings['max_db_backups']);
		}
		
		if($this->settings['max_file_backups'] > '0' && $method != 'progress')
		{
			$this->EE->m62_backup_lib->cleanup_backup_count('files', $this->settings['max_file_backups']);
		}

		$this->total_space_used = $this->EE->m62_backup_lib->get_space_used();
		if($this->total_space_used > $this->settings['auto_threshold'] && $method != 'progress')
		{
			$this->EE->m62_backup_lib->cleanup_auto_threshold_backups($this->settings['auto_threshold'], $this->total_space_used);
		}		
		
		$this->errors = $this->EE->m62_backup_lib->error_check();
	}
	
	public function index()
	{			
		$vars = array();
		$vars['errors'] = $this->errors;
		$vars['paths'] = array(
							  'db' => $this->EE->m62_backup_lib->backup_db_dir, 
							  'files' => $this->EE->m62_backup_lib->backup_files_dir
		);
			
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('m62_backup_module_name'));
		
		$vars['backups'] = $this->EE->m62_backup_lib->get_backups();		
		
		$this->EE->javascript->output($this->EE->m62_backup_js->get_check_toggle());
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->m62_backup_js->get_accordian_css()); 

		$this->EE->jquery->tablesorter('#database_backups table', '{headers: {3: {sorter: false}}, widgets: ["zebra"], sortList: [[0,1]]}');  
		$this->EE->jquery->tablesorter('#file_backups table', '{headers: {3: {sorter: false}}, widgets: ["zebra"], sortList: [[0,1]]}');  
		
		$this->EE->javascript->compile();
		
		return $this->EE->load->view('index', $vars, TRUE); 
	}	
	
	public function backup()
	{
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('backup_in_progress'));
		
		$this->EE->m62_backup_lib->write_progress_log('Backup Started', 1, 0);
		$type = $this->EE->input->get_post('type', TRUE);
		$proc_url = FALSE;
		switch($type)
		{
			case 'backup_db':
				$proc_url = $this->url_base.'backup_db';
			break;
			case 'backup_files':
				$proc_url = $this->url_base.'backup_files';
			break;
		}
		
		if(!$proc_url)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('can_not_backup'));
			$this->EE->functions->redirect($this->url_base.'index');			
			exit;
		}
		
		$this->EE->cp->add_js_script('ui', 'progressbar'); 
		$this->EE->javascript->output('$("#progressbar").progressbar({ value: 0 });'); 
		$this->EE->javascript->output($this->EE->m62_backup_js->get_backup_progressbar($proc_url, $this->url_base));
		
		$this->EE->javascript->compile();

		$vars = array('proc_url' => $proc_url);
		return $this->EE->load->view('backup', $vars, TRUE);
	}
	
	public function progress()
	{
		die(file_get_contents($this->progress_tmp));
	}	
	
	public function backup_db()
	{
		ini_set('memory_limit', -1);
		set_time_limit(3600); //limit the time to 1 hours		
		$path = $this->EE->m62_backup_lib->make_db_filename();
		if($this->EE->m62_sql_backup->backup($path, $this->db_conf))
		{	
			$this->EE->logger->log_action($this->EE->lang->line('log_database_backup'));	
			exit;
		}	
	}
	
	public function backup_files()
	{
		//some systems have a low(ish) memory limit so we have to remove that.
		ini_set('memory_limit', -1);
		set_time_limit(3600); //limit the time to 1 hours

		if($this->EE->m62_backup_lib->backup_files())
		{
			$this->EE->logger->log_action($this->EE->lang->line('log_file_backup'));
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('file_backup_created'));
			$this->EE->functions->redirect($this->url_base.'index');
			exit;
		}
		else
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('file_backup_failure'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}		
	}
	
	public function download_backup()
	{
		$file_name = base64_decode($this->EE->input->get_post('id', TRUE));
		$type = $this->EE->input->get_post('type');
		if($type == 'files')
		{
			$file = $this->backup_location.'/files/'.$file_name;
		}
		else
		{
			$file = $this->backup_location.'/database/'.$file_name;	
		}
		
		if(file_exists($file))
		{
			$this->EE->logger->log_action($this->EE->lang->line('log_backup_downloaded'));
			$new_name = $this->EE->m62_backup_lib->make_pretty_filename($file_name, $type);
			$this->EE->m62_backup_lib->file_download($file, $new_name);
			exit;
		}
		else
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('db_backup_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}
	}
	
	public function delete_backup_confirm()
	{
		$backups = $this->EE->input->get_post('toggle', TRUE);
		if(!$backups || count($backups) == 0)
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('backups_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}
		
		$ids = array();
		$i = 0;
		foreach($backups AS $backup)
		{
			$file = base64_decode($backup);
			$ids[$i]['path'] = $file;
			$ids[$i]['type'] = (substr($file, 0, 5) == 'files' ? 'files' : 'database');
			$parts = explode('/', $file);
			$ids[$i]['details'] = $this->EE->m62_backup_lib->parse_filename($parts['1'], $ids[$i]['type']);
			$i++;
		}

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('delete_backup'));
		$this->EE->cp->set_variable('download_delete_question', $this->EE->lang->line('delete_backup_confirm'));
		
		$vars = array();
		$vars['form_action'] = $this->query_base.'delete_backups';
		$vars['damned'] = $ids;
		return $this->EE->load->view('delete_confirm', $vars, TRUE);
	}
	
	public function delete_backups()
	{
		$backups = $this->EE->input->get_post('delete', TRUE);
		$remove_s3 = $this->EE->input->get_post('remove_s3', FALSE);
		$remove_cf = $this->EE->input->get_post('remove_cf', FALSE);
		$remove_ftp = $this->EE->input->get_post('remove_ftp', FALSE);
		
		$backups = array_map('base64_decode',$backups);
		if($this->EE->m62_backup_lib->delete_backups($backups, $remove_ftp, $remove_s3, $remove_cf))
		{
			$this->EE->logger->log_action($this->EE->lang->line('log_backup_deleted'));
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('backups_deleted'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;			
		}
		
		$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('backup_delete_failure'));
		$this->EE->functions->redirect($this->url_base.'index');
		exit;	
				
	}
	
	public function restore_db_confirm()
	{
		$backup = base64_decode($this->EE->input->get_post('id', TRUE));
		$file = $this->backup_location.'/database/'.$backup;
		if(!file_exists($file))
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('db_backup_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;	
		}		
		
		$vars = array();
		$vars['backup_details'] = $this->EE->m62_backup_lib->parse_filename($backup, 'database');
		$vars['backup'] = base64_encode($backup);
		$vars['form_action'] = $this->query_base.'restore_db';
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('restore_db'));
		return $this->EE->load->view('restore_confirm', $vars, TRUE);
	}

	public function restore_db()
	{
		$path = $this->backup_location.'/database/'.base64_decode($this->EE->input->get_post('restore_db', TRUE));
		if(!file_exists($path))
		{
			$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('db_backup_not_found'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;				
		}
		
		$tmp = $this->backup_location.'/database/tmp';
		if(!file_exists($tmp))
		{
			mkdir($tmp);
		}
				
		$path = $this->EE->m62_backup_lib->unzip_db_backup($path, $tmp);	
		if($this->EE->m62_sql_backup->restore($path, $this->db_conf))
		{
			$this->EE->m62_backup_lib->delete_dir($tmp);
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('database_restored'));
			$this->EE->functions->redirect($this->url_base.'index');	
			exit;	
		}
	}
	
	public function settings()
	{
		if(isset($_POST['go_settings']))
		{
			if(!isset($_POST['cron_attach_backups']))
			{
				$_POST['cron_attach_backups'] = '0';	
			}
			
			if(!isset($_POST['ftp_passive']))
			{
				$_POST['ftp_passive'] = '0';
			}

			if(isset($_POST['ftp_hostname']) && $_POST['ftp_hostname'] != '')
			{
				$this->EE->load->library('m62_backup_ftp');
				$this->EE->m62_backup_ftp->test_connection($_POST);
			}
			
			if(isset($_POST['s3_access_key']) && $_POST['s3_access_key'] != '' && isset($_POST['s3_secret_key']) && $_POST['s3_secret_key'] != '')
			{
				$this->EE->load->library('m62_backup_s3');
				$this->EE->m62_backup_s3->test_connection($_POST);
			}

			if(isset($_POST['cf_username']) && $_POST['cf_username'] != '' && isset($_POST['cf_api']) && $_POST['cf_api'] != '')
			{
				$this->EE->load->library('m62_backup_cf');
				$this->EE->m62_backup_cf->test_connection($_POST);
			}
			
			if($this->EE->m62_backup_settings->update_settings($_POST))
			{	
				$this->EE->logger->log_action($this->EE->lang->line('log_settings_updated'));
				$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('settings_updated'));
				$this->EE->functions->redirect($this->url_base.'settings');		
				exit;			
			}
			else
			{
				$this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('settings_update_fail'));
				$this->EE->functions->redirect($this->url_base.'settings');	
				exit;					
			}
		}
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('settings'));
		
		$this->EE->cp->add_js_script('ui', 'accordion'); 
		$this->EE->javascript->output($this->EE->m62_backup_js->get_accordian_css()); 	
		$this->EE->javascript->output($this->EE->m62_backup_js->get_settings_form());
		$this->EE->javascript->compile();	

		$vars = array();
		$vars['errors'] = $this->errors;
		$vars['paths'] = array(
							  'db' => $this->EE->m62_backup_lib->backup_db_dir, 
							  'files' => $this->EE->m62_backup_lib->backup_files_dir
		);
		
		$this->settings['ftp_username'] = $this->EE->encrypt->decode($this->settings['ftp_username']);
		$this->settings['ftp_password'] = $this->EE->encrypt->decode($this->settings['ftp_password']);
		$this->settings['s3_access_key'] = $this->EE->encrypt->decode($this->settings['s3_access_key']);
		$this->settings['s3_secret_key'] = $this->EE->encrypt->decode($this->settings['s3_secret_key']);
		$this->settings['cf_username'] = $this->EE->encrypt->decode($this->settings['cf_username']);
		$this->settings['cf_api'] = $this->EE->encrypt->decode($this->settings['cf_api']);
		
		$vars['settings'] = $this->settings;
		$vars['total_space_used'] = $this->EE->m62_backup_lib->filesize_format($this->total_space_used);
		$vars['member_groups'] = $this->EE->m62_backup_settings->get_member_groups();
		$vars['cron_commands'] = $this->EE->m62_backup_lib->get_cron_commands($this->class);

		$vars['settings_disable'] = FALSE;
		$vars['threshold_options'] = $this->EE->m62_backup_lib->get_threshold_options();
		if(isset($this->EE->config->config['m62_backup']))
		{
			$vars['settings_disable'] = 'disabled="disabled"';
		}			
		return $this->EE->load->view('settings', $vars, TRUE);
	}
}