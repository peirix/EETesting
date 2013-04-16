<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(PATH_THIRD . 'missing_link/config.php');

class Missing_link_ext {

    var $settings        = array();
    var $active_site;

    var $name            = ML_NAME;
    var $version         = ML_VERSION;
    var $description     = ML_DESC;
    var $settings_exist  = 'y';
    var $docs_url        = ML_DOCS;

    // -------------------------------
    //   Constructor
    // -------------------------------

    function __construct($settings='')
    {
		$this->EE =& get_instance();
		$this->EE->lang->loadfile(ML_ID);

		$this->settings = $settings;
		$this->active_site = ($this->EE->config->item('site_id')) ? $this->EE->config->item('site_id') : 1;
    }
	// END

	// --------------------------------
	//  Activate Extension
	// --------------------------------

	function activate_extension()
	{
	    $this->EE->db->query(
	    	$this->EE->db->insert_string('exp_extensions',
				array(
					'class'        => __CLASS__,
					'method'       => "action_monitoring",
					'hook'         => "cp_js_end",
					'settings'     => "",
					'priority'     => 100,
					'version'      => $this->version,
					'enabled'      => "y"
				)
			)
		);
	}
	// END

	// --------------------------------
	//  Update Extension
	// --------------------------------

	function update_extension($current='')
	{
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }

	    if ($current < '1.3')
	    {
		    // Convert old settings to site-specific settings (they'll be applied to site #1 or the active site if #1 doesn't exist)
	    	$site_data = $this->EE->db->get_where('exp_sites', array('site_id' => 1));

			$site = ($site_data->num_rows() === 1) ? 1 : $this->EE->config->item('site_id');

	    	$current_settings = $this->EE->db->query("SELECT settings FROM exp_extensions WHERE class = '".__CLASS__."' LIMIT 1");

	    	if ($current_settings->num_rows() > 0)
	    	{
   				$old_settings = unserialize($current_settings->row('settings'));
   				$new_settings = serialize( array($site => $old_settings) );

   				$this->EE->db->where('class', __CLASS__)->update('exp_extensions', array('settings' => $new_settings));
	    	}

	    	// Previously no hook/method was used. As of 1.3 we are using cp_js_end.
			$this->EE->db->where('class', __CLASS__)->update('exp_extensions', array('method' => 'action_monitoring', 'hook' => 'cp_js_end'));
	    }

	    $this->EE->db->query("UPDATE exp_extensions
	                SET version = '".$this->EE->db->escape_str($this->version)."'
	                WHERE class = '".__CLASS__."'");
	}
	// END

	// --------------------------------
	//  Action Monitoring
	// --------------------------------
	// Experimental feature: Automatically close fancybox and reload the base page (or display a
	// dialog) after the user has successfully completed an action. E.g. edited an entry, cleared
	// the cache etc.
	//
	// Conditions before we try to auto-close:
	//
	// 		1. We must be within an iframe whose parent page utilises Fancybox
	//		2. An ExpressionEngine success notice must be visible (or we must be on the entry
	//		   preview page - notifications aren't shown after an entry is saved).
	//		3. The page referrer URL must match the URL of the ML link the user selected
	//
	// The theory behind #3 is that we don't want to close fancybox automatically if the user's in
	// the middle of working with a set of pages. We only want to do it following the initial page
	// load. E.g. Edit This Entry > Entry Edited should auto-close. Manage Categories > Category
	// group > New Category > Category Saved should not, because the user might intend to add
	// several new categories, in which case the last thing they'd want is for fancybox to close.
	//
	// It's possible that the CP URL will be relative so instead of checking that the active link
	// and referrer match exactly we'll accept relative matches too (using parseUri()).
	// E.g. If the active link is /sys/index.php then a document.referrer val of
	// http://mysite.com/sys/index.php would be acceptable.

	function action_monitoring()
	{
		$r = '';

		// If a new entry is created or an existing one is updated we'll have no success notification
		// to use as a dialog title. Dig out the standard EE message.

		$this->EE->lang->loadfile('content');
		$entry_updated = $this->EE->lang->line('entry_has_been_updated');

		// Setup JS vars
		$r .= <<<EOF

	ml_reload_view_changes	= "{$this->EE->lang->line('reload_view_changes')}";
	ml_reload_now			= "{$this->EE->lang->line('reload_now')}";
	ml_not_done_yet			= "{$this->EE->lang->line('not_done_yet')}";
	ml_entry_updated		= "{$entry_updated}";

EOF;

		// The main JS

		$r .= <<<EOF

function parseUri(str){var o=parseUri.options,m=o.parser[o.strictMode?"strict":"loose"].exec(str),uri={},i=14;while(i--)uri[o.key[i]]=m[i]||"";uri[o.q.name]={};uri[o.key[12]].replace(o.q.parser,function($0,$1,$2){if ($1)uri[o.q.name][$1]=$2;});return uri;};parseUri.options={strictMode:false,key:["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],q:{name:"queryKey",parser:/(?:^|&)([^&=]*)=?([^&]*)/g},parser:{strict:/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,loose:/^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/}};$(document).ready(function(){var getVar=parseUri(document.URL).queryKey;var success_msg=$('div#notice_texts_container div.notice_success p');if (typeof parent.ml_action_monitoring!='undefined')
{var ml_action_monitoring=parent.ml_action_monitoring;}
else
{var ml_action_monitoring='';}
if (typeof parent.$.fancybox=='function'&&(success_msg.length>0||getVar.C=='content_publish'&&getVar.M=='view_entry')&&parseUri(window.parent.$("div#ml_dialog").data('active_link')).relative==parseUri(document.referrer).relative)
{if (ml_action_monitoring=='reload')
{parent.do_reload(300);parent.$.fancybox.close();}
else if (ml_action_monitoring=='dialog')
{if (success_msg.length>0)
{var dialog_title=$('div#notice_texts_container div.notice_success p').text();}
else
{var dialog_title=ml_entry_updated;}
jQuery('body').append('<div id="ml_confirm_dialog" title="'+dialog_title+'"><span class="ui-icon ui-icon-refresh" style="float:left; margin:0 7px 20px 0;"></span>'+ml_reload_view_changes+'</div>');var dialog_buttons={};dialog_buttons[ml_reload_now]=function(){parent.do_reload(100);parent.$.fancybox.close();};dialog_buttons[ml_not_done_yet]=function(){jQuery(this).dialog("close");}
jQuery('div#ml_confirm_dialog').dialog({resizable:false,minHeight:80,width:300,modal:true,buttons:dialog_buttons,open:function(){jQuery(this).parents('.ui-dialog-buttonpane button:eq(0)').focus();jQuery('body').css('overflow','hidden');$('.ui-widget-overlay').css('width','100%');},close:function(event,ui){jQuery('body').css('overflow','auto');}});}}});

EOF;

		return $this->EE->extensions->last_call . $r;
	}

	// END

	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension()
	{
		$this->EE->db->query('DELETE FROM exp_extensions WHERE class = "'.__CLASS__.'"');
	}

	// --------------------------------
	//  Settings Form
	// --------------------------------

	function settings_form($current)
	{
		$this->EE->cp->set_right_nav(array(
			'documentation'	=> $this->EE->cp->masked_url(ML_DOCS),
			'support'		=> $this->EE->cp->masked_url(ML_SUPPORT)
		));

		// If the extensions page has been bypassed when accessing this page (e.g. the user
		// followed a settings link from Switchboard) then the update_extension() function
		// won't have triggered. Check that the installed version isn't older than the
		// current version and run update_extension() if it is.

		$query = $this->EE->db->select('version')->get_where('extensions', array('class' => __CLASS__));

		if (@$query->row('version') < $this->version)
		{
			$this->update_extension(@$query->row('version'));
			$this->EE->functions->redirect(BASE.AMP.$this->EE->cp->get_safe_refresh());
		}

		$query->free_result();

		$this->EE->cp->load_package_css('settings');

		// Grab this site's settings, if any exist

		if (array_key_exists($this->active_site, $current))
		{
			$this->settings = $current[$this->active_site];
		}
		else
		{
			$this->settings = array();
		}

		// Entry view count dropdown

		$view_count_options = array(
			'disabled'			=> $this->EE->lang->line('disabled'),
			'view_count_one'	=> $this->EE->lang->line('view_count_one'),
			'view_count_two'	=> $this->EE->lang->line('view_count_two'),
			'view_count_three'	=> $this->EE->lang->line('view_count_three'),
			'view_count_four'	=> $this->EE->lang->line('view_count_four')
		);

		$view_count_value = (isset($this->settings['entry_view_count'])) ? $this->settings['entry_view_count'] : 'disabled';

		$fields['enable_links']['entry_controls']['entry_view_count'] = array(
			'name'		=> 'entry_view_count',
			'options'	=> $view_count_options,
			'value'		=> $view_count_value,
			'type'		=> 'dropdown',
			'info'		=> 'track_views_info'
		);

		// Normal field toggles

		$field_toggles = array(
			'entry_controls'	=> array('edit_this_entry', 'manage_entry_comments', 'delete_entry'),
			'content_mgmt'		=> array('publish_entry', 'edit_entries'),
			'site_admin'		=> array(
				'control_panel', 'categories', 'channels', 'clear_cached_data',
				'fields', 'manage_members', 'output_debugging', 'system_prefs',
				'manage_templates', 'logout'
			)
		);

		// Conditional field toggles, i.e. links dependent upon third party addons

		$fields['switchboard_installed'] = FALSE;

		if ($this->EE->db->get_where('modules', array('module_name' => 'Structure'))->num_rows() > 0)
		{
			$field_toggles['content_mgmt'][] = 'structure';
		}

		if ($this->EE->db->get_where('modules', array('module_name' => 'Low_variables'))->num_rows() > 0)
		{
			$field_toggles['content_mgmt'][] = 'low_variables';
		}

		if ($this->EE->db->get_where('accessories', array('class' => 'Switchboard_acc'))->num_rows() > 0)
		{
			$field_toggles['extras'][] = 'switchboard';
			$fields['switchboard_installed'] = TRUE;
		}


		foreach ($field_toggles as $section => $settings)
		{
			foreach ($settings as $setting)
			{
				$fields['enable_links'][$section][$setting] = array(
					'name'	=> $setting,
					'value' => $this->_set_value($setting),
					'type'	=> 'checkbox'
				);
			}
		}

		// Sort the site admin links alphabetically.
		ksort($fields['enable_links']['site_admin']);

		$fields['compatibility']['jquery']				= $this->_set_value('jquery', $this->settings);
		$fields['compatibility']['jquery_ui_core']		= $this->_set_value('jquery_ui_core', $this->settings);
		$fields['compatibility']['jquery_ui_dialog']	= $this->_set_value('jquery_ui_dialog', $this->settings);
		$fields['compatibility']['jquery_ui_css']		= $this->_set_value('jquery_ui_css', $this->settings);
		$fields['compatibility']['jquery_fx_core']		= $this->_set_value('jquery_fx_core', $this->settings);
		$fields['compatibility']['jquery_fx_clip']		= $this->_set_value('jquery_fx_clip', $this->settings);
		$fields['compatibility']['fancybox']			= $this->_set_value('fancybox', $this->settings);

		// set the assets folder URL
		$fields['assets_url'] = (empty($this->settings['assets_url'])) ? '' : $this->settings['assets_url'];

		// keyboard shortcuts
		$options = array(
			'disabled'	=> $this->EE->lang->line('disabled'),
			'ctrl'		=> $this->EE->lang->line('enabled_ctrl_modifiers'),
			'alt'		=> $this->EE->lang->line('enabled_alt_modifiers')
		);

		$shortcut_value = (isset($this->settings['shortcuts'])) ? $this->settings['shortcuts'] : 'ctrl';

		$fields['shortcuts'] = array('options' => $options, 'value' => $shortcut_value);

		// action monitoring
		$options = array(
			'dialog'	=> $this->EE->lang->line('show_confirmation_dialog'),
			'reload'	=> $this->EE->lang->line('reload_automatically'),
			'disabled'	=> $this->EE->lang->line('do_nothing')
		);

		$action_value = (isset($this->settings['action_monitoring'])) ? $this->settings['action_monitoring'] : 'dialog';

		$fields['monitoring'] = array('options' => $options, 'value' => $action_value);

		return $this->EE->load->view('settings', $fields, TRUE);
	}
	// END

	// --------------------------------
	//  Save Settings
	// --------------------------------

	function save_settings()
	{
		// Process the form data

		$blacklist = array('file', 'ml_prefs_submit');

		$settings = array();

		// Get existing settings so we don't overwrite those for other sites

		$query = $this->EE->db->get_where('exp_extensions', array('class' => __CLASS__), 1);

		if ($query->num_rows() > 0 AND $query->row('settings') != '')
		{
			$settings = unserialize($query->row('settings'));

			// Remove the old settings for this site
			// This is necessary as the boolean enabled/disabled settings are only stored when
			// they're false. As such it would otherwise be impossible to change a setting from
			// disabled back to enabled.
			unset($settings[$this->active_site]);
		}

		foreach ($_POST as $field => $setting)
		{
			if ( ! in_array($field, $blacklist))
			{
				// Only save fields that have been disabled - ML assumes fields are enabled by default
				if ($setting == 'n')
				{
					$settings[$this->active_site][$field] = 'n';
				}

				// Apart from a few prefs which are strings
				if (in_array($field, array('entry_view_count', 'shortcuts', 'action_monitoring')))
				{
					$settings[$this->active_site][$field] = $this->EE->db->escape_str($setting);
				}
			}
		}

		// Serialise it and store it

		$settings = serialize($settings);
		$sql = $this->EE->db->update_string('exp_extensions', array('settings' => $settings), "class = '".__CLASS__."'");
		$this->EE->db->query($sql);

		// Send a success message and redirect

		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('ml_settings_updated'));
		$this->EE->functions->redirect(BASE.AMP.'C=addons_extensions');

		exit;
	}
	// END

	// --------------------------------
	//  Set form values
	// --------------------------------
	function _set_value($field)
	{
		if (empty($this->settings[$field]))
		{
			return 'y';
		}
		else
		{
			return $this->settings[$field];
		}
	}

}
// END CLASS

/* End of file ext.missing_link.php */
/* Location: ./system/expressionengine/third_party/missing_link_ext/ext.missing_link_ext.php */