<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dupdater extends CI_Controller {

	/**
	 * Constructor
	 *
	 * Sets some base values
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		// -----------------------------------------
		// Increase all types of limits!
		// -----------------------------------------
		@set_time_limit(0);
		@error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		@header('Access-Control-Allow-Origin: *');
		@header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');
        @header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        @header('Access-Control-Allow-Headers: Keep-Alive, Content-Type, User-Agent, Cache-Control, X-Requested-With, X-File-Name, X-File-Size');

        try
		{
	        // Load Config
	        @include (EE_APPPATH.'config/config.php');
			@include (EE_APPPATH.'config/database.php');

			$this->config->config = array_merge($this->config->config, $config);

			// Load the DB
			$this->load->database($db[$active_group], FALSE, TRUE);
			$this->db->save_queries	= TRUE;

			// Lets test DB connection!
			$this->db->list_tables();

			$this->init();
		}
		catch (Exception $e)
		{
			$out = array('success' => 'no', 'body' => '');
			$out['body'] = $e->getMessage();
			exit($this->generate_json($out));
		}
	}

	//********************************************************************************* //

	public function index()
	{
		// If it's an ajax request we will need to fail here!
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) === TRUE &&  $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
        {
        	$out = array('success' => 'no', 'body' => '');
        	exit ( $this->generate_json($out) );
        }

        $data = array();
		$data['dupdater'] = $this;

        // Else lets show our stuff
        $this->load->view('index', $data);
	}

	//********************************************************************************* //

	private function init()
	{
		// Parse Server Paths
        $this->server = $this->decode_json($this->input->post('server'));

        // We can't rely on "$this->server->system_third_party" to be available.
        // So we manually need to do this.
        if ($this->config->item('third_party_path'))
		{
			if (defined('EE_PATH_THIRD') === FALSE) define('EE_PATH_THIRD', rtrim(realpath($this->config->item('third_party_path')), '/').'/');
		}
		else
		{
			if (defined('EE_PATH_THIRD') === FALSE) define('EE_PATH_THIRD', EE_APPPATH.'third_party/');
		}

        // Load Updater Libraries
        $this->load->add_package_path(EE_PATH_THIRD . 'updater/');
        $this->load->helper('directory');

        $this->EE =& get_instance();
        $this->EE->load->config('updater_config');
        $this->EE->debug_updater = ($this->EE->config->item('updater_debug') == 'yes') ? TRUE : FALSE ;

        $this->EE->load->library('firephp');
		$this->EE->load->library('updater_helper');
		$this->EE->load->library('updater_transfer');

		$this->EE->updater_transfer->init();

		// Store the path maps
		$this->maps = $this->EE->updater_transfer->map;

        // Set the EE Cache Path? (hell you can override that)
        $this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : EE_APPPATH.'cache/';

        // Settings
        $this->settings = $this->EE->updater_helper->grab_settings();

        if ($this->settings['infinite_memory'] == 'yes')
        {
            ini_set('memory_limit', -1);
        }
	}

	//********************************************************************************* //

	public function update_ee()
	{
		//$this->EE->db->query("SELECT * FROM foo_table"); // Trigger DB error

		$out = array('success' => 'no', 'body' => '');
		$key = $this->input->post('key');
		$version = $this->input->post('version');
		$temp_dir = $this->cache_path.'updater/'.$key.'/';

		// -----------------------------------------
		// Upgrade!
		// -----------------------------------------
		if (file_exists($temp_dir.'system/installer/updates/ud_'.$version.'.php') === FALSE)
   		{
   			$out['body'] = 'Install file not found!';
			exit($this->generate_json($out));
   		}

   		$this->load->add_package_path($temp_dir.'system/installer/');
   		$this->load->library('progress');
   		$this->load->library('layout');

   		$file = $temp_dir.'system/installer/updates/ud_'.$version.'.php';
   		require($file);

   		$this->db->queries = array();
   		$UP = new Updater();
   		$UP->do_update();

   		// Update the APP Version!
   		$this->config->_update_config(array('app_version' => $version) );

   		$out['queries'] = $this->db->queries;
		$out['success'] = 'yes';
		exit ( $this->generate_json($out) );
	}

	//********************************************************************************* //

    public function update_modules()
    {
        $out = array('success' => 'no', 'body' => '');
        $native_modules = array('blacklist', 'channel', 'comment', 'commerce',
        'email', 'emoticon', 'file', 'forum', 'gallery', 'ip_to_nation',
        'jquery', 'mailinglist', 'member', 'metaweblog_api', 'moblog', 'pages',
        'query', 'referrer', 'rss', 'rte', 'safecracker', 'search',
        'simple_commerce', 'stats', 'updated_sites', 'wiki');


        $this->EE->db->select('module_name, module_version');
        $query = $this->EE->db->get('modules');

        // Clean it up
        $this->db->queries = array();

        foreach ($query->result() as $row)
        {
            $module = strtolower($row->module_name);

            /*
             * - Send version to update class and let it do any required work
             */
            if (in_array($module, $native_modules))
            {
                $path = EE_APPPATH.'/modules/'.$module.'/';
            }
            else
            {
                continue; // FOR NOW, SKIP THIRD PARTY MODULES!
                $path = EE_PATH_THIRD.$module.'/';
            }

            // Just in case lets define it!
            if (defined('PATH_THIRD') === FALSE) define('PATH_THIRD', EE_PATH_THIRD);

            if (file_exists($path.'upd.'.$module.EXT))
            {
                $class = ucfirst($module).'_upd';

                if ( ! class_exists($class))
                {
                    require $path.'upd.'.$module.EXT;
                }

                $UPD = new $class;
                $UPD->_ee_path = EE_APPPATH;

                if ($UPD->version > $row->module_version && method_exists($UPD, 'update') && $UPD->update($row->module_version) !== FALSE)
                {
                    $this->EE->db->update('modules', array('module_version' => $UPD->version), array('module_name' => ucfirst($module)));
                }
            }
        }

        $out['queries'] = $this->db->queries;
        $out['success'] = 'yes';
        exit ( $this->generate_json($out) );
    }

    //********************************************************************************* //

	public function copy_files_prepare()
	{
		$out = array('success' => 'yes', 'body' => '');
		$key = $this->input->post('key');
		$temp_dir = $this->cache_path.'updater/'.$key.'/';

		// -----------------------------------------
		// We don't need these..
		// -----------------------------------------
		@unlink($temp_dir.'system/expressionengine/config/config.php');
		@unlink($temp_dir.'system/expressionengine/config/database.php');

		copy(EE_APPPATH.'config/config.php', $temp_dir.'system/expressionengine/config/config.php');
		copy(EE_APPPATH.'config/database.php', $temp_dir.'system/expressionengine/config/database.php');

		$codeigniter_map = directory_map($temp_dir.'system/codeigniter/system/', 2);
		$ee_map = directory_map($temp_dir.'system/expressionengine/', 2);
		$themes_map = directory_map($temp_dir.'themes/', 2);

		// Loop over all directories in the Codeigniter DIR
		foreach ($codeigniter_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			if ($dir == 'cache' || $dir == 'logs') continue;
			if (strpos($dir, '_OLD') !== FALSE) continue;
			$out['dirs'][] = 'system/codeigniter/system/'.$dir;
		}

		// Loop over all directories in the EE system dir
		foreach ($ee_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;

			if ($dir == 'modules' OR $dir == 'third_party')
			{
				$modules_map = directory_map($temp_dir.'system/expressionengine/'.$dir.'/', 2);

				foreach ($modules_map as $module_dir => $module_arr)
				{
					if (is_array($module_arr) === FALSE) continue;
					$out['dirs'][] = 'system/expressionengine/'.$dir.'/'.$module_dir;
				}

				continue;
			}

			if ($dir == 'cache' || $dir == 'templates') continue;
			if (strpos($dir, '_OLD') !== FALSE) continue;
			$out['dirs'][] = 'system/expressionengine/'.$dir;
		}

		// Loop over all directories in the Themes dir
		foreach ($themes_map as $dir => $arr)
		{
			if (is_array($arr) === FALSE) continue;
			if ($dir == 'third_party') continue;
			if (strpos($dir, '_OLD') !== FALSE) continue;
			$out['dirs'][] = 'themes/'.$dir;
		}


		exit($this->generate_json($out));

		$out['success'] = 'yes';
		return $out;
	}

	//********************************************************************************* //

	public function copy_files()
	{
		$out = array('success' => 'no', 'body' => '');
        $dir = $this->EE->input->post('dir');
        $key = $this->EE->input->post('key');
        $temp_dir = $this->cache_path.'updater/'.$key.'/';

        // -----------------------------------------
		// Parse the Source Dir
		// -----------------------------------------
        $source_dir = '';
        $basedir = '';
        $location = 'system';

        if (strpos($dir, 'system/codeigniter') === 0)
        {
        	$source_dir = $temp_dir.$dir;
        	$basedir = 'codeigniter/system/';
        	$location = 'system';
        }
        elseif (strpos($dir, 'system/expressionengine') === 0)
        {
        	$source_dir = $temp_dir.$dir;
        	$basedir = 'expressionengine/';
        	if (strpos($dir, '/third_party/') !== FALSE) $basedir = 'expressionengine/third_party/';
        	if (strpos($dir, '/modules/') !== FALSE) $basedir = 'expressionengine/modules/';

        	$location = 'system';
        }
        elseif (strpos($dir, 'themes/') === 0)
        {
        	$source_dir = $temp_dir.$dir;
        	$basedir = '';
        	$location = 'themes';
        }
        else
        {
        	$out['body'] = 'Path not recognized!';
        	exit($this->EE->updater_helper->generate_json($out));
        }

        // -----------------------------------------
		// Copy Files!
		// -----------------------------------------
        try
		{
			$dirname = basename($source_dir);

			/*
			$exists = $this->EE->updater_transfer->dir_exists($location, $basedir.$dirname);

			$exists_old = $this->EE->updater_transfer->dir_exists($location, $basedir.$dirname.'_OLD');
			if ($exists_old) $this->EE->updater_transfer->delete($location, $basedir.$dirname.'_OLD', 'dir');

			if ($exists) $this->EE->updater_transfer->rename($location, $basedir.$dirname, $basedir.$dirname.'_OLD');
			 */
			$this->EE->updater_transfer->mkdir($location, $basedir.$dirname);
			$this->EE->updater_transfer->upload($location, $source_dir, $basedir.$dirname, 'dir', TRUE);

			/*
			$this->EE->updater_transfer->delete($location, $basedir.$dirname.'_OLD', 'dir');
			 */
		}
		catch (Exception $e)
		{
			$out['body'] = $e->getMessage();
			exit($this->generate_json($out));
		}

		$out = array('success' => 'yes', 'body' => '');
		exit($this->generate_json($out));
	}

	//********************************************************************************* //

	public function cleanup($manual=FALSE)
	{
		$this->config->_update_config(array('is_system_on' => 'y') );

		// -----------------------------------------
		// Rename Installer
		// -----------------------------------------
        try
		{
			$exists = $this->EE->updater_transfer->dir_exists('system', 'installer');
			if ($exists) $this->EE->updater_transfer->rename('system', 'installer', 'installer_OLD');

			//$this->EE->updater_transfer->delete('system', 'expressionengine/cache/updater/', 'dir');
			$this->delete_files($this->cache_path.'updater/', TRUE);

			if ($this->EE->updater_transfer->dir_exists('system', 'installer_OLD') == TRUE)
			{
				$this->EE->updater_transfer->delete('system', 'installer_OLD', 'dir');
			}

		}
		catch (Exception $e)
		{
			if ($manual == TRUE) return $e->getMessage();

			$out['body'] = $e->getMessage();
			exit($this->generate_json($out));
		}

		if ($manual == TRUE)
		{
			return 'Success...';
		}

		$out = array('success' => 'yes', 'body' => '');
		exit($this->generate_json($out));
	}

	//********************************************************************************* //

	private function generate_json($arr=array())
	{
		if (function_exists('json_encode') === FALSE)
		{
			if (class_exists('Services_JSON') == FALSE) include APPPATH.'libraries/JSON.php';
			$JSON = new Services_JSON();
			return $JSON->encode($arr);
		}

		return json_encode($arr);
	}

	// ********************************************************************************* //

	private function decode_json($obj)
	{
		if (function_exists('json_decode') === FALSE)
		{
			if (class_exists('Services_JSON') === FALSE) include APPPATH.'libraries/JSON.php';
			$JSON = new Services_JSON();
			return $JSON->decode($obj);
		}
		else
		{
			return json_decode($obj);
		}
	}

	// ********************************************************************************* //

	private function delete_files($path, $del_dir = FALSE, $level = 0)
	{
		// Trim the trailing slash
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		if ( ! $current_dir = @opendir($path))
		{
			return FALSE;
		}

		while(FALSE !== ($filename = @readdir($current_dir)))
		{
			if ($filename != "." and $filename != "..")
			{
				if (is_dir($path.DIRECTORY_SEPARATOR.$filename))
				{
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.')
					{
						$this->delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
					}
				}
				else
				{
					unlink($path.DIRECTORY_SEPARATOR.$filename);
				}
			}
		}
		@closedir($current_dir);

		if ($del_dir == TRUE AND $level > 0)
		{
			return @rmdir($path);
		}

		return TRUE;
	}

	// ********************************************************************************* //
}

if (defined('EE_APPPATH') == FALSE) define('EE_APPPATH', APPPATH);
require_once(EE_APPPATH.'/libraries/Layout'.EXT);
class EE_Layout extends Layout {
	// Nothing to see here.
}
