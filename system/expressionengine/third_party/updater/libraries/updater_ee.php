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
class Updater_ee
{
	private $addons = array();
	private $zip_file_errors = array();
	private $addon_zip_errors = array();

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

		$ee_zip_uploaded = FALSE;

		// -----------------------------------------
		// EE File URL?
		// -----------------------------------------
		if (isset($_POST['file_url']) === TRUE && $_POST['file_url'] != FALSE)
		{
			$url = trim($_POST['file_url']);
			if ($url == FALSE) continue;

			// Grab the file
			$FILE = $this->EE->updater_helper->fetch_url_file($url);

			// Write the file to disk
			$result = write_file($this->temp_dir.'ee.zip', $FILE);
			$ee_zip_uploaded = TRUE;
		}

		// -----------------------------------------
		// Uploaded Files?
		// -----------------------------------------
		if (isset($_FILES['file']) === TRUE && $ee_zip_uploaded === FALSE)
		{
			// Any Errors?
			if ($_FILES['file']['error'] > 0)
			{
				$out['actions']['upload_file']['success'] = 'no';
				$out['actions']['upload_file']['body'] = $this->EE->lang->line('error:file_upload_error');
				exit($this->EE->updater_helper->generate_json($out));
			}

			// Move it!
			if (@move_uploaded_file($_FILES['file']['tmp_name'], $this->temp_dir.'ee.zip') === FALSE)
			{
				$out['actions']['upload_file']['success'] = 'no';
				$out['actions']['upload_file']['body'] = $this->EE->lang->line('error:move_upload');
				exit($this->EE->updater_helper->generate_json($out));
			}

			$ee_zip_uploaded = TRUE;
		}

		// -----------------------------------------
		// Did we get anything?
		// -----------------------------------------
		if (empty($ee_zip_uploaded) === TRUE)
		{
			$out['actions']['upload_file']['success'] = 'no';
			$out['actions']['upload_file']['body'] = $this->EE->lang->line('error:no_file_selected');
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
		$zip = new ZipArchive();
		$res = $zip->open($this->temp_dir.'ee.zip');

		if ($res !== TRUE)
		{
			$out['actions']['extract_zip']['success'] = 'no';
			$out['actions']['extract_zip']['body'] = $this->EE->lang->line('error:zip_extract_fail');
			exit($this->EE->updater_helper->generate_json($out));
		}

		$zip->extractTo($this->temp_dir);
		$zip->close();

		$out['actions']['extract_zip']['success'] = 'yes';

		// -----------------------------------------
		// Integrity Check
		// -----------------------------------------
		if (file_exists($this->temp_dir.'system/expressionengine/config/config.php') === TRUE)
		{
			$out['actions']['ee_zip']['success'] = 'yes';
			$out['actions']['ee_zip']['body'] = '';
		}
		else
		{
			$out['actions']['ee_zip']['success'] = 'no';
			$out['actions']['ee_zip']['body'] =$this->EE->lang->line('error:no_ee_detected');
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// What Version?
		// -----------------------------------------
		if (file_exists($this->temp_dir.'system/expressionengine/libraries/Core.php') === TRUE)
		{
			$version = '';
			$file = file_get_contents($this->temp_dir.'system/expressionengine/libraries/Core.php');
			$file_install = file_get_contents($this->temp_dir.'system/installer/controllers/wizard.php');

			preg_match('/APP_BUILD.*\'(.*?)\'/', $file, $matches);
			$build = end($matches);
			preg_match('/\$version.*\'(.*?)\'/', $file_install, $matches);
			$ver = end($matches);
			$version = "v{$ver} (Build: {$build})";

			if (version_compare($build, APP_BUILD, '>='))
			{
				$out['actions']['ee_info']['success'] = 'yes';
				$out['actions']['ee_info']['body'] = $version;
			}
			else
			{
				$out['actions']['ee_info']['success'] = 'no';
				$out['actions']['ee_info']['body'] = $version;
				exit($this->EE->updater_helper->generate_json($out));
			}
		}
		else
		{
			$out['actions']['ee_info']['success'] = 'no';
			$out['actions']['ee_info']['body'] =$this->EE->lang->line('error:ee_version_detect');
			exit($this->EE->updater_helper->generate_json($out));
		}

		$out['go_on'] = 'yes';

		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //

	public function copy_installer()
	{
		$out = array('success' => 'no', 'body'=>'');
		$key = $this->EE->input->get_post('key');
		$this->temp_dir = $this->cache_path.'updater/'.$key.'/';

		try
		{
			$this->EE->updater_transfer->init();
			$this->EE->updater_transfer->mkdir('system', 'installer');
			$this->EE->updater_transfer->upload('system', PATH_THIRD.'updater/libraries/installer', 'installer', 'dir', TRUE);
			//$this->EE->updater_transfer->upload('system', $this->temp_dir.'system/installer/updates', 'installer/updates', 'dir', TRUE);
			//$this->EE->updater_transfer->upload('system', $this->temp_dir.'system/installer/core/Installer_Config.php', 'installer/core/Installer_Config.php', 'file', TRUE);

			$this->EE->updater_transfer->mkdir('system', 'installer/language/english/');
			$this->EE->updater_transfer->upload('system', PATH_THIRD.'updater/language/english/updater_lang.php', 'installer/language/english/updater_lang.php', 'file', TRUE);
		}
		catch (Exception $e)
		{
			$out['body'] = $e->getMessage();
			exit($this->EE->updater_helper->generate_json($out));
		}

		// -----------------------------------------
		// Scan Available Updates
		// -----------------------------------------
		$out['updates'] = array();
		$current = $this->EE->config->item('app_version');

		$files = directory_map($this->temp_dir.'system/installer/updates/', 1);
		sort($files);

		foreach ($files as $key => $filename)
		{
			if ($filename == '.' || $filename == '..') continue;
			if (substr($filename, 0, 3) != 'ud_') continue;
			$ver = str_replace(array('ud_', '.php'), '', $filename);
		    if ($ver <= $current) continue;

		    $out['updates'][] = array('label' => 'Version '.substr($ver,0,1).'.'.substr($ver,1,1).'.'.substr($ver,2,1), 'version' => $ver);
		}

		// Give the system time to come back?
		sleep(2);

		$out['success'] = 'yes';
		$out['server'] = $this->server;
		exit($this->EE->updater_helper->generate_json($out));
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file updater_addons.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_addons.php */
