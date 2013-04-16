<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Updater Module
 *
 * @package			DevDemon_Updater
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Updater
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('updater');
		$this->EE->load->config('updater_config');
		$this->EE->load->library('firephp');
		$this->EE->load->library('updater_helper');
		$this->EE->load->library('updater_transfer');
		$this->settings = $this->EE->updater_helper->grab_settings();

		$this->EE->debug_updater = ($this->EE->config->item('updater_debug') == 'yes') ? TRUE : FALSE ;

		// Set the EE Cache Path? (hell you can override that)
		$this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';
	}

	// ********************************************************************************* //

	public function ACT_general_router()
	{
		@header('Access-Control-Allow-Origin: *');
		@header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');
        @header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        @header('Access-Control-Allow-Headers: Keep-Alive, Content-Type, User-Agent, Cache-Control, X-Requested-With, X-File-Name, X-File-Size');

        // -----------------------------------------
		// Increase all types of limits!
		// -----------------------------------------
		set_time_limit(0);

		if ($this->settings['infinite_memory'] == 'yes')
		{
			ini_set('memory_limit', -1);
		}

		@error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// IS AJAX?
		$IS_AJAX = $this->EE->updater_helper->is_ajax();

        // Only Super Admins can do this.
        if ($this->EE->session->userdata['group_id'] != 1)
        {
        	header("HTTP/1.0 403 Forbidden");
        	exit();
        }

        // Logged in user? But no AJAX call?
    	if (!$IS_AJAX)
    	{
    		show_error('This is the Updater action URL<br>
    			This message is only displayed because you are logged in as a Super Admin.<br>
    			Note: All further code execution has halted just in case.
    		');
    		exit();
    	}

        // Task
        $task = $this->EE->input->get('task');

        if (method_exists('Updater', $task) == FALSE)
        {
        	header("HTTP/1.0 403 Forbidden");
        	exit();
        }

        $this->path_root = FCPATH;
		$this->path_system = str_replace('expressionengine/', '', APPPATH);
		$this->path_themes = $this->EE->config->item('theme_folder_path');

        $this->{$task}();

        exit();
	}

	// ********************************************************************************* //

	public function cp_ajax_router($task)
	{
		// -----------------------------------------
		// Increase all types of limits!
		// -----------------------------------------
		set_time_limit(0);

		if ($this->settings['infinite_memory'] == 'yes')
		{
			ini_set('memory_limit', -1);
		}

		@error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// Execute
		$this->{$task}();
		exit();
	}

	// ********************************************************************************* //

	private function test_ajax_call()
	{
		 exit($this->EE->updater_helper->generate_json(array('success' => 'yes')));
	}

	// ********************************************************************************* //

	private function get_server_info($return=FALSE)
	{
		$out = array('server'=>array());

		// -----------------------------------------
		// Get Server Info
		// -----------------------------------------
		$out['server']['root'] = FCPATH;
		$out['server']['backup'] = FCPATH . 'site_backup/';
		$out['server']['system'] = str_replace('expressionengine/', '', APPPATH);
		$out['server']['system_third_party'] = $this->EE->updater_helper->get_thirdparty_path();
		$out['server']['themes'] = $this->EE->updater_helper->get_theme_path();
		$out['server']['themes_third_party'] = $this->EE->updater_helper->get_thirdparty_theme_path();

		if ($return) return $out['server'];

        exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function test_transfer_method()
	{
		$this->EE->load->library('updater_tests');

		$post_settings = $this->EE->input->post('settings');
		if (is_array($post_settings) == FALSE) $post_settings = array();
		$settings = $this->EE->updater_helper->array_extend($this->settings, $post_settings);

		if (isset($settings['file_transfer_method']) == FALSE) $settings['file_transfer_method'] = 'local';

		$this->EE->updater_tests->test_transfer_method($settings);

		exit();
	}

	// ********************************************************************************* //

	private function addon_process_file()
	{
		// -----------------------------------------
		// Remove Temp DIRS
		// -----------------------------------------
		$temp_path = $this->cache_path.'updater/';

		if (file_exists($temp_path) === TRUE)
		{
			$this->EE->load->helper('file');

			// Loop over all files
			$tempdirs = @scandir($temp_path);

			foreach ($tempdirs as $tempdir)
			{
				if ($tempdir == '.' OR $tempdir == '..') continue;
				if ( ($this->EE->localize->now - $tempdir) < 7200) continue;

				@chmod($temp_path.$tempdir, 0777);
				@delete_files($temp_path.$tempdir, TRUE);
				@rmdir($temp_path.$tempdir);
			}
		}


		//sleep(2); // Simulate long loading
		$this->EE->load->library('updater_addons');

		$this->EE->updater_addons->process_files();
	}

	// ********************************************************************************* //

	private function addon_move_files()
	{
		$out = array('success' => 'no', 'body' => '');

		// Be sure our key is there
		if (isset($_POST['addon']) === FALSE)
		{
			$out['body'] = 'Missing addon POST key';
			exit($this->EE->updater_helper->generate_json($out));
		}

		// Decode tje json
		$addon = $this->EE->updater_helper->decode_json($_POST['addon']);

		// Is it correct?
		if (isset($addon->name) === FALSE)
		{
			$out['body'] = 'Failed to decode Addon JSON';
			exit($this->EE->updater_helper->generate_json($out));
		}

		$update = TRUE;

		try
		{
			$this->EE->updater_transfer->init();

			// Transfer all system
			foreach ($addon->paths->system as $path)
			{
				$dirname = basename($path);
				$update = $this->EE->updater_transfer->dir_exists('system_third_party', $dirname);

				$this->EE->updater_transfer->mkdir('system_third_party', $dirname);
				$this->EE->updater_transfer->upload('system_third_party', $addon->root_location.$path, $dirname);
			}

			// Transfer all themes
			if (isset($addon->paths->themes) === TRUE && is_array($addon->paths->themes) === TRUE)
			{
				foreach ($addon->paths->themes as $path)
				{
					// Sometimes these just don't exists!
					if (file_exists($addon->root_location.$path) === FALSE) continue;
					$dirname = basename($path);

					$this->EE->updater_transfer->mkdir('themes_third_party', $dirname);
					$this->EE->updater_transfer->upload('themes_third_party', $addon->root_location.$path, $dirname);
				}
			}

			// Do we need to put something in root?
			if (isset($addon->paths->root) === TRUE && is_array($addon->paths->root) === TRUE)
			{
				foreach ($addon->paths->root as $path)
				{
					// Sometimes these just don't exists!
					if (file_exists($addon->root_location.$path) === FALSE) continue;

					$dirname = basename($path);
					$exists = $this->EE->updater_transfer->dir_exists('root', $dirname);

					// Why do we do this? Example:
					// BrilliantRetail uses /media dir to store product images
					if (!$exists)
					{
						$this->EE->updater_transfer->mkdir('root', $dirname);
						$this->EE->updater_transfer->upload('root', $addon->root_location.$path, $dirname);
					}
					else
					{
						$this->EE->updater_transfer->upload('root', $addon->root_location.$path, $dirname);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$out['body'] = $e->getMessage();
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// Force Database Backup First?
		// -----------------------------------------
		if (file_exists(PATH_THIRD."{$addon->name}/upd.{$addon->name}.php") === TRUE)
		{
			// Module installed?
			$query = $this->EE->db->select('module_version')->from('exp_modules')->where('module_name', ucfirst($addon->name) )->get();

			if ($query->num_rows() > 0)
			{
				$version = $query->row('module_version');

				require PATH_THIRD."{$addon->name}/upd.{$addon->name}.php";
				$class = ucfirst($addon->name.'_upd');
				$UPD = new $class();

				if (method_exists($UPD, 'database_backup_required') === TRUE)
				{
					$ret = $UPD->database_backup_required($version);

					if ($ret == TRUE)
					{
						$out['force_db_backup'] = 'yes';
					}
				}

				// -----------------------------------------
				// Update Notes?
				// -----------------------------------------
				if (isset($addon->update_notes) === TRUE && is_array($addon->update_notes) === TRUE)
				{
					$out['update_notes'] = array();

					foreach ($addon->update_notes as $note)
					{
						if (isset($note->version) === FALSE) continue;
						if (version_compare($note->version, $version, '>=') === TRUE)
						{
							$out['update_notes'][] = $note;
						}
					}
				}
			}
		}




		$out['update'] = $update ? 'yes' : 'no';

		$out['success'] = 'yes';
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function addon_install()
	{
		$out = array('success' => 'no', 'body' => '');

		$this->EE->load->library('updater_addons');
		$this->EE->updater_addons->install_update_addon();

		$out['queries'] = $this->EE->updater_addons->queries_executed;
		$out['success'] = 'yes';
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function remove_temp_dirs()
	{
		$this->EE->load->helper('file');
		$out = array('success' => 'no', 'body' => '');
		$key = $this->EE->input->post('key');

		$this->EE->updater_helper->delete_files($this->cache_path.'updater/'.$key.'/', TRUE);
		@rmdir($this->cache_path.'updater/'.$key.'/');

		$out['success'] = 'yes';
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function backup_database_prepare()
	{
        $out = array('success' => 'no', 'body' => '');

		$out['success'] = 'yes';
		$out['tables'] = $this->EE->db->list_tables();
		$out['tables'][] = $this->EE->lang->line('upload_final_dest');

		//$out['tables'] = array($this->EE->lang->line('upload_final_dest'));
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function backup_database()
	{
        $out = array('success' => 'no', 'body' => '');

        $this->EE->load->dbutil();
		$key = $this->EE->input->get_post('key');
		$table = $this->EE->input->post('table');

		// Does the temp directory exist?
		$path = $this->cache_path.'updater/'.$key.'/';

		if (@is_dir($path) === FALSE)
   		{
   			@mkdir($path, 0777, true);
   			@chmod($path, 0777);
   		}

   		// -----------------------------------------
		// Do we need to move the backup file
		// to it's final destination?
		// -----------------------------------------
		if ($table == $this->EE->lang->line('upload_final_dest'))
		{
			try
			{
				$this->EE->updater_transfer->init();
				$this->EE->updater_transfer->mkdir('backup', date('Y_m_d-Hi',$key).'/mysql');
				$this->EE->updater_transfer->upload('backup', PATH_THIRD.'updater/libraries/htaccess', '.htaccess', 'file');
				$this->EE->updater_transfer->upload('backup', $path.'backup.sql', date('Y_m_d-Hi',$key).'/mysql/backup.sql', 'file');
			}
			catch (Exception $e)
			{
				$out['body'] = $e->getMessage();
				exit($this->EE->updater_helper->generate_json($out));
			}

			$out['success'] = 'yes';
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// Write the table to the temp backup file
		// -----------------------------------------
		if ($this->EE->db->table_exists($table) == TRUE)
		{
			$options = array();
			$options['tables'][] = $table;
			$options['format'] = 'txt';
			$backup =& $this->EE->dbutil->backup($options);
			write_file($path.'backup.sql', $backup, FOPEN_READ_WRITE_CREATE);
		}

		$out['success'] = 'yes';
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function backup_files_prepare()
	{
        $out = array('success' => 'no', 'body' => '');

        $this->EE->load->helper('directory');
        $server_dirs = $this->get_server_info(TRUE);

		$codeigniter_map = directory_map(BASEPATH, 2);
		$ee_map = directory_map(APPPATH, 2);
		$third_party_map = directory_map($server_dirs['system_third_party'], 2);
		$themes_map = directory_map($server_dirs['themes'], 2);

		// Loop over all directories in the Codeigniter DIR
		foreach ($codeigniter_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			$out['dirs'][] = 'system/codeigniter/'.$dir;
		}

		// Loop over all directories in the EE system dir
		foreach ($ee_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			if ($dir == 'cache' || $dir == 'third_party') continue;
			$out['dirs'][] = 'system/expressionengine/'.$dir;
		}

		foreach ($third_party_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			$out['dirs'][] = 'system/expressionengine/third_party/'.$dir;
		}

		// Loop over all directories in the Themes dir
		foreach ($themes_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			$out['dirs'][] = 'themes/'.$dir;
		}

		$out['success'] = 'yes';

		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function backup_files()
	{
        $out = array('success' => 'no', 'body' => '');
        $server_dirs = $this->get_server_info(TRUE);
        $dir = $this->EE->input->post('dir');
        $key = $this->EE->input->post('key');

        // -----------------------------------------
		// Parse the Source Dir
		// -----------------------------------------
        $source_dir = '';

        if (strpos($dir, 'system/codeigniter') === 0)
        {
        	$source_dir = BASEPATH . str_replace('system/codeigniter/', '', $dir) . '/';
        }
        elseif (strpos($dir, 'system/expressionengine/third_party') === 0)
        {
        	$source_dir = PATH_THIRD . str_replace('system/expressionengine/third_party', '', $dir) . '/';
        }
        elseif (strpos($dir, 'system/expressionengine') === 0)
        {
        	$source_dir = APPPATH . str_replace('system/expressionengine/', '', $dir) . '/';
        }
        elseif (strpos($dir, 'themes/') === 0)
        {
        	$source_dir = $server_dirs['themes'] . str_replace('themes/', '', $dir) . '/';
        }
        else
        {
        	$out['body'] = 'Path not recognized!';
        	exit($this->EE->updater_helper->generate_json($out));
        }

        // Basedir
        $basedir = date('Y_m_d-Hi',$key).'/files/'.$dir;

        // -----------------------------------------
		// Copy Files!
		// -----------------------------------------
        try
		{
			$dirname = basename($source_dir);
			$this->EE->updater_transfer->init();
			$this->EE->updater_transfer->mkdir('backup', $basedir);
			$this->EE->updater_transfer->upload('backup', PATH_THIRD.'updater/libraries/htaccess', '.htaccess', 'file');
			$this->EE->updater_transfer->upload('backup', $source_dir, $basedir, 'dir', TRUE);
		}
		catch (Exception $e)
		{
			$out['body'] = $e->getMessage();
			exit($this->EE->updater_helper->generate_json($out));
		}

		$out['success'] = 'yes';
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function ee_process_file()
	{
		// -----------------------------------------
		// Remove Temp DIRS
		// -----------------------------------------
		$temp_path = $this->cache_path.'updater/';

		if (file_exists($temp_path) === TRUE)
		{
			$this->EE->load->helper('file');

			// Loop over all files
			$tempdirs = @scandir($temp_path);

			foreach ($tempdirs as $tempdir)
			{
				if ($tempdir == '.' OR $tempdir == '..') continue;
				if ( ($this->EE->localize->now - $tempdir) < 7200) continue;

				@chmod($temp_path.$tempdir, 0777);
				@delete_files($temp_path.$tempdir, TRUE);
				@rmdir($temp_path.$tempdir);
			}
		}


		//sleep(2); // Simulate long loading
		$this->EE->load->library('updater_ee');

		$this->EE->updater_ee->process_files();
	}

	// ********************************************************************************* //

	private function ee_backup_init()
	{
		$out = array('success' => 'no', 'body' => '');

		$action = $this->EE->input->get_post('action');
		$this->EE->load->library('updater_ee');

		// -----------------------------------------
		// Put site Offline
		// -----------------------------------------
		if ($action == 'site_offline')
		{
			$this->EE->config->_update_config(array('is_system_on' => 'n') );
			$out['success'] = 'yes';
			exit($this->EE->updater_helper->generate_json($out));
		}

		if ($action == 'copy_installer')
		{
			$this->EE->updater_ee->server = $this->get_server_info(TRUE);
			$this->EE->updater_ee->copy_installer();
		}

		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //



} // END CLASS

/* End of file mod.updater.php */
/* Location: ./system/expressionengine/third_party/updater/mod.updater.php */
