<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
if (file_exists(PATH_THIRD.'updater/config.php') === TRUE) include PATH_THIRD.'updater/config.php';
else include dirname(dirname(__FILE__)).'/updater/config.php';

/**
 * Updater Module Control Panel Class
 *
 * @package			DevDemon_Updater
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/updater/
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Updater_mcp
{

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->load->config('updater_config');
		$this->EE->load->library('updater_helper');
		$this->EE->load->library('firephp');

		$this->settings = $this->EE->updater_helper->grab_settings();

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';

		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('updater'));

		// Add JS & CSS
		$this->EE->updater_helper->define_theme_url();
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'json2.js" type="text/javascript"></script>');
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'jquery.form.js" type="text/javascript"></script>');
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'bootstrap.js" type="text/javascript"></script>');
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'mcp.js?v='.UPDATER_VERSION.'" type="text/javascript"></script>');
		$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . UPGRADER_THEME_URL . 'mcp.css?v='.UPDATER_VERSION.'" type="text/css" media="print, projection, screen" />');
	}

	// ********************************************************************************* //

	public function index()
	{
		return $this->addons();
	}

	// ********************************************************************************* //

	public function addons()
	{
		// Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('update_addons'));
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'mcp_addons.js?v='.UPDATER_VERSION.'" type="text/javascript"></script>');

		$data = $this->global_vars();
		$data['section'] = 'addons';

		return $this->EE->load->view('addons', $data, TRUE);
	}

	// ********************************************************************************* //

	public function ee_install()
	{
		// Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('update_ee'));
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'mcp_ee.js?v='.UPDATER_VERSION.'" type="text/javascript"></script>');

		$data = $this->global_vars();
		$data['section'] = 'ee_install';

		return $this->EE->load->view('ee_install', $data, TRUE);
	}

	// ********************************************************************************* //

	public function settings()
	{
		// Set the page title
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('settings'));
		$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'mcp_settings.js?v='.UPDATER_VERSION.'" type="text/javascript"></script>');

		$data = $this->global_vars();
		$data['section'] = 'settings';
		$data['settings'] = $this->EE->updater_helper->grab_settings();
		$data['override_settings'] = $this->EE->config->item('updater');

		return $this->EE->load->view('settings', $data, TRUE);
	}

	// ********************************************************************************* //

	public function update_settings()
	{
		$settings = $this->EE->input->post('settings');

		// Put it Back
		$this->EE->db->set('settings', serialize($settings));
		$this->EE->db->where('module_name', 'Updater');
		$this->EE->db->update('exp_modules');


		$this->EE->functions->redirect($this->base . '&method=index');
	}

	// ********************************************************************************* //

	public function ajax_router()
	{
		include PATH_THIRD . 'updater/mod.updater.php';
		$MOD = new Updater();
		$MOD->cp_ajax_router($this->EE->input->get('task'));
	}

	// ********************************************************************************* //

	private function global_vars($data=array())
	{
		$data['base_url'] = $this->base;
		$data['base_url_short'] = $this->base_short;
		$data['post_max_size'] = ini_get('post_max_size');
		$data['upload_max_filesize'] = ini_get('upload_max_filesize');
		$data['zip_extension_version'] = phpversion('zip');
		$data['action_url'] = $this->EE->updater_helper->get_router_url('url');
		$data['action_url_cp'] = str_replace(AMP, '&', $this->base) . '&method=ajax_router';

		$data['zip_extension'] = in_array('zip', get_loaded_extensions());
		$data['settings_done'] = $this->settings_done();
		$data['post_5mb'] = ($this->return_bytes($data['post_max_size']) > 5242880) ? TRUE : FALSE;
		$data['upload_5mb'] = ($this->return_bytes($data['upload_max_filesize']) > 5242880) ? TRUE : FALSE;

		$data['disable_btn'] = FALSE;
		if (!$data['zip_extension'] || !$data['post_5mb'] || !$data['upload_5mb'] || !$data['settings_done']) $data['disable_btn'] = TRUE;

		return $data;
	}

	// ********************************************************************************* //

	private function settings_done()
	{
		$done = FALSE;

		if (isset($this->settings['path_map']['system']) === TRUE && $this->settings['path_map']['system'] != FALSE) $done = TRUE;

		return $done;
	}

	// ********************************************************************************* //


	/**
	 * Return Bytes
	 * @param  string $val
	 * @return int - bytes
	 */
	private function return_bytes($val) {
		$val = trim($val);

		$last = strtolower($val[strlen($val)-1]);

		switch($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file mcp.updater.php */
/* Location: ./system/expressionengine/third_party/updater/mcp.updater.php */
