<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updater Tests File
 *
 * @package			DevDemon_Updater
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Updater_addons
{
	private $addons = array();
	private $zip_file_errors = array();
	private $addon_zip_errors = array();
	public $queries_executed = array();

	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->EE->load->helper('file');
		$this->EE->load->helper('directory');

		// Set the EE Cache Path? (hell you can override that)
		$this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';
	}

	// ********************************************************************************* //

	public function process_files()
	{
		$out = array('go_on' => 'no', 'actions'=>array());
		$out['addons'] = array('success' => 'no', 'list' => array());
		$key = $this->EE->input->post('key');
		$this->temp_dir = $this->cache_path.'updater/'.$key.'/';
		$addons_zip = array();
		$addons_zip_count = 0;

		// -----------------------------------------
		// Temp Dir
		// -----------------------------------------
		if (@is_dir($this->temp_dir) === FALSE)
   		{
   			@mkdir($this->temp_dir, 0777, true);
   			@chmod($this->temp_dir, 0777);
   		}

   		// Last check, does the target dir exist, and is writable
		if (is_really_writable($this->temp_dir) !== TRUE)
		{
			$out['actions']['upload_file']['success'] = 'no';
			$out['actions']['upload_file']['body'] = $this->EE->lang->line('error:temp_dir_write');
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// Addon File URL'S?
		// -----------------------------------------
		if (isset($_POST['file_url']) === TRUE && is_array($_POST['file_url']) === TRUE)
		{
			foreach ($_POST['file_url'] as $url)
			{
				$url = trim($url);
				if ($url == FALSE) continue;

				$filename = basename($url);

				// Grab the file
				$FILE = $this->EE->updater_helper->fetch_url_file($url);

				// Write the file to disk
				$result = write_file("{$this->temp_dir}addon_{$addons_zip_count}.zip", $FILE);

				// Is Success?
				if ($result)
				{
					$addons_zip[] = array('path' => "{$this->temp_dir}addon_{$addons_zip_count}.zip", 'filename' => $filename, 'name' => "addon_{$addons_zip_count}");
					$addons_zip_count++;
				}

			}
		}

		// -----------------------------------------
		// Uploaded Files?
		// -----------------------------------------
		if (isset($_FILES['file']) === TRUE && is_array($_FILES['file']['name']) === TRUE)
		{
			// Loop over all uploaded files
			foreach ($_FILES['file']['tmp_name'] as $index => $upath)
			{
				$filename = $_FILES['file']['name'][$index];

				// Any Errors?
				if ($_FILES['file']['error'][$index] > 0) continue;

				// Move it!
				if (@move_uploaded_file($_FILES['file']['tmp_name'][$index], "{$this->temp_dir}addon_{$addons_zip_count}.zip") === FALSE) continue;

				$addons_zip[] = array('path' => "{$this->temp_dir}addon_{$addons_zip_count}.zip", 'filename' => $filename, 'name' => "addon_{$addons_zip_count}");
				$addons_zip_count++;
			}
		}

		// -----------------------------------------
		// Did we get anything?
		// -----------------------------------------
		if (empty($addons_zip) === TRUE)
		{
			$out['actions']['upload_file']['success'] = 'no';
			$out['actions']['upload_file']['body'] = $this->EE->lang->line('error:no_addonfiles');
			exit($this->EE->updater_helper->generate_json($out));
		}

		$out['actions']['upload_file']['success'] = 'yes';

		// -----------------------------------------
		// Zip Extension Installed?
		// -----------------------------------------
		if (class_exists('ZipArchive') === FALSE)
		{
			$out['actions']['extract_zip']['success'] = 'no';
			$out['actions']['extract_zip']['body'] = $this->EE->lang->line('error:zip_extension');
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// Process all ZIPS
		// -----------------------------------------
		foreach($addons_zip as $key => $info)
		{
			$res = $this->verify_zip($info);

			if (!$res)
			{
				$this->zip_file_errors[] = $info['filename'];
				unset($addons_zip[$key]);
			}
		}

		// -----------------------------------------
		// Any Erros unzipping?
		// -----------------------------------------
		if (empty($this->zip_file_errors) === FALSE)
		{
			$out['actions']['extract_zip']['success'] = 'yes';
			$out['actions']['extract_zip']['body'] = '';

			foreach($this->zip_file_errors as $filename)
			{
				$out['actions']['extract_zip']['body'] .= "<span class='label label-inverse'>FAILED: </span>&nbsp;&nbsp;&nbsp;&nbsp;{$filename}<br>";
			}
		}

		$out['actions']['extract_zip']['success'] = 'yes';

		// -----------------------------------------
		// Find Addon INFO!
		// -----------------------------------------
		foreach($addons_zip as $key => $info)
		{
			$this->verify_addon($info);
		}

		// -----------------------------------------
		// Any Erros
		// -----------------------------------------
		if (empty($this->addon_zip_errors) === FALSE)
		{
			$out['actions']['addon_zip']['success'] = 'yes';
			$out['actions']['addon_zip']['body'] = '';

			foreach($this->addon_zip_errors as $filename)
			{
				$out['actions']['addon_zip']['body'] .= "<span class='label label-inverse'>FAILED: </span>&nbsp;&nbsp;&nbsp;&nbsp;{$filename}<br>";
			}
		}

		$out['actions']['addon_zip']['success'] = 'yes';

		// -----------------------------------------
		// Do we have any addons?
		// -----------------------------------------
		if (empty($this->addons) === TRUE)
		{
			$out['actions']['addon_info']['success'] = 'no';
			$out['actions']['addon_info']['body'] = $this->EE->lang->line('error:no_addon_detected');
			exit($this->EE->updater_helper->generate_json($out));
		}


		$html = '';
		$html .= '<div> <table> <thead> <tr>';
		$html .= '<th>'.$this->EE->lang->line('addon').'</th>';
		$html .= '<th>'.$this->EE->lang->line('version').'</th>';
		$html .= '<th>'.$this->EE->lang->line('types').'</th>';
		$html .= '</tr></thead><tbody>';

		foreach ($this->addons as $addon)
		{
			$html .= '<tr>';
			$html .= '<td>'.$addon->label.'</td>';
			$html .= '<td>'.$addon->version.'</td>';
			$html .= '<td>'.implode(', ', $addon->types).'</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table></div>';

		$out['actions']['addon_info']['success'] = 'yes';
		$out['actions']['addon_info']['body'] = $html;

		$out['addons'] = $this->addons;
		$out['go_on'] = 'yes';

		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	private function verify_zip($info)
	{
		if (@is_dir($this->temp_dir.$info['name']) === FALSE)
   		{
   			@mkdir($this->temp_dir.$info['name'], 0777, true);
   			@chmod($this->temp_dir.$info['name'], 0777);
   		}

		$zip = new ZipArchive();
		$res = $zip->open($info['path']);

		if ($res !== TRUE)
		{
			return FALSE;
		}

		$zip->extractTo($this->temp_dir.$info['name']);
		$zip->close();

		return TRUE;
	}

	// ********************************************************************************* //

	private function verify_addon($info)
	{
		$dir = $this->temp_dir.$info['name'];

		if (@is_dir($this->temp_dir.$info['name']) === FALSE)
   		{
   			return FALSE;
   		}

   		// -----------------------------------------
		// Get the directory contents
		// -----------------------------------------
   		$dir_contents = array();
		$dir_iterator = new RecursiveDirectoryIterator($dir);
		$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

    	foreach ($iterator as $file)
    	{
    		$dirpath = str_replace($dir, '', $file->__toString());
    		$dir_contents[] = $dirpath;
		}

   		$res = $this->check_package_json($dir, $dir_contents);
   		if (!$res) $res = $this->check_package_legacy($dir, $dir_contents, $info);
	}

	// ********************************************************************************* //

	private function check_package_json($dir, $dir_contents)
	{
		$JSON_path = FALSE;

		// -----------------------------------------
		// Find the JSON.. Wherever it is!
		// -----------------------------------------
		$package_filename = 'package.json';
		$package_length = strlen($package_filename);

    	foreach ($dir_contents as $file)
    	{
    		if (substr($file, -$package_length) === $package_filename)
    		{
    			$JSON_path = $file;
    		}
		}

		// JSON Path found? bail
		if (!$JSON_path) return FALSE;

		$JSON_path = $dir.'/'.$JSON_path;

		$contents = @read_file($JSON_path);
		if (!$contents) return FALSE;

		$JSON_path_dir = str_replace($package_filename, '', $JSON_path);

		// -----------------------------------------
		// Decode the JSON
		// -----------------------------------------
		$json = $this->EE->updater_helper->decode_json($contents);
		if ($json == FALSE) return FALSE;

		// Multiple Addons?
		if (isset($json->addons) === TRUE)
		{
			foreach ($json->addons as $addon)
			{
				// Lets do some checks!
				if (!isset($addon->name) || !isset($addon->paths) ) continue;

				$addon->root_location = $JSON_path_dir.'/';
				$this->addons[$addon->name] = $addon;
			}
		}
		else
		{
			// Lets do some checks!
			if (!isset($json->name) || !isset($json->paths) ) return FALSE;

			$json->root_location = $JSON_path_dir.'/';
			$this->addons[$json->name] = $json;
		}


		return TRUE;
	}

	// ********************************************************************************* //

	private function check_package_legacy($dir, $dir_contents, $zip_info)
	{
		$dirs = $this->legacy_find_dirs($dir_contents);

		if ($dirs == FALSE)
		{
			$this->addon_zip_errors[] = $zip_info['filename'];
			return FALSE;
		}

		$system_dir = $dir.'/'.$dirs['system'];

		// Map the directory
		$subdirs = directory_map($system_dir, 2);

		foreach ($subdirs as $addon => $sub)
		{
			// We only want real dirs. (not eg: index.html)
			if (is_array($sub) === FALSE) continue;

			$this->legacy_addon_info($addon, $system_dir.'/'.$addon, $dirs, $zip_info);
		}
	}

	// ********************************************************************************* //

	private function legacy_find_dirs($dir_contents)
	{
		$dir = array();
		$dir['system'] = FALSE;
		$dir['themes'] = FALSE;

		$slash = DIRECTORY_SEPARATOR;
		$system_dirs = array();
		$system_dirs[] = array("path" => "system{$slash}expressionengine{$slash}third_party", 'length' => strlen("system{$slash}expressionengine{$slash}third_party"));
		$system_dirs[] = array("path" => "ee2{$slash}third_party", 'length' => strlen("ee2{$slash}third_party"));

		$theme_dirs = array();
		$theme_dirs[] = array("path" => "themes{$slash}third_party", 'length' => strlen("themes{$slash}third_party"));

    	foreach ($dir_contents as $file)
    	{
    		if (strpos($file, '__MACOSX') !== FALSE) continue;
    		if (strpos($file, '.svn') !== FALSE) continue;

    		foreach ($system_dirs as $sdir)
    		{
    			if (substr($file, -$sdir['length']) === $sdir['path'])
	    		{
	    			$dir['system'] = ltrim($file, DIRECTORY_SEPARATOR);
	    			continue 2;
	    		}
    		}

    		foreach ($theme_dirs as $tdir)
    		{
    			if (substr($file, -$tdir['length']) === $tdir['path'])
	    		{
	    			$dir['themes'] = ltrim($file, DIRECTORY_SEPARATOR);
	    			continue 2;
	    		}
    		}

		}

		if (isset($dir['system']) == FALSE || $dir['system'] == FALSE) return FALSE;

    	return $dir;
	}

	// ********************************************************************************* //

	private function legacy_addon_info($addon_class, $system_dir_path, $dirs, $zip_info)
	{
		$addon = FALSE;
		$dummy_addon = new stdClass();
		$dummy_addon->name = $addon_class;
		$dummy_addon->label = ucfirst($addon_class);
		$dummy_addon->version = '(N/A)';

		// -----------------------------------------
		// Config.php?
		// -----------------------------------------
		if (file_exists($system_dir_path.'/config.php') === TRUE)
		{
			$file = file_get_contents($system_dir_path.'/config.php');

			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				$config = require_once $system_dir_path.'/config.php';

				if (isset($config['version']) === TRUE)
				{
					$addon = new stdClass();
					$addon->name = $addon_class;
					$addon->label = $config['name'];
					$addon->version = $config['version'];
				}
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// Module?
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/upd.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/upd.{$addon_class}.php");

			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				require_once "{$system_dir_path}/upd.{$addon_class}.php";
				$vars = get_class_vars( ucfirst($addon_class.'_upd') );

				$this->EE->lang->load($addon_class, $this->EE->lang->user_lang, FALSE, TRUE, $system_dir_path . '/');

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $this->EE->lang->line($addon_class.'_module_name');
				$addon->version = $vars['version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// Fieldtype?
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/ft.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/ft.{$addon_class}.php");
			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				require_once APPPATH . 'fieldtypes/EE_Fieldtype.php';
				require_once "{$system_dir_path}/ft.{$addon_class}.php";
				$vars = get_class_vars( ucfirst($addon_class.'_ft') );

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $vars['info']['name'];
				$addon->version = $vars['info']['version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// Extension?
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/ext.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/ext.{$addon_class}.php");
			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				require_once "{$system_dir_path}/ext.{$addon_class}.php";
				$vars = get_class_vars( ucfirst($addon_class.'_ext') );

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $vars['name'];
				$addon->version = $vars['version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// Accesories
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/acc.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/acc.{$addon_class}.php");
			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				require_once "{$system_dir_path}/acc.{$addon_class}.php";
				$vars = get_class_vars( ucfirst($addon_class.'_acc') );

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $vars['name'];
				$addon->version = $vars['version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// RTE
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/rte.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/rte.{$addon_class}.php");
			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				require_once "{$system_dir_path}/rte.{$addon_class}.php";
				$vars = get_class_vars( ucfirst($addon_class.'_rte') );

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $vars['info']['name'];
				$addon->version = $vars['info']['version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		// -----------------------------------------
		// Plugin
		// -----------------------------------------
		if ($addon == FALSE && file_exists("{$system_dir_path}/pi.{$addon_class}.php") === TRUE)
		{
			$file = file_get_contents("{$system_dir_path}/pi.{$addon_class}.php");
			if (strpos($file, 'PATH_THIRD') === FALSE)
			{
				$plugin_info = require_once "{$system_dir_path}/pi.{$addon_class}.php";

				$addon = new stdClass();
				$addon->name = $addon_class;
				$addon->label = $plugin_info['pi_name'];
				$addon->version = $plugin_info['pi_version'];
			}
			else
			{
				$addon = $dummy_addon;
			}
		}

		if ($addon == FALSE)
		{
			$this->addon_zip_errors[] = $addon_class;
			return FALSE;
		}

		// Double check label/verion
		$addon->version = trim($addon->version);
		$addon->label = trim($addon->label);
		if (!$addon->label) $addon->label = ucfirst($addon->name);
		if (!$addon->version) $addon->version = '(N/A)';

		$addon->schema_version = '1.0';

		$addon->root_location = $this->temp_dir.$zip_info['name'].'/';
		$addon->paths = new stdClass();
		$addon->paths->system = array();
		$addon->paths->system[] = $dirs['system'].'/'.$addon_class;

		// -----------------------------------------
		// Any Theme Dirs?
		// -----------------------------------------
		if (isset($dirs['themes']) === TRUE && $dirs['themes'] != FALSE)
		{
			// Double check to see if it really exists
			if (file_exists($addon->root_location.$dirs['themes'].'/'.$addon_class) === TRUE)
			{
				$addon->paths->themes = array();
				$addon->paths->themes[] = $dirs['themes'].'/'.$addon_class;
			}
		}

		// -----------------------------------------
		// Addon Types?
		// -----------------------------------------
		$addon->types = array();
		$types = array('upd' => 'module', 'ext' => 'extension', 'ft' => 'fieldtype', 'pi' => 'plugin', 'acc' => 'accessory', 'rte' => 'rte');

		foreach ($types as $prefix => $type)
		{
			if (file_exists("{$system_dir_path}/{$prefix}.{$addon_class}.php") === TRUE)
			{
				$addon->types[] = $type;
			}
		}

		// Last Check!
		if (empty($addon->types) === TRUE)
		{
			$this->addon_zip_errors[] = $addon_class;
			return FALSE;
		}

		$this->addons[$addon_class] = $addon;
		return TRUE;
	}

	// ********************************************************************************* //

	public function install_update_addon()
	{
		$this->EE->load->library('addons/addons_installer');
		$key = $this->EE->input->post('key');
		$addon = $this->EE->input->post('addon');
		$install = ($this->EE->input->post('update') == 'no') ? TRUE : FALSE;
		$files = directory_map(PATH_THIRD . $addon.'/');

		if ($files == FALSE)
		{
			// Sometimes we are too fast.. Last give it a break
			sleep(3);
			$files = directory_map(PATH_THIRD . $addon.'/');

			if ($files == FALSE)
			{
				// Still not? Lets give it 3 mroe seconds!
				sleep(3);
				$files = directory_map(PATH_THIRD . $addon.'/');
			}
		}

		// -----------------------------------------
		// Module?
		// -----------------------------------------
		if (in_array("upd.{$addon}.php", $files) === TRUE)
		{
			// -----------------------------------------
			// Install the module?
			// -----------------------------------------
			if ($install)
			{
				// We need to do it manually since EE's installer is heavily dependent on the CP Class
				require_once PATH_THIRD.$addon.'/upd.'.$addon.'.php';
				$class = ucfirst($addon).'_upd';
				$UPD = new $class();
				$UPD->_ee_path = APPPATH;

				$this->get_queries('start');
				if ($UPD->install() !== TRUE)
				{
					$out['addons']['success'] = 'no';
					exit($this->EE->updater_helper->generate_json($out));
				}
				$this->get_queries('end');
			}

			// -----------------------------------------
			// Update
			// -----------------------------------------
			else
			{
				require_once PATH_THIRD.$addon.'/upd.'.$addon.'.php';
				$class = ucfirst($addon).'_upd';

				// Grab the version number
				$query = $this->EE->db->select('module_version')->from('exp_modules')->where('module_name', $addon)->get();

				// Only update if it's installed
				if ($query->num_rows() > 0)
				{
					$version = $query->row('module_version');
					$this->EE->load->add_package_path(PATH_THIRD.$addon.'/');

					$UPD = new $class;
					$UPD->_ee_path = APPPATH;

					$this->get_queries('start');
					if (version_compare($UPD->version, $version, '>') && method_exists($UPD, 'update') && $UPD->update($version) !== FALSE)
					{
						$this->EE->db->update('modules', array('module_version' => $UPD->version), array('module_name' => ucfirst($addon)));
					}
					$this->get_queries('end');

					$this->EE->load->remove_package_path(PATH_THIRD.$addon.'/');
				}
			}

		}

		// -----------------------------------------
		// Fieldtype?
		// -----------------------------------------
		if (in_array("ft.{$addon}.php", $files) === TRUE)
		{
			// -----------------------------------------
			// Install the fieldtype
			// -----------------------------------------
			if ($install)
			{
				$this->get_queries('start');
				if (!$this->EE->addons_installer->install($addon, 'fieldtype', FALSE))
				{
					$out['addons']['success'] = 'no';
					exit($this->EE->updater_helper->generate_json($out));
				}
				$this->get_queries('end');
			}

			// -----------------------------------------
			// Update
			// -----------------------------------------
			else
			{
				require_once APPPATH . 'fieldtypes/EE_Fieldtype.php';
				require_once PATH_THIRD.$addon.'/ft.'.$addon.'.php';
				$class = ucfirst($addon).'_ft';

				// Grab the version number
				$query = $this->EE->db->select('version')->from('exp_fieldtypes')->where('name', $addon)->get();

				// Only update if it's installed
				if ($query->num_rows() > 0)
				{
					$version = $query->row('version');
					$this->EE->load->add_package_path(PATH_THIRD.$addon.'/');

					$FT = new $class;

					$this->get_queries('start');
					if (version_compare($FT->info['version'], $version, '>') && method_exists($FT, 'update') && $FT->update($version) !== FALSE)
					{
						$this->EE->db->update('fieldtypes', array('version' => $FT->info['version']), array('name' => $class));
					}
					$this->get_queries('end');

					$this->EE->load->remove_package_path(PATH_THIRD.$addon.'/');
				}
			}
		}

		// -----------------------------------------
		// Extension?
		// -----------------------------------------
		if (in_array("ext.{$addon}.php", $files) === TRUE)
		{
			// -----------------------------------------
			// Install the extension
			// -----------------------------------------
			if ($install)
			{
				// We need to do it manually since EE's installer is heavily dependent on the CP Class
				require_once PATH_THIRD.$addon.'/ext.'.$addon.'.php';
				$class = ucfirst($addon).'_ext';
				$EXT = new $class();

				$this->get_queries('start');
				if (method_exists($EXT, 'activate_extension') === TRUE)
				{
					$activate = $EXT->activate_extension();
				}
				$this->get_queries('end');
			}

			// -----------------------------------------
			// Update
			// -----------------------------------------
			else
			{
				require_once PATH_THIRD.$addon.'/ext.'.$addon.'.php';
				$class = ucfirst($addon).'_ext';

				// Grab the version number
				$query = $this->EE->db->select('version')->from('exp_extensions')->where('class', $class)->limit(1)->get();

				// Only update if it's installed
				if ($query->num_rows() > 0)
				{
					$version = $query->row('version');
					$this->EE->load->add_package_path(PATH_THIRD.$addon.'/');

					$EXT = new $class;

					$this->get_queries('start');
					if (version_compare($EXT->version, $version, '>') && method_exists($EXT, 'update_extension'))
					{
						$EXT->update_extension($version);
					}
					$this->get_queries('end');

					$this->EE->load->remove_package_path(PATH_THIRD.$addon.'/');
				}
			}
		}

		// -----------------------------------------
		// Accessory
		// -----------------------------------------
		if (in_array("acc.{$addon}.php", $files) === TRUE)
		{
			$this->EE->load->library('accessories');

			// -----------------------------------------
			// Install the Accesory
			// -----------------------------------------
			if ($install)
			{
				// We need to do it manually since EE's installer is heavily dependent on the CP Class
				require_once PATH_THIRD.$addon.'/acc.'.$addon.'.php';
				$class = ucfirst($addon).'_acc';
				$ACC = new $class();

				$this->get_queries('start');
				if (method_exists($ACC, 'install'))
				{
					$ACC->install();
				}

				$this->EE->db->set('class', $class);
				$this->EE->db->set('accessory_version', $ACC->version);
				$this->EE->db->insert('accessories');

				$this->EE->accessories->update_placement($class);
				$this->get_queries('end');
			}

			// -----------------------------------------
			// Update
			// -----------------------------------------
			else
			{
				require_once PATH_THIRD.$addon.'/acc.'.$addon.'.php';
				$class = ucfirst($addon).'_acc';

				// Grab the version number
				$query = $this->EE->db->select('accessory_version')->from('exp_accessories')->where('class', $class)->get();

				// Only update if it's installed
				if ($query->num_rows() > 0)
				{
					$version = $query->row('accessory_version');
					$this->EE->load->add_package_path(PATH_THIRD.$addon.'/');

					$this->EE->load->library('accessories');
					$ACC = new $class();

					$this->get_queries('start');
					if (version_compare($ACC->version, $version, '>') > $version && method_exists($ACC, 'update') && $ACC->update($version) !== FALSE)
					{
						$this->EE->db->update('exp_accessories', array('accessory_version' => $ACC->version), array('class' => $class));
					}
					$this->get_queries('end');

					$this->EE->load->remove_package_path(PATH_THIRD.$addon.'/');
				}
			}
		}

		// -----------------------------------------
		// RTE
		// -----------------------------------------
		if (in_array("rte.{$addon}.php", $files) === TRUE && $this->EE->db->table_exists('rte_tools') === TRUE)
		{
			// -----------------------------------------
			// Install RTE
			// -----------------------------------------
			if ($install)
			{
				$this->get_queries('start');
				if (!$this->EE->addons_installer->install($addon, 'rte_tool', FALSE))
				{
					$out['addons']['success'] = 'no';
					exit($this->EE->updater_helper->generate_json($out));
				}
				$this->get_queries('end');
			}

			// -----------------------------------------
			// Update
			// -----------------------------------------
			else
			{

			}
		}
	}

	// ********************************************************************************* //

	private function get_queries($action)
	{
		$this->EE->db->save_queries	= TRUE;

		if ($action == 'start')
		{
			$this->EE->db->queries = array();
		}
		else
		{
			foreach ($this->EE->db->queries as $sql)
			{
				$this->queries_executed[] = $sql;
			}
		}
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file updater_addons.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_addons.php */
