<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(PATH_THIRD . 'missing_link/config.php');

$plugin_info = array(
	'pi_name'        => ML_NAME,
	'pi_version'     => ML_VERSION,
	'pi_author'      => 'VayaDesign',
	'pi_author_url'  => ML_DOCS,
	'pi_description' => ML_DESC,
	'pi_usage'       => Missing_link::usage()
);

/**
 * Missing Link Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Dom Stubbs
 * @copyright		Copyright (c) VayaDesign
 * @link			http://www.vayadesign.net
 */


class Missing_link {

	var $userdata;
	var $settings = array();

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->lang->loadfile(ML_ID);
		$this->EE->load->model('member_model');

		// Determine the active site
		$this->active_site = ($this->EE->config->item('site_id')) ? $this->EE->config->item('site_id') : 1;

		// Make accessing user data a bit more convenient
		$this->userdata = $this->EE->session->userdata;

		// Quick access to the CP base URL, which we need to use lots
		// (differs from the standard CP URL as it includes the session ID and D=cp vars)

		if ( ! defined('CP_BASE'))
		{
			$session_id = ($this->EE->config->item('admin_session_type') == 'c') ? '0' : $this->userdata['session_id'];

			define('CP_BASE', $this->EE->config->item('cp_url').'?S='.$session_id.AMP.'D=cp');
		}

		// Grab the settings for this site
		if ( ! isset($this->EE->session->cache[ML_ID]['settings']))
		{
			$settings_raw = $this->EE->db->get_where('extensions', array('class' => __CLASS__.'_ext'));

			if ($settings_raw->row('settings'))
			{
				$all_settings = unserialize($settings_raw->row('settings'));
				if (array_key_exists($this->active_site, $all_settings))
				{
					$this->settings = $all_settings[$this->active_site];
				}
			}

			$settings_raw->free_result();

			$this->EE->session->cache[ML_ID]['settings'] = $this->settings;
		}
		else
		{
			$this->settings = $this->EE->session->cache[ML_ID]['settings'];
		}

		// Path to the missing link themes folder
		if ( ! defined('ML_ASSETS'))
		{
			define('ML_ASSETS', $this->_theme_folder_url() . "third_party/missing_link/");
		}

		// ==========================================
		// ! Check which CP theme this user has set
		// ==========================================
		// This ensures that the dialog will be styled correctly. Defaults to the system
		// CP theme setting if the user has no setting defined.

		if ( ! isset($this->EE->session->cache[ML_ID]['cp_theme']))
		{
			$where = array('member_id' => $this->userdata['member_id'], 'cp_theme !=' => '');
			$user_settings = $this->EE->db->select('cp_theme')->get_where('members', $where, 1);

			if ($user_settings->num_rows() > 0)
			{
				$this->EE->session->cache[ML_ID]['cp_theme'] = $user_settings->row('cp_theme');
			}
			else
			{
				$this->EE->session->cache[ML_ID]['cp_theme'] = $this->EE->config->item('cp_theme');
			}
			$user_settings->free_result();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Head
	 *
	 * Generate JS/CSS includes for the head tags
	 *
	 * @access	public
	 * @return	string
	 */
	function head()
	{

		if ( ! $this->_can_access_ml())
		{
			return;
		}

		// ============================================
		// ! Ensure we are logged into the correct CP
		// ============================================

		if ($this->EE->config->item('multiple_sites_enabled') == 'y' AND
			$this->EE->TMPL->fetch_param('site_switcher') != 'disabled')
		{
			$this->EE->config->site_prefs('', $this->active_site);
			$this->EE->functions->set_cookie('cp_last_site_id', $this->active_site, 0);
		}

		// ===========================
		// ! Load the jQuery modules
		// ===========================

		$session_id = ($this->EE->config->item('admin_session_type') == 'c') ? '0' : $this->userdata['session_id'];
		$script_base = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT=jquery';

		$r  = '';

		if ($this->_setting_enabled('jquery'))
		{
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.'"></script>'."\n";
		}
		if ($this->_setting_enabled('jquery_ui_core'))
		{
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'ui=core"></script>'."\n";
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'ui=widget"></script>'."\n";
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'ui=position"></script>'."\n";
		}
		if ($this->_setting_enabled('jquery_ui_dialog'))
		{
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'ui=dialog"></script>'."\n";
		}
		if ($this->_setting_enabled('jquery_fx_core'))
		{
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'effect=core"></script>'."\n";
		}
		if ($this->_setting_enabled('jquery_fx_clip'))
		{
			$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'effect=clip"></script>'."\n";
		}
		if ($this->_setting_enabled('fancybox'))
		{
			// Bundled fancybox
 			$r .= '<script type="text/javascript" charset="utf-8" src="'.ML_ASSETS.'fancybox/jquery.fancybox-1.3.1.pack.js"></script>'."\n";
			$r .= '<link rel="stylesheet" href="'.ML_ASSETS.'fancybox/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />'."\n";

			// EE core fancybox (disabled as it's two years out of date :()
			//$r .= '<script type="text/javascript" charset="utf-8" src="'.$script_base.AMP.'plugin=fancybox"></script>'."\n";
			//$r .= '<link rel="stylesheet" href="'.CP_BASE.AMP.'C=css'.AMP.'M=fancybox" type="text/css" media="screen" />'."\n";
		}

		// ===========================
		// ! Load the jQuery CSS
		// ===========================
		// EE 2.1.2 introduced cascading CP themes and stores jQuery CSS in a different location, so different
		// behaviours are required for different EE versions.

		if ($this->_setting_enabled('jquery_ui_css'))
		{
			if (version_compare(APP_VER, '2.1.2', '<'))
			{
				$r .= '<link rel="stylesheet" href="'.$this->_theme_folder_url().'jquery_ui/'.
					$this->EE->session->cache[ML_ID]['cp_theme'].'/jquery-ui-1.7.2.custom.css" type="text/css" media="screen" />'."\n";
			}
			elseif (version_compare(APP_VER, '2.3.0', '<'))
			{
				$r .= '<link rel="stylesheet" href="'.$this->_theme_folder_url().'cp_themes/'.
					$this->EE->session->cache[ML_ID]['cp_theme'].'/css/jquery-ui-1.7.2.custom.css" type="text/css" media="screen" />'."\n";
			}
			else
			{
				$r .= '<link rel="stylesheet" href="'.$this->_theme_folder_url().'cp_themes/'.
					$this->EE->session->cache[ML_ID]['cp_theme'].'/css/jquery-ui-1.8.16.custom.css" type="text/css" media="screen" />'."\n";
			}
		}

		// ======================
		// ! EE 2.3 - 2.5 jQuery UI case sensitivity fix
		// ======================
		// A jQuery UI file naming bug causes some jQuery UI images to break on case
		// sensitive file systems.
		// http://expressionengine.com/bug_tracker/bug/16823

		if (
			$this->_setting_enabled('jquery_ui_css') AND
			version_compare(APP_VER, '2.3.0', '>=') AND
			version_compare(APP_VER, '2.5.0', '<='))
		{
			$theme_images = $this->_theme_folder_url() .
								'cp_themes/' .
								$this->EE->session->cache[ML_ID]['cp_theme'] .
								'/images/';

			$r .= <<<EOF
			<style type="text/css" media="screen">
				#ml_dialog .ui-state-highlight .ui-icon {
					background-image: url({$theme_images}ui-icons_F4C424_256x240.png);
				}
			</style>
EOF;
		}

		// ======================
		// ! Load the custom JS
		// ======================

		$r .= '<script type="text/javascript" charset="utf-8" src="'.ML_ASSETS.'javascript/'.ML_ID.'.min.js'.'"></script>'."\n";

		// =======================
		// ! Load the custom CSS
		// =======================

		$r .= '<link rel="stylesheet" href="'.ML_ASSETS.'css/'.ML_ID.'.css?v=1'.'" type="text/css" media="screen" />'."\n";

		// =======================================
		// ! Load keyboard shortcut JS if enabled
		// =======================================
		// Defaults to enabled, with CTRL modifiers

		if ( ! isset($this->settings['shortcuts']) OR $this->settings['shortcuts'] == 'ctrl')
		{
			$modifier = 'ctrlKey';
		}
		elseif ($this->settings['shortcuts'] == 'alt')
		{
			$modifier = 'altKey';
		}

		$shortcut_js = '';
		if (isset($modifier))
		{
			$shortcut_js = 'var ml_modifier = "'.$modifier.'";';
		}

		// =======================================
		// ! Action monitoring setting
		// =======================================
		// This really belongs inside the cp_js_end code, however that is prone to being cached which
		// means that users can change the setting and see no changes whatsoever. In Safari, even if
		// a full force refresh occurs this doesn't always seem to be enough to override the cache.
		// As a workaround we're outputting the action monitoring setting here, inline, where it will
		// not be cached, and accessing it from within the lightbox. Hopefully the cp_js_end code
		// will be sent with a no-cache header in the future!

		// Action monitoring is enabled by default (with dialogs, not auto-reloads)
		$action_monitoring = 'dialog';

		if (isset($this->settings['action_monitoring']))
		{
			$action_monitoring = $this->settings['action_monitoring'];
		}

		// =======================
		// ! Add custom <head> JS
		// =======================
		$head_js = <<<EOF

<script type="text/javascript">
// <![CDATA[
	{$shortcut_js}
	var ml_action_monitoring = "{$action_monitoring}";
// ]]>
</script>
EOF;

		$r .= $head_js;

		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Body
	 *
	 * Generate the Missing Link base menu markup
	 *
	 * @access	public
	 * @return	string
	 */
	function body()
	{
		if ( ! $this->_can_access_ml())
		{
			return;
		}

		// =========
		// ! Setup
		// =========

		$this->EE->load->helper( array('inflector', 'form') );

		// ===================================
		// ! Window title and refresh notice
		// ===================================

		$r = <<<EOF
		<div id="ml_dialog" title="{$this->EE->config->item('site_name')}" style="display:none">
			<div class="ui-widget" id="ml_reload_notice">
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 0.7em; padding: 0.3em;">
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<a href="#" class="nofb" style="font-weight:bold">{$this->EE->lang->line('reload')}</a> {$this->EE->lang->line('to_see_updates')}</p>
				</div>
			</div>
			<div class="ui-widget" id="ml_gohome_notice">
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 0.7em; padding: 0.3em;">
					<p><span class="ui-icon ui-icon-circle-arrow-w" style="float: left; margin-right: .3em;"></span>
					<a href="{$this->EE->functions->fetch_site_index()}" class="nofb" style="font-weight:bold">{$this->EE->lang->line('return_home')}</a></p>
				</div>
			</div>
EOF;

		// ===================
		// ! Load entry data
		// ===================

		$r .= "<div id='ml_entry_markup'>\n";

		$markup = $this->_generate_entry_markup($this->EE->TMPL->fetch_param('entry_id'));

		// Check for errors and display/log them if applicable
		if ( ! isset($markup['error']))
		{
			$r .= $markup['markup'];
		}
		else
		{
			switch($markup['error'])
			{
				// The body tag won't show an error if no entry ID is present - that's an expected usage scenario
				case 'no_entry_id':
					$this->_log('Body tag did not receive an entry ID.');
					break;
				case 'invalid_entry_id':
					$r .= $this->_generate_line($this->EE->lang->line('entry_controls_invalid_id'), 'alert');
					$this->_log('Body tag received an invalid entry ID - '.$this->EE->TMPL->fetch_param('entry_id'));
					break;
			}
		}

		$r .= "</div>\n";

		// ================================
		// ! Wrapper for non-entry content
		// ================================

		$r .= "<div id='ml_generic_markup'>\n";

		// ===============================
		// ! Content mgmt items begin
		// ===============================

		$mgmt_str = '';

		// =======================
		// ! Publish a new entry
		// =======================

		if ($this->_setting_enabled('publish_entry') AND
			$this->_allowed_group('can_access_content', 'can_access_publish'))
		{
			// Superadmins can publish anywhere, for anyone else check the member group permissions

			if ($this->userdata['group_id'] != 1)
			{
				$this->EE->db->select('c.channel_id, c.channel_title')
							 ->from('channels as c')
							 ->join('channel_member_groups as m', 'c.channel_id = m.channel_id')
							 ->where('m.group_id', $this->userdata['group_id'])
							 ->where('c.site_id', $this->active_site)
							 ->order_by('channel_title', 'asc');

				$channels = $this->EE->db->get();
			}
			else
			{
				$channels = $this->EE->db->select('channel_id, channel_title')
										 ->order_by('channel_title', 'asc')
										 ->get_where('channels', array('site_id' => $this->active_site));
			}

			switch($channels->num_rows())
			{
				case 0:

					// this user can't publish anywhere, so show no publish link
					break;

				case 1:

					// this user can only publish to one channel, so no dropdown is needed

					$mgmt_str .= $this->_generate_line(
								$this->EE->lang->line('publish_entry'), 'document',
								'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$channels->row('channel_id')
							);
					break;

				default:

					// the user has access to multiple channels, so generate individual
					// channel links with a Publish toggle link

					$mgmt_str .= $this->_generate_line(
						$this->EE->lang->line('publish_entry'),
						'document',
						'C=content_publish',
						'ml_publish_link',
						'nofb'
					);

					$mgmt_str .= "<div id='ml_toggle_content'>\n";

					foreach ($channels->result() as $channel)
					{
						$mgmt_str .= "<p class='subitem'><span class='ui-icon ui-icon-arrowreturnthick-1-e' style='float:left; margin:-2px 2px 5px 0;'></span>";
						$mgmt_str .= "<a href='".CP_BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$channel->channel_id."'>";
						$mgmt_str .=  $channel->channel_title."</a></p>\n";
					}

					$mgmt_str .= "</div>\n";

					break;
			}
		}

		// =================
		// ! Edit entries
		// =================

		if ($this->_setting_enabled('edit_entries') AND
			$this->_allowed_group('can_access_content', 'can_access_edit'))
		{
			$mgmt_str .= $this->_generate_line(
				$this->EE->lang->line('edit_entries'), 'pencil', 'C=content_edit'
			);
		}

		// =================
		// ! Structure
		// =================
		// This link will only appear if the Structure module is installed

		if ($this->_setting_enabled('structure'))
		{
			$structure_installed = $this->EE->db->get_where('modules', array('module_name' => 'Structure'));

			if ($structure_installed->num_rows() > 0 AND
				($this->userdata['group_id'] == 1 OR $this->EE->member_model->can_access_module('structure')))
			{
				$mgmt_str .= $this->_generate_line(
					$this->EE->lang->line('structure'),
					'copy',
					'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'
				);
			}
		}

		// =================
		// ! Low Variables
		// =================
		// This link will only appear if the Low Variables module is installed

		if ($this->_setting_enabled('low_variables'))
		{
			$lv_installed = $this->EE->db->get_where('modules', array('module_name' => 'Low_variables'));

			if ($lv_installed->num_rows() > 0 AND
				($this->userdata['group_id'] == 1 OR $this->EE->member_model->can_access_module('low_variables')))
			{
				$mgmt_str .= $this->_generate_line(
					$this->EE->lang->line('variables'),
					'note',
					'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=low_variables'
				);
			}
		}


		// ===============================
		// ! Content mgmt heading
		// ===============================
		// If no content has been output yet then the heading needs some extra padding

		if ( ! empty($mgmt_str))
		{
			$class = (empty($entry_str)) ? 'first' : '';
			$r .= $this->_generate_title($this->EE->lang->line('content_mgmt'), $class) . $mgmt_str;
		}


		// ==================================
		// ! Site Administration items begin
		// ==================================

		$admin_str = '';


		// ===========
		// ! CP home
		// ===========

		if ($this->_setting_enabled('control_panel'))
		{
			$admin_str .= $this->_generate_line(
				$this->EE->lang->line('control_panel'), 'home', 'C=homepage'
			);
		}

		if ($this->_allowed_group('can_access_admin', 'can_access_content_prefs'))
		{
			// ====================
			// ! Manage categories
			// ====================

			if ($this->_setting_enabled('categories'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('categories'), 'tag', 'C=admin_content'.AMP.'M=category_management'
				);
			}

			// ===================
			// ! Manage channels
			// ===================

			if ($this->_setting_enabled('channels'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('channels'), 'folder-open', 'C=admin_content'.AMP.'M=channel_management'
				);
			}
		}

		// ====================
		// ! Clear caching
		// ====================
		// Points to CE Cache if installed

		if ($this->_setting_enabled('clear_cached_data'))
		{
			$ce_cache_installed = $this->EE->db->get_where('modules', array('module_name' => 'Ce_cache'))->num_rows();

			if ($ce_cache_installed === 1)
			{
				if ($this->userdata['group_id'] == 1 OR $this->EE->member_model->can_access_module('Ce_cache'))
				{
					$admin_str .= $this->_generate_line(
						$this->EE->lang->line('cache_management'),
						'clock',
						'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ce_cache'
					);
				}
			}
			elseif ($this->_allowed_group('can_access_tools', 'can_access_data'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('clear_cached_data'), 'clock', 'C=tools_data'.AMP.'M=clear_caching'
				);
			}
		}

		// =================
		// ! Manage fields
		// =================

		if ($this->_allowed_group('can_access_admin', 'can_access_content_prefs'))
		{
			if ($this->_setting_enabled('fields'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('fields'), 'document-b', 'C=admin_content'.AMP.'M=field_group_management'
				);
			}
		}

		// ====================
		// ! Manage members
		// ====================

		if ($this->_setting_enabled('manage_members') AND $this->_allowed_group('can_access_members'))
		{
			$admin_str .= $this->_generate_line($this->EE->lang->line('manage_members'), 'person', 'C=members');
		}

		// ====================
		// ! Output & Debugging
		// ====================

		if ($this->_setting_enabled('output_debugging'))
		{
			if ($this->_allowed_group('can_access_admin', 'can_access_sys_prefs'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('output_debugging'),
					'wrench',
					'C=admin_system'.AMP.'M=output_debugging_preferences'
				);
			}
		}

		// =================
		// ! Sys Preferences
		// =================

		if ($this->_setting_enabled('system_prefs'))
		{
			if ($this->_allowed_group('can_access_admin', 'can_access_sys_prefs'))
			{
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('system_prefs'), 'gear', 'C=admin_system'
				);
			}
		}

		// ====================
		// ! Manage templates
		// ====================
		// If the user has provided a template path using the template param then we'll provide a link to edit
		// this page's template directly. Otherwise a link to the main manage templates page will be shown.

		if ($this->_setting_enabled('manage_templates') AND $this->_allowed_group('can_access_design'))
		{
			// Show a direct edit link (so long as we can ascertain the template ID
			if ($template = $this->EE->TMPL->fetch_param('template'))
			{
				$segs = explode('/', $template);

				// If the template format isn't group/name then bail out
				if (count($segs) != 2) {
					$this->_log('The template parameter provided was not in the format group/name.');
				}
				else
				{
					$group_name		= $segs[0];
					$template_name	= $segs[1];

					$this->EE->db->select('t.template_id')
								 ->from('templates as t')
								 ->join('template_groups as g', 'g.group_id = t.group_id')
								 ->where('t.template_name', $template_name)
								 ->where('g.group_name', $group_name)
								 ->where('t.site_id', $this->active_site)
								 ->where('g.site_id', $this->active_site)
								 ->limit(1);

					$query = $this->EE->db->get();

					if ($query->num_rows() === 1)
					{
						$template_edit_url = 'C=design'.AMP.'M=edit_template'.AMP.'id='.$query->row('template_id');
						$admin_str .= $this->_generate_line(
							$this->EE->lang->line('edit_this_template'), 'script', $template_edit_url
						);
					}
					else
					{
						$this->_log('Unable to locate the template "'.$template.'"');
					}

					$query->free_result();
				}
			}

			// Either no template path was provided or the path was invalid. Show a generic manage templates link.
			if ( ! isset($template_edit_url))
			{
				// Link to the template manager
				$admin_str .= $this->_generate_line(
					$this->EE->lang->line('manage_templates'), 'script', 'C=design'.AMP.'M=manager'
				);
			}
		}

		// ====================
		// ! Logout
		// ====================

		if ($this->_setting_enabled('logout'))
		{
			$qs = ($this->EE->config->item('force_query_string') == 'y') ? '' : '?';
			$logout_link = $this->EE->functions->fetch_site_index(0, 0).$qs.'ACT='.$this->EE->functions->fetch_action_id('Member', 'member_logout');
			$admin_str .= $this->_generate_line(
				$this->EE->lang->line('logout'), 'power', $logout_link, '', 'nofb', FALSE
			);
		}

		// ===============================
		// ! Site Administration heading
		// ===============================
		// If no entry or content mgmt info was shown then the Site Admin heading needs some extra padding

		if ( ! empty($admin_str))
		{
			$class = (empty($entry_str) AND empty($mgmt_str)) ? 'first' : '';
			$r .= $this->_generate_title($this->EE->lang->line('site_admin'), $class) . $admin_str;
		}

		// ===============
		// ! Switchboard
		// ===============
		// Show a Switchboard search form if it is installed

		$sb = $this->EE->db->get_where('exp_accessories', array('class' => 'Switchboard_acc'));

		if ($sb->num_rows() === 1 AND $this->_setting_enabled('switchboard'))
		{
			// Check whether this user's group has access to the accessory
			$groups = explode('|', $sb->row('member_groups'));

			if (in_array($this->userdata['group_id'], $groups))
			{
				$r .= '<span id="ml_search_top"></span>';
				$r .= '<a href="#" id="ml_search_link"></a>';
				$r .= form_open('', array('id' => 'ml_search'));
				$r .= '<input type="hidden" id="switchboard_url" value="'.CP_BASE.AMP.'C=homepage'.AMP.'sbsearch='.'" />';
				$r .= '<input type="search" id="ml_search_field" placeholder="Search" results="5" autosave="ml_searchbox" />';
				$r .= form_close();
			}
		}

		// ====================================
		// ! End wrapper for non-entry content
		// ====================================

		$r .= "</div>\n";

		// ============================================
		// ! Close dialog div and show trigger button
		// ============================================

		$r .= "</div>\n";
		$r .= "<a href='#' id='ml_trigger'><img src='".ML_ASSETS."admin.png' /></a>\n";

		//print_r($this->userdata);
		return $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Inline Button
	 *
	 * Now located at menu() since the link() function is also inline, making
	 * this function name illogical and confusing.
	 *
	 * @deprecated Since 1.4
	 * @access public
	 * @return	string
	 */

	function inline()
	{
		return $this->menu();
	}

	// --------------------------------------------------------------------

	/**
	 * Menu
	 *
	 * Generates an entry menu, plus a link that will toggle its display
	 *
	 * @access	public
	 * @return	string
	 */
	function menu()
	{
		if ( ! $this->_can_access_ml())
		{
			return;
		}

		$entry_id = $this->EE->TMPL->fetch_param('entry_id');

		// Check that we have something to wrap our link around
		if (empty($this->EE->TMPL->tagdata)) {
			$this->_log('Menu tag did not receive any tagdata.');
			return;
		}

		// Grab the entry markup and display/log errors as applicable
		$raw_markup = $this->_generate_entry_markup($entry_id, TRUE);

		if ( ! isset($raw_markup['error']))
		{
			$markup = $raw_markup['markup'];
		}
		else
		{
			// We normally use entry IDs to reference the markup for inline menus. That presents a problem if
			// the entry ID happens to be invalid/non-existent. When an error occurs we'll use a random ID
			// instead.
			$entry_id = rand(1, 100000);

			switch($raw_markup['error'])
			{
				case 'no_entry_id':
					$markup = $this->_generate_line($this->EE->lang->line('entry_controls_no_id'), 'alert');
					$this->_log('Menu tag did not receive an entry ID.');
					break;
				case 'invalid_entry_id':
					$markup = $this->_generate_line($this->EE->lang->line('entry_controls_invalid_id'), 'alert');
					$this->_log('Menu tag received an invalid entry ID - '.$this->EE->TMPL->fetch_param('entry_id'));
					break;
			}
		}

		return $this->_build_dialog_link($markup, 'menu');
	}

	// --------------------------------------------------------------------

	/**
	 * Link
	 *
	 * Generates various types of links, all of which will open in a lightbox.
	 *
	 * @access public
	 * @return string
	 */
	function link()
	{
		if ( ! $this->_can_access_ml())
		{
			return;
		}

		// Promptly populate principal parameters

		$key_params = array('type', 'id', 'name', 'url', 'cp_url');

		foreach ($key_params as $param)
		{
			${$param} = $this->EE->TMPL->fetch_param($param);
		}

		// Generate links

		$cp_link = TRUE; // The vast majority of link types direct users to the control panel

		if ($id OR $name)
		{
			switch ($type)
			{
				default:
				case 'entry':

					if ($name)
					{
						$id = $this->_get_adjacent_field($name, 'url_title', 'entry_id', 'channel_titles');
						if ( ! $id) break;
					}

					$url = 'C=content_publish&M=entry_form&channel_id=2&entry_id=' . $id;
					break;

				case 'global_variable':

					if ($name)
					{
						$id = $this->_get_adjacent_field($name, 'variable_name', 'variable_id', 'global_variables');
						if ( ! $id) break;
					}

					$url = 'C=design&M=global_variables_update&variable_id=' . $id;
					break;

				// EE's edit snippet URLs use snippet names, unlike every other data type in existence.
				// This means that the usual behaviour is inverted - we need a lookup query for ID params
				case 'snippet':

					if ($id)
					{
						$name = $this->_get_adjacent_field($id, 'snippet_id', 'snippet_name', 'snippets');
						if ( ! $name) break;
					}

					$url = 'C=design&M=snippets_edit&snippet=' . $name;
					break;

				case 'low_variable':

					if ($id)
					{
						$group_id = $this->_get_adjacent_field($id, 'variable_id', 'group_id', 'low_variables', FALSE);
					}
					elseif ($name)
					{
						// Low Var names are stored as Global Vars so we need to cross-check two tables
						$this->EE->db->select('lv.group_id');
						$this->EE->db->from('low_variables as lv');
						$this->EE->db->join('global_variables as gv', 'lv.variable_id = gv.variable_id');
						$this->EE->db->where('gv.variable_name', $name);
						$this->EE->db->where('gv.site_id', $this->active_site);
						$group_id = $this->EE->db->get()->row('group_id');
					}

					if ($group_id === FALSE OR ! is_string($group_id)) break; // An ID of 0 is valid

					$url = 'C=addons_modules&M=show_module_cp&module=low_variables&group_id=' . $group_id;
					break;
			}
		}
		elseif ($cp_url)
		{
			$url = $cp_url;
		}
		elseif ($url)
		{
			$cp_link = FALSE;
		}

		// We have a link now, right?

		if ($url)
		{
			$markup = $this->_generate_line(
				$this->EE->lang->line('click_here_if_your_link_does_not_load'),
				'',
				$url,
				'',
				'',
				$cp_link
			);
		}
		else
		{
			$markup = $this->_generate_line(
				$this->EE->lang->line('item_not_found'),
				'alert'
			);
		}

		return $this->_build_dialog_link($markup);
	}

	// --------------------------------------------------------------------

	/**
	 * CP Login tag
	 *
	 * Redirects the user to a CP login form which will immediately return
	 * them to a page on the frontend. This avoids the need for users to
	 * login twice if session preferences vary between the front and backend.
	 *
	 * @return null
	 */
	function cp_login()
	{
		$return = $this->EE->TMPL->fetch_param('return');

		if ($return)
		{
			$return = $this->EE->functions->create_url($return);
		}
		else
		{
			$previous_page = @$this->EE->session->tracker[1];

			if ( ! empty($previous_page) AND $previous_page != 'index')
			{
				$return = $this->EE->functions->create_url($previous_page);
			}
			else
			{
				$return = $this->EE->functions->fetch_site_index();
			}
		}

		// If the user's already logged in go directly to the return URL
		if ($this->EE->session->userdata['member_id'] == 0)
		{
			$login_url = CP_BASE . AMP . 'C=login' . AMP . 'return=' . base64_encode('URL=' . $return);
			$this->EE->functions->redirect($login_url);
		}
		else
		{
			$this->EE->functions->redirect($return);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Build Dialog Links
	 *
	 * Creates markup for jQuery UI dialogs and the links that trigger them.
	 *
	 * @access private
	 * @param  string $dialog_markup The dialog contents
	 * @return string                The JSON-encoded dialog markup, plus the
	 *                               trigger link.
	 */
	private function _build_dialog_link($dialog_markup, $type = 'link')
	{
		$this->EE->load->library('javascript');

		$params		= $this->EE->TMPL->tagparams;
		$tagdata	= $this->EE->TMPL->tagdata;

		// Generate a unique (for this page load) dialog ID
		$link_id = $this->_unique_link_id();

		// Convert the HTML to a JSON item
		$markup = $this->EE->javascript->generate_json($dialog_markup, TRUE);

		// Build custom link attributes

		$attributes = array();

		foreach($params as $name => $val)
		{
			if (substr($name, 0, 5) == 'attr:')
			{
				$attributes[substr($name, 5)] =  $val;
			}
		}

		// Check for a class parameter
		// @deprecated (use attr:class instead!)

		if ( ! array_key_exists('class', $attributes))
		{
			$attributes['class'] = $this->EE->TMPL->fetch_param('class');
		}

		// Check whether the entry edit link should be triggered as soon as the menu opens
		// This was useful within entry menus when we didn't have a dedicated link function.
		// @deprecated (use {exp:missing_link:link} for instant edit links)

		if ($this->EE->TMPL->fetch_param('instant_edit') == 'yes' AND $type == 'menu')
		{
			$attributes['class'] .= ' instant_edit';
		}

		// Links should always open immediately.
		if ($type == 'link')
		{
			$attributes['class'] .= ' ml_link';
		}

		// Assign the must-have ML inline link class
		$attributes['class'] .= ' ml_trigger_inline';

		// We need to use the ID parameter for our own nefarious purposes
		$attributes['id'] = 'show_ml_entry_links_' . $link_id;

		$link_markup = '<a href="#"';

		foreach($attributes as $key => $val)
		{
			$link_markup .= ' ' . $key . '="' . $val . '"';
		}

		$link_markup .= '>';

		// Check whether the {link}text{/link} syntax has been used
		if (stristr($tagdata, LD.'link'.RD) AND stristr($tagdata, LD.'/link'.RD))
		{
			$r = str_ireplace(LD.'link'.RD, $link_markup, $tagdata);
			$r = str_ireplace(LD.'/link'.RD, '</a>', $r);
		}
		// If not wrap all of the tagdata with the link
		else
		{
			$r = $link_markup.trim($tagdata).'</a>';
		}

		// In previous releases the ml_entry_links object was declared as part of {exp:missing_link:head}.
		// Though that is neater it guarantees that inline ML links will break if the head tag is placed
		// outside of <head>. Whilst doing so isn't technically supported ML should otherwise be usable
		// if {exp:missing_link:head} is positioned later on in the markup, e.g. just before </body>.

		$r .= <<<EOF
<script type="text/javascript">
// <![CDATA[
	if (typeof ml_entry_links != 'object') { var ml_entry_links = new Object; }
	ml_entry_links[{$link_id}] = {$markup};
// ]]>
</script>
EOF;

		return $r;

	}


	// --------------------------------------------------------------------

	/**
	 * Generate entry markup
	 *
	 * Returns markup for entry-specific links
	 *
	 * @access	private
	 * @return	array
	 */
	private function _generate_entry_markup($entry_id, $links_required = FALSE)
	{
		$r = '';

		if ( ! $entry_id)
		{
			// No entry ID was provided - omit the entry controls entirely
			return array('error' => 'no_entry_id');
		}
		elseif (substr($entry_id, 0, 1) == LD AND substr($entry_id, -1, 1) == RD)
		{
			// We received an unparsed variable name. This probably means
			// that we aren't on a permalink page, so again show nothing
			return array('error' => 'no_entry_id');
		}
		elseif ( ! ctype_digit($entry_id))
		{
			return array('error' => 'invalid_entry_id');
		}
		else
		{
			$this->EE->db->select(
				'title, view_count_one, view_count_two, view_count_three,
				view_count_four, allow_comments, channel_id, author_id'
			);
			$entry = $this->EE->db->get_where(
				'channel_titles', array('entry_id' => $entry_id, 'site_id' => $this->active_site),
				1
			);

			// ============================================
			// ! Entry was found - show controls and info
			// ============================================

			$entry_str = '';

			if ($entry->num_rows() > 0)
			{
				// Check if the logged in user authored this entry (we need to know this for lots of the permissions checks)
				if ($entry->row('author_id') == $this->userdata['member_id'])
				{
					$is_author = TRUE;
				}
				else
				{
					$is_author = FALSE;
				}

				// Ensure they've been assigned to this entry's channel

				$assigned_to_channel = FALSE;

				if ($this->userdata['group_id'] == 1)
				{
					$assigned_to_channel = TRUE;
				}
				else
				{
					$where = array(
						'channel_id'	=> $entry->row('channel_id'),
						'group_id'		=> $this->userdata['group_id']
					);
					$query = $this->EE->db->get_where('channel_member_groups', $where);

					if ($query->num_rows === 1)
					{
						$assigned_to_channel = TRUE;
					}

					$query->free_result();
				}

				// ====================
				// ! Entry view count
				// ====================

				if (isset($this->settings['entry_view_count']) AND
					$this->settings['entry_view_count'] != 'disabled')
				{
					$entry_row = $entry->row_array();

					if ($entry_row[$this->settings['entry_view_count']] == '1')
					{
						$views = singular($this->EE->lang->line('views'));
					}
					else
					{
						$views = $this->EE->lang->line('views');
					}

					$entry_str .= $this->_generate_line(
						$this->EE->lang->line('this_entry_has').' <strong>'.
						$entry_row[$this->settings['entry_view_count']].'</strong> '.$views,
						'info'
					);
				}

				// ===================
				// ! Edit entry link
				// ===================

				if ($this->_setting_enabled('edit_this_entry') AND
					$this->_allowed_group('can_access_edit') AND $assigned_to_channel === TRUE)
				{
					if ($is_author OR ( ! $is_author AND $this->_allowed_group('can_edit_other_entries')))
					{
						$can_edit_this = TRUE;
					}
				}

				if (isset($can_edit_this))
				{
					$entry_str .= $this->_generate_line(
						$this->EE->lang->line('edit_this_entry'), 'pencil',
						'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$entry->row('channel_id').AMP.'entry_id='.$entry_id,
						'edit_this_entry'
					);
				}

				// ======================
				// ! Edit comments link
				// ======================
				// Replicating EE behaviour: the manage comments page will always load for the entry's author.
				// If the user can view other user's comments then the page will load regardless.

				if (
					$this->_setting_enabled('manage_entry_comments') AND
					$this->_allowed_group('can_moderate_comments') AND
					($is_author OR $this->_allowed_group('can_view_other_comments')) AND
					$assigned_to_channel === TRUE
				)
				{
					// EE 2.1.1+ has a comment module control panel.
					// The link differs and we need to check if the user has permission to access it.

					if (version_compare(APP_VER, '2.1.1', '<'))
					{
						$comments_url =	'C=content_edit'.AMP.'M=view_comments'.AMP.
										'channel_id='.$entry->row('channel_id').AMP.'entry_id='.$entry_id;
					}
					else
					{
						// can_access_module should work for superadmins too but it appears unreliable in EE 2.2
						if ($this->userdata['group_id'] == 1 OR $this->EE->member_model->can_access_module('comment'))
						{
							$comments_url =	'C=addons_modules'.AMP.'M=show_module_cp'.
											AMP.'module=comment'.AMP.'method=index'.AMP.'entry_id='.$entry_id;
						}
					}

					if (isset($comments_url))
					{
						$entry_str .= $this->_generate_line(
							$this->EE->lang->line('manage_entry_comments'), 'comment', $comments_url
						);
					}
				}

				// =====================
				// ! Delete entry link
				// =====================
				// We can't simply link to the delete form, as the only way of accessing it is via a POST
				// The workaround is to use an invisible form and a link that submits it.
				if ($this->_setting_enabled('delete_entry') AND $assigned_to_channel == TRUE)
				{
					if ($is_author AND
						($this->_allowed_group('can_delete_self_entries') OR
						$this->_allowed_group('can_delete_all_entries')))
					{
						$can_delete_this = TRUE;
					}
					elseif ( ! $is_author AND $this->_allowed_group('can_delete_all_entries'))
					{
						$can_delete_this = TRUE;
					}
				}

				if (isset($can_delete_this))
				{
					$entry_str .= $this->_generate_line($this->EE->lang->line('delete_entry'), 'trash', '#', 'ml_delete_link');

					// XID hash for delete form
					$hash = $this->EE->security->xss_hash();

					$hash_insert = "INSERT INTO exp_security_hashes (date, ip_address, hash) VALUES";
					$hash_insert .= "(UNIX_TIMESTAMP(), '".$this->EE->input->ip_address()."', '".$hash."')";
					$this->EE->db->query($hash_insert);

					$form_action = CP_BASE.AMP."C=content_edit".AMP."M=multi_edit_form";

					$entry_str .= <<<EOF

<form target="fancybox-frame" action="{$form_action}" method="post" id="ml_delete_form" style="display:none">
	<input type="hidden" name="XID" value="{$hash}">
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="toggle[]" value="{$entry_id}" />
</form>

EOF;
				}

				// ===============
				// ! Entry title
				// ===============
				// If no lines have been generated then no title is needed - this section will be omitted.
				// Unless of course this is an inline menu, in which case we should show an error when
				// the user doesn't have permission to access *any* links (an empty menu is going to be a
				// bit confusing).

				if ( ! empty($entry_str))
				{
					$r .= $this->_generate_title($entry->row('title'), 'first') . $entry_str;
				}
				elseif ($links_required == TRUE)
				{
					$r .= $this->_generate_line($this->EE->lang->line('no_links_available'), 'alert');
				}
			}

			// =======================
			// ! Entry was not found
			// =======================

			else
			{
				return array('error' => 'invalid_entry_id');
			}
		}

		return array('markup' => $r);
	}

	/**
	 * Generate Line
	 *
	 * Outputs a new line item, including any specified jQuery UI icon
	 *
	 * @access	private
	 * @return	string
	 */
	private function _generate_line($str, $icon = FALSE, $link = FALSE, $id = '', $link_class = '', $link_prefix = TRUE)
	{
		$line = '<p id="'.$id.'">';

		if ($icon)
		{
			$line .= '<span class="ui-icon ui-icon-'.$icon.'" style="float:left; margin:-2px 7px 5px 0;"></span> ';
		}

		if ($link)
		{
			$url_prefix = ($link_prefix) ? CP_BASE . AMP : '';
			$line .= '<a href="' . $url_prefix . $link . '" class="' . $link_class . '">' . $str . '</a>';
		}
		else
		{
			$line .= $str;
		}

		$line .= "</p>\n";

		return $line;
	}

	// --------------------------------------------------------------------

	/**
	 * Unique Link ID
	 *
	 * Generates a unique ID for each inline link/menu on a page.
	 *
	 * @access private
	 * @return int
	 */
	private function _unique_link_id()
	{
		if ( ! $this->EE->session->cache(ML_ID, 'link_id_count'))
		{
			$link_id = 1;
		}
		else
		{
			$link_id = $this->EE->session->cache(ML_ID, 'link_id_count') + 1;
		}

		$this->EE->session->set_cache(ML_ID, 'link_id_count', $link_id);

		return $link_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate Title
	 *
	 * Outputs a new title line item, including formatting
	 *
	 * @access	private
	 * @return	string
	 */
	private function _generate_title($str, $extra_class = '')
	{
		$class = 'title';
		if ($extra_class) { $class .= ' '.$extra_class; }

		return '<p class="'.$class.'"><strong>'.$str."</strong></p>\n";
	}

	// --------------------------------------------------------------------

	/**
	 * Can access Missing Link
	 *
	 * Checks whether the user is logged in and has access to the CP
	 *
	 * @access	private
	 * @return	boolean
	 */
	private function _can_access_ml()
	{
		if (( ! $this->userdata['member_id']) OR ! $this->_allowed_group('can_access_cp'))
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Template logging
	 *
	 * A shorthand function for $this->EE->TMPL->log_item
	 *
	 * @access	private
	 * @return	null
	 */
	private function _log($msg)
	{
		$this->EE->TMPL->log_item('Missing Link: '.$msg);
	}

	// --------------------------------------------------------------------

	private function _theme_folder_url()
	{
		return rtrim($this->EE->config->item('theme_folder_url'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	// --------------------------------------------------------------------

	/**
	 * Check whether a given setting has been disabled
	 * @param  string $setting Setting name
	 * @return boolean
	 */
	private function _setting_enabled($setting)
	{
		if (isset($this->settings[$setting]) AND $this->settings[$setting] == 'n')
		{
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Allowed Group
	 *
	 * We can't load the CP lib from the frontend: duplicating this function
	 * seems like the least worst option.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	private function _allowed_group()
	{
		$which = func_get_args();

		if ( ! count($which))
		{
			return FALSE;
		}

		// Super Admins always have access
		if ($this->EE->session->userdata('group_id') == 1)
		{
			return TRUE;
		}

		foreach ($which as $w)
		{
			$k = $this->EE->session->userdata($w);

			if ( ! $k OR $k !== 'y')
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Get adjacent field
	 *
	 * Looks up the contents of a certain field for a certain record based
	 * on the value of another field, assumed unique.
	 *
	 * @param  mixed   $source_val      The source data
	 * @param  string  $source_field    The field containing the source data
	 * @param  string  $lookup_field    The field containing the data we're after
	 * @param  string  $table           The source table
	 * @param  boolean $site_check      Whether results should be filtered by a site_id field
	 * @return mixed                    Record ID
	 */
	private function _get_adjacent_field($name, $name_field, $id_field, $table, $site_check = TRUE)
	{
		$this->EE->db->select($id_field);
		$this->EE->db->where($name_field, $name);

		if ($site_check)
		{
			$this->EE->db->where('site_id', $this->active_site);
		}

		$data = $this->EE->db->get($table, 1);

		if ($data->num_rows() === 0)
		{
			return FALSE;
		}

		return $data->row($id_field);
	}

	// --------------------------------------------------------------------

	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start();
		?>
		For detailed usage instructions see the Missing Link documentation (linked above).
		<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}

	// --------------------------------------------------------------------

}
// END Missing Link Class

/* End of file  pi.missing_link.php */
/* Location: ./system/expressionengine/third_party/missing_link/pi.missing_link.php */