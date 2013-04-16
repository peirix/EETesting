<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*

                                    __/---\__
                     ,___     ___  /___o--\  \
                      \_ o---/ _/          )--)
                        \-----/           ______
                                          |    |
                                          |    |
                    ---_    ---_    ---_  |    |
                    |   \__ |   \__ |   \__    |
                    |      \__     \__     \__ o
                    |         `       `      \__
                    |                          |
                    |                          |
                    |__________________________|

                    | ) |_´ | ) | | |_) |  | / '
                    | \ |_, |´  \_/ |_) |_,| \_,
                            F A C T O R Y

Republic Analytics made by Republic Factory AB <http://www.republic.se> and is
licensed under a Creative Commons Attribution-NoDerivs 3.0 Unported License
<http://creativecommons.org/licenses/by-nd/3.0/>.

You can use it for free, both in personal and commercial projects as long as
this attribution in left intact. But, by downloading this add-on you also take
full responsibility for anything that happens while using it. The add-on is
made with love and passion, and is used by us on daily basis, but we cannot
guarantee that it works equally well for you.

See Republic Labs site <http://republiclabs.com> for more information.

*/


require_once PATH_THIRD.'republic_analytics/model/model.php';
require_once PATH_THIRD.'republic_analytics/config.php';


/**
 * Republic Analytics Extension Class
 *
 * @author    Ragnar Frosti Frostason <ragnar@republic.se> - Republic Factory
 * @link			http://www.republiclabs.com
 */

class Republic_analytics_ext {

	var $name			= 'Republic Analytics';
	var $version 		= REPUBLIC_ANALYTICS_VERSION;
	var $description	= 'Enable Republic Analytics as a startpage in the CP';
	var $settings_exist	= 'n';
	var $docs_url		= '';
	var $settings 		= array();


	function republic_analytics_ext()
	{
		$this->EE =& get_instance();

		$this->model = new Republic_analytics_model();

		if ( ! isset($this->EE->session->cache['republic_analytics']))
		{
			$this->EE->session->cache['republic_analytics'] = array();
		}

		$this->cache =& $this->EE->session->cache['republic_analytics'];


		if ($this->model->module_is_installed() === TRUE)
		{
			$this->model->get_configurations();
			$this->analytics_settings = $this->cache['settings'];
		}

	}


	function cp_member_login()
	{

		$group_id = $this->model->get_member_group_id($this->EE->input->post('username'));
		if (isset($this->analytics_settings['redirect_on_login']) && $this->analytics_settings['redirect_on_login'] === 'y' && in_array($group_id, $this->analytics_settings['member_group_redirect_on_login']))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=republic_analytics');
		}
	}

	function cp_js_end()
	{
		$javascript = "";
		if ($this->EE->extensions->last_call)
		{
			$javascript = $this->EE->extensions->last_call;
		}

		if (isset($this->analytics_settings['override_homepage_icon']) && $this->analytics_settings['override_homepage_icon'] === 'y' && in_array($this->EE->session->userdata['group_id'] , $this->analytics_settings['member_group_redirect_on_login']))
		{
			$url = str_replace("amp;", "", BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=republic_analytics');
			$javascript .= <<<EOF
				$("ul#navigationTabs li.home a").attr("href", "${url}");
EOF;
		}

		return $javascript;
	}

	function sessions_end($str)
	{
		if ($this->EE->input->get('D') === 'cp' && $this->EE->input->get('C') === 'homepage')
		{
			if (isset($this->analytics_settings['override_homepage_page']) && $this->analytics_settings['override_homepage_page'] === 'y' && in_array($str->userdata['group_id'] , $this->analytics_settings['member_group_redirect_on_login']))
			{
				header('Refresh: 0;url=?D=cp&C=addons_modules&M=show_module_cp&module=republic_analytics');
			}
    }
	}


	/**
	 * Activate Extension
	 * @return void
	 */
	function activate_extension()
	{
		$hooks = array(
			'cp_member_login' => 'cp_member_login',
			'cp_js_end'       => 'cp_js_end',
			'sessions_end'    => 'sessions_end'
		);

		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'    => __CLASS__,
				'method'   => $method,
				'hook'     => $hook,
				'settings' => '',
				'priority' => 10,
				'version'  => $this->version,
				'enabled'  => 'y'
			);

			$this->EE->db->insert('extensions', $data);
		}

	}


	/**
	 * Disable Extension
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}


	/**
	 * Update Extension
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current === '' OR version_compare($current, $this->version) === 0)
		{
			return FALSE;
		}

		if (version_compare($current, '2.1', '<'))
		{
			$hooks = array(
				'cp_js_end'      => 'cp_js_end',
				'sessions_end' => 'sessions_end'
			);

			foreach ($hooks as $hook => $method)
			{
				$data = array(
					'class'    => __CLASS__,
					'method'   => $method,
					'hook'     => $hook,
					'settings' => '',
					'priority' => 10,
					'version'  => $this->version,
					'enabled'  => 'y'
					);

				$this->EE->db->insert('extensions', $data);
			}
		}

		$this->EE->db->where('class', __CLASS__);
    $this->EE->db->update('extensions', array('version' => $this->version));

		return TRUE;
	}
}
// END CLASS

/* End of file ext.republic_analytics.php */
