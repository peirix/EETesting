<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

// load dependencies
require_once PATH_THIRD.'assets/config.php';

/**
 * Assets Fieldtype
 *
 * @package   Assets
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Assets_ft extends EE_Fieldtype
{
	var $info = array(
		'name'    => ASSETS_NAME,
		'version' => ASSETS_VER
	);

	var $has_array_data = TRUE;

	var $row_id; // Set by Matrix
	var $var_id; // Set by Low Variables
	var $is_draft = 0; // Set by Better Workflow

	/**
	 * @var EE
	 */
	public $EE;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// Add Package Path
		$this->EE->load->add_package_path(PATH_THIRD.'assets/');

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['assets']))
		{
			$this->EE->session->cache['assets'] = array();
		}

		$this->cache =& $this->EE->session->cache['assets'];

		// -------------------------------------------
		//  Get lib
		// -------------------------------------------
		$this->EE->load->library('assets_lib');
	}

	// --------------------------------------------------------------------

	/**
	 * Display Global Settings
	 */
	function display_global_settings()
	{
		if ($this->EE->addons_model->module_installed('assets'))
		{
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=assets'.AMP.'method=settings');
		}
		else
		{
			$this->EE->lang->loadfile('assets');
			$this->EE->session->set_flashdata('message_failure', lang('no_module'));
			$this->EE->functions->redirect(BASE.AMP.'C=addons_modules');
		}
	}

	// --------------------------------------------------------------------

	private function _prep_settings(&$settings)
	{
		$settings = array_merge(array(
			'filedirs'       => 'all',
			'multi'          => 'y',
			'view'           => 'thumbs',
			'thumb_size'     => 'small',
			'show_filenames' => 'n',
			'show_cols'      => array('name')
		), $settings);
	}

	/**
	 * Field Settings
	 */
	private function _field_settings($settings, $is_cell = FALSE)
	{
		// prep the settings
		$this->_prep_settings($settings);

		// -------------------------------------------
		//  Include Resources
		// -------------------------------------------

		if (! isset($this->cache['included_resources']))
		{
			Assets_helper::include_css('settings.css');
			Assets_helper::include_js('settings.js');

			// load the language file
			$this->EE->lang->loadfile('assets');

			$this->cache['included_resources'] = TRUE;
		}

		// get all the file upload directories
		$filedirs = $this->EE->db->select('id, name')->from('upload_prefs')
		                         ->where('site_id', $this->EE->config->item('site_id'))
		                         ->order_by('name')
		                         ->get()->result();

		$filedir_array = array();
		foreach ($filedirs as $filedir)
		{
			$filedir_array['ee:' . $filedir->id] = $filedir->name;
		}

		$sources = $this->EE->db->get('assets_sources')->result();
		$source_array = array();
		foreach ($sources as $source)
		{
			$source_array[$source->source_type . ':' . $source->source_id] = $source->name;
		}

		asort($source_array);

		$filedir_array = array_merge($filedir_array, $source_array);

		return array(
			// File Upload Directories
			array(
				lang('file_upload_directories', 'assets_filedirs') . (! $is_cell ? '<br/>'.lang('file_upload_directories_info') : ''),
				$this->EE->load->view('field/settings-filedirs', array('data' => $settings['filedirs'], 'filedirs' => $filedir_array), TRUE)
			),

			// View
			array(
				lang('view_options', 'assets_view'),
				$this->EE->load->view('field/settings-view', array('settings' => $settings), TRUE)
			),

			// Allow multiple selections?
			array(
				lang('allow_multiple_selections', 'assets_multi'),
				form_dropdown('assets[multi]', array('y'=>lang('yes'), 'n'=>lang('no')), $settings['multi'])
			),
		);
	}

	/**
	 * Display Field Settings
	 */
	function display_settings($data)
	{
		$rows = $this->_field_settings($data);

		foreach ($rows as $row)
		{
			if (isset($row['data']))
			{
				$this->EE->table->add_row($row);
			}
			else
			{
				$this->EE->table->add_row($row[0], $row[1]);
			}
		}
	}

	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($data)
	{
		$rows = $this->_field_settings($data, TRUE);

		$r = '<table class="matrix-col-settings" cellspacing="0" cellpadding="0" border="0">';

		$total_cell_settings = count($rows);

		foreach ($rows as $key => $row)
		{
			$tr_class = '';
			if ($key == 0) $tr_class .= ' matrix-first';
			if ($key == $total_cell_settings-1) $tr_class .= ' matrix-last';

			$r .= "<tr class=\"{$tr_class}\">";

			foreach ($row as $j => $cell)
			{
				if (! is_array($cell))
				{
					$cell = array('data' => $cell);
				}

				if ($j == 0)
				{
					$tag = 'th';
					$attr = 'class="matrix-first"';
				}
				else
				{
					$tag = 'td';
					$attr = 'class="matrix-last"';
				}

				if (isset($cell['style']))
				{
					$attr .= " style=\"{$cell['style']}\"";
				}

				$r .= "<{$tag} {$attr}>{$cell['data']}</{$tag}>";
			}

			$r .= '</tr>';
		}

		$r .= '</table>';

		return $r;
	}

	/**
	 * Display Variable Settings
	 */
	function display_var_settings($data)
	{
		if (! $this->var_id)
		{
			return array(
				array('', 'Assets requires Low Variables 1.3.7 or later.')
			);
		}

		return $this->_field_settings($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Save Field Settings
	 */
	function save_settings($data)
	{
		$settings = $this->EE->input->post('assets');

		// cross the T's
		$settings['field_fmt'] = 'none';
		$settings['field_show_fmt'] = 'n';
		$settings['field_type'] = 'assets';

		return $settings;
	}

	/**
	 * Save Cell Settings
	 */
	function save_cell_settings($settings)
	{
		$settings = $settings['assets'];

		return $settings;
	}

	/**
	 * Save Variable Settings
	 */
	function save_var_settings()
	{
		return $this->EE->input->post('assets');
	}

	// --------------------------------------------------------------------

	/**
	 * Migrate Field Data
	 */
	private function _migrate_field_data($field_data, $entry_data)
	{
		// Spit the newline-separated data into an array of file paths
		$paths = array_filter(preg_split('/[\r\n]/', $field_data));

		foreach ($paths as $sort_order => $path)
		{
			unset($folder_id, $file_id, $file);

			// is this a valid {filedir_X}filename.ext path?
			if (preg_match('/^\{filedir_(\d+)\}(.*\/)?([^\/]+)$/', $path, $match))
			{
				$filedir_id = $match[1];
				$folder_path = $match[2];
				$filename = $match[3];

				// Do we already have a record of this folder?
				$query = $this->EE->db->get_where('assets_folders', array(
					'filedir_id' => $filedir_id,
					'full_path'  => $folder_path
				));

				if ($query->num_rows())
				{
					$folder_id = $query->row('folder_id');
					$file_id = $this->EE->assets_lib->get_file_id_by_folder_id_and_name($folder_id, $filename);
				}

				// If we don't have a record of the file yet, create one.
				if (empty($file_id))
				{
					// Do we at least have a record of the upload directory?
					$query = $this->EE->db->get_where('assets_folders', array(
						'source_type' => 'ee',
						'filedir_id'  => $filedir_id
					));

					if (!$query->num_rows())
					{
						// hey, we tried our best. it's not our fault. it's not our fault. it's not our fault.
						continue;
					}

					// Make sure the folder records exist
					$parent_id = $query->row('folder_id');
					$folder_parts = array_filter(explode('/', $folder_path));
					$full_path = '';

					// walk through the path parts
					while (! is_null($path_part = array_shift($folder_parts)))
					{
						$full_path .= $path_part . '/';

						// Do we know about this folder?
						$query = $this->EE->db->get_where('assets_folders', array(
							'source_type' => 'ee',
							'parent_id'   => $parent_id,
							'folder_name' => $path_part
						));

						if ($query->num_rows())
						{
							$parent_id = $query->row('folder_id');
						}
						else
						{
							// Nope. Create one.
							$this->EE->db->insert('assets_folders', array(
								'source_type' => 'ee',
								'parent_id'   => $parent_id,
								'folder_name' => $path_part,
								'full_path'   => $full_path,
								'source_id'   => NULL,
								'filedir_id'  => $filedir_id,
							));

							$parent_id = $this->EE->db->insert_id();
						}
					}

					// Now that we have a record of all the folders leading up to the file,
					// create the file record
					$this->EE->db->insert('assets_files', array(
						'source_type' => 'ee',
						'folder_id'   => $parent_id,
						'file_name'   => $filename,
						'filedir_id'  => $filedir_id,
					));

					$file_id = $this->EE->db->insert_id();
				}

				$this->EE->assets_lib->update_file_search_keywords($file_id);

				// save the association in exp_assets_selections
				$this->EE->db->insert('assets_selections', array_merge($entry_data, array(
					'file_id'    => $file_id,
					'sort_order' => $sort_order
				)));
			}
		}
	}

	/**
	 * Modify exp_channel_data Column Settings
	 */
	function settings_modify_column($data)
	{
		// is this a new Assets field?
		if ($data['ee_action'] == 'add')
		{
			$field_id = $data['field_id'];
			$field_name = 'field_id_'.$field_id;

			// is this an existing field?
			if ($this->EE->db->field_exists($field_name, 'channel_data'))
			{
				$entries = $this->EE->db->select("entry_id, {$field_name}")
				                        ->where("{$field_name} LIKE '{filedir_%'")
				                        ->where("{$field_name} != ", '')
				                        ->get('channel_data');

				foreach ($entries->result() as $entry)
				{
					$this->_migrate_field_data($entry->$field_name, array(
						'entry_id' => $entry->entry_id,
						'field_id' => $field_id
					));
				}
			}
		}
		else if ($data['ee_action'] == 'delete')
		{
			// delete any asset associations created by this field
			$this->EE->db->where('field_id', $data['field_id'])
			             ->delete('assets_selections');
		}

		// just return the default column settings
		return parent::settings_modify_column($data);
	}

	/**
	 * Modify exp_matrix_data Column Settings
	 */
	function settings_modify_matrix_column($data)
	{
		// is this a new Assets column?
		if ($data['matrix_action'] == 'add')
		{
			$field_id = $this->EE->input->post('field_id');
			$col_id = $data['col_id'];
			$col_name = 'col_id_'.$col_id;

			// is this an existing field?
			if ($field_id && $this->EE->db->field_exists($col_name, 'matrix_data'))
			{
				$rows = $this->EE->db->select("entry_id, row_id, {$col_name}")
				                     ->where("{$col_name} LIKE '{filedir_%'")
				                     ->where("{$col_name} != ", '')
				                     ->get('matrix_data');

				foreach ($rows->result() as $row)
				{
					$this->_migrate_field_data($row->$col_name, array(
						'entry_id' => $row->entry_id,
						'field_id' => $field_id,
						'col_id'   => $col_id,
						'row_id'   => $row->row_id,
					));
				}
			}
		}
		else if ($data['matrix_action'] == 'delete')
		{
			// delete any asset associations created by this column
			$this->EE->db->where('col_id', $data['col_id'])
			             ->delete('assets_selections');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Build Field
	 */
	private function _build_field($data, $context)
	{
		// include the resources
		Assets_helper::include_sheet_resources();

		// prep the settings
		$this->_prep_settings($this->settings);
		// -------------------------------------------
		//  Field HTML
		// -------------------------------------------

		if ($is_cell = isset($this->cell_name))
		{
			$vars['field_name'] = $this->cell_name;
			$vars['field_id'] = str_replace(array('[',']'), array('_',''), $this->cell_name);
		}
		else if ($this->var_id)
		{
			$vars['field_name'] = $this->field_name;
			$vars['field_id'] = str_replace(array('[',']'), array('_',''), $this->field_name);
		}
		else
		{
			$vars['field_name'] = $vars['field_id'] = $this->field_name;
		}

		// -------------------------------------------
		//  Get the selected files
		// -------------------------------------------

		$entry_id = $this->EE->input->get('entry_id');

		$vars['files'] = array();

		// if there was a validation error, EE's already passed us the post array
		if (is_array($data))
		{
			foreach ($data as $file_id)
			{
				if (!empty($file_id))
				{
					$file = $this->EE->assets_lib->get_file_by_id($file_id);

					if ($file !== FALSE)
					{
						$vars['files'][] = $file;
					}
				}
			}
		}

		// should there be existing data?
		else if (
				($context == 'channel' && $this->EE->input->get('entry_id'))
			||
				($context == 'matrix' && isset($this->row_id))
			||
				($context == 'low')
			)
		{
			$sql = "SELECT DISTINCT a.source_type, a.folder_id, a.file_name, a.file_id, af.source_id, af.filedir_id
			        FROM exp_assets_files AS a
			        INNER JOIN exp_assets_selections AS ae ON ae.file_id = a.file_id
			        INNER JOIN exp_assets_folders AS af ON af.folder_id = a.folder_id
			        WHERE";

			switch ($context)
			{
				case 'low':
					$sql .= " ae.var_id = {$this->var_id}";
					break;

				case 'matrix':
					$sql .= " ae.col_id = '{$this->col_id}'
					          AND ae.row_id = '{$this->row_id}'
					          AND";

				case 'channel':
					$entry_id = $this->EE->security->xss_clean($this->EE->input->get('entry_id'));

					$sql .= " ae.entry_id ". ($entry_id ? "= '{$entry_id}'" : 'IS NULL')
						. " AND ae.field_id ". ($this->field_id ? "= '{$this->field_id}'" : 'IS NULL');
			}

			$sql .= ' ORDER BY ae.sort_order';

			if ($this->settings['multi'] == 'n')
			{
				$sql .= ' LIMIT 1';
			}

			// -------------------------------------------
 			//  'assets_field_selections_query' hook
 			//   - Modify the row data before it gets saved to exp_assets_selections
 			//
			if ($this->EE->extensions->active_hook('assets_field_selections_query'))
			{
				$query = $this->EE->extensions->call('assets_field_selections_query', $this, $sql);
			}
			else
			{
				$query = $this->EE->db->query($sql);
			}
 			//
 			// -------------------------------------------


			foreach ($query->result() as $row)
			{
				try
				{
					if ($file = $this->EE->assets_lib->get_file_by_id($row->file_id, TRUE))
					{
						$vars['files'][] = $file;
					}
				}
				catch (Exception $exception)
				{
					// these files are gone.
				}
			}
		}

		$vars['multi'] = ($this->settings['multi'] == 'y');

		$vars['helper'] = $this->EE->assets_lib;

		if ($this->settings['view'] == 'thumbs')
		{
			// load the filemanager library and file helper for generating thumbs
			$this->EE->load->library('filemanager');
			$this->EE->load->helper('file');

			$vars['file_view'] = 'thumbview/thumbview';
			$vars['thumb_size'] = $this->settings['thumb_size'];
			$vars['show_filenames'] = ($this->settings['show_filenames'] == 'y');
		}
		else
		{
			$vars['file_view'] = 'listview/listview';
			$vars['cols']   = $this->settings['show_cols'];
		}

		$r = $this->EE->load->view('field/field', $vars, TRUE);

		// Add a hidden input in case no files are selected
		$r .= '<input type="hidden" name="'.($is_cell ? $this->cell_name : $this->field_name).'[]" value="" />';

		// Include any thumb CSS queued up by the field
		Assets_helper::insert_queued_css();

		// -------------------------------------------
		//  Pass field settings to JS
		// -------------------------------------------

		if (!$is_cell || !isset($this->cache['initialized_col_settings'][$this->col_id]))
		{
			if ($is_cell)
			{
				$namespace = 'col_id_'.$this->col_id;
			}
			else
			{
				$namespace = 'field_id_'.$this->field_id;
			}

			$settings_json = $this->EE->javascript->generate_json(array(
				'filedirs'       => $this->settings['filedirs'],
				'multi'          => $vars['multi'],
				'view'           => $this->settings['view'],
				'thumb_size'     => $this->settings['thumb_size'],
				'show_filenames' => $this->settings['show_filenames'],
				'show_cols'      => $this->settings['show_cols'],
				'namespace'      => $namespace
			), TRUE);

			if ($is_cell)
			{
				Assets_helper::insert_js('Assets.Field.matrixConfs.col_id_'.$this->col_id.' = '.$settings_json.';');
				$this->cache['initialized_col_settings'][$this->col_id] = TRUE;
			}
			else
			{
				$field_id = preg_replace('/[^\w\-]+/', '_', $vars['field_id']);
				Assets_helper::insert_js("new Assets.Field('{$field_id}', '{$this->field_name}', {$settings_json});");
			}
		}

		return $r;
	}

	/**
	 * Display Field
	 */
	function display_field($data)
	{
		return $this->_build_field($data, 'channel');
	}

	/**
	 * Display Cell
	 */
	function display_cell($data)
	{
		// include the resources
		Assets_helper::include_sheet_resources();

		if (! isset($this->cache['included_matrix_resources']))
		{
			Assets_helper::include_js('matrix.js');

			$this->cache['included_matrix_resources'] = TRUE;
		}

		return array(
			'data' => $this->_build_field($data, 'matrix'),
			'class' => 'assets'
		);
	}

	/**
	 * Display Variable Field
	 */
	function display_var_field($data)
	{
		if (! $this->var_id) return;

		return $this->_build_field($data, 'low');
	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 */
	function validate($data)
	{
		// is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (!is_array($data) || !array_filter($data))
			{
				return lang('required');
			}
		}

		return TRUE;
	}

	/**
	 * Validate Cell
	 */
	function validate_cell($data)
	{
		// is this a required cell?
		if ($this->settings['col_required'] == 'y')
		{
			if (!is_array($data) || !array_filter($data))
			{
				return lang('col_required');
			}
		}

		return TRUE;
	}

	/**
	 * Get Filenames
	 */
	private function _get_filenames($file_ids)
	{
		$file_names = array();

		if ($file_ids)
		{
			$query = $this->EE->db->select('file_name')
			                      ->where_in('file_id', $file_ids)
			                      ->get('assets_files');

			foreach ($query->result() as $asset)
			{
				$file_names[] = $asset->file_name;
			}
		}

		return implode("\n", $file_names);
	}

	/**
	 * Save a BWF draft.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function draft_save($data)
	{
		$this->field_id = $this->settings['field_id'];
		$this->is_draft = 1;
		$this->post_save($data);
		return $data;
	}

	/**
	 * Discard a BWF draft.
	 */
	public function draft_discard()
	{
		$where = array(
			'entry_id' => $this->settings['entry_id'],
			'field_id' => $this->settings['field_id'],
			'is_draft' => 1
		);

		$this->EE->db->where($where)->delete('assets_selections');
	}

	/**
	 * Publish a BWF draft.
	 */
	public function draft_publish()
	{
		$where = array(
			'entry_id' => $this->settings['entry_id'],
			'field_id' => $this->settings['field_id'],
			'is_draft' => 0
		);
		$this->EE->db->where($where)->delete('assets_selections');

		$where['is_draft'] = 1;
		$update = array('is_draft' => 0);
		$this->EE->db->where($where)->update('assets_selections', $update);

		return;
	}

	/**
	 * Save
	 */
	function save($file_ids)
	{
		// ignore if it doesn't look like Assets data
		if (! is_array($file_ids))
		{
			$field_name = $this->EE->db->get_where('channel_fields', array('field_id' => $this->field_id))->row()->field_name;
			$filedir_id = $this->EE->input->post($field_name .'_filedir');

			if (!empty($_FILES[$field_name]['name']) && !empty($filedir_id))
			{
				$filename = $_FILES[$field_name]['name'];
				try
				{
					$source = $this->EE->assets_lib->instantiate_source_type((object) array('source_type' => 'ee', 'filedir_id' => $filedir_id));

					$filedir = $source->settings();
					$filename = preg_replace('/[^a-z0-9_\-\.]/i', '_', $filename);

					$file_parts = explode('.', $filename);
					$ext = array_pop($file_parts);
					$file_base = join(".", $file_parts);
					$filename = $file_base . '.' . $ext;
					$target = $filedir->server_path . $filename;

					$i = 0;
					while (file_exists($target))
					{
						if (is_numeric($i) && $i < 50)
						{
							$i++;
						}
						else{
							$i = uniqid('', TRUE);
						}

						$filename = $file_base . '_' . $i . '.' . $ext;
						$target = $filedir->server_path . $filename;
					}

					move_uploaded_file($_FILES[$field_name]['tmp_name'], $target);
					$file_id = $this->EE->assets_lib->register_ee_file($filedir->id, $filename);
					if ($file_id)
					{
						$this->cache['uploaded_assets'][$this->field_id] = array('file_id' => $file_id);
					}
				}
				catch (Exception $exception)
				{
					// Skip this.
				}
			}
		}

		// save the post data for later
		$this->cache['field_data'][$this->field_id] = $file_ids;

		// return the filenames
		return $this->_get_filenames($file_ids);
	}

	/**
	 * Save Cell
	 */
	function save_cell($file_ids)
	{
		// ignore if it doesn't look like Assets data
		if (! is_array($file_ids)) return;

		// save the post data for later
		$id = ($this->var_id ? $this->var_id : $this->field_id);
		$this->cache['field_data'][$id][$this->settings['col_id']][$this->settings['row_name']] = $file_ids;

		// return the filenames
		return $this->_get_filenames($file_ids);
	}

	/**
	 * Save Variable Field
	 */
	function save_var_field($data)
	{
		if (! $this->var_id) return;

		// ignore if it doesn't look like Assets data
		if (! is_array($data)) return;

		$file_ids = array_filter($data);

		$where = array(
			'var_id' => $this->var_id
		);

		// save the changes
		$this->_save_field($file_ids, $where);

		// return the filenames
		return $this->_get_filenames($file_ids);
	}

	// --------------------------------------------------------------------

	/**
	 * Post Save
	 */
	function post_save($data)
	{
		// ignore if we didn't cache the asset IDs
		if (! isset($this->cache['field_data'][$this->field_id])) return;

		// get the asset IDs from the cache
		$file_ids = $this->cache['field_data'][$this->field_id];
		if (!is_array($file_ids))
		{
			$file_ids = array();
		}

		$where = array(
			'entry_id' => $this->settings['entry_id'],
			'field_id' => $this->field_id,
			'is_draft' => $this->is_draft
		);

		if (!empty($this->cache['uploaded_assets'][$this->field_id]))
		{
			$file_ids[] = $this->cache['uploaded_assets'][$this->field_id]['file_id'];
		}

		// save the changes
		$this->_save_field($file_ids, $where);
	}

	/**
	 * Post Save Cell
	 */
	function post_save_cell($data)
	{
		$id = ($this->var_id ? $this->var_id : $this->field_id);

		// ignore if we didn't cache the asset IDs
		if (! isset($this->cache['field_data'][$id][$this->settings['col_id']][$this->settings['row_name']])) return;

		// get the asset IDs from the cache
		$file_ids = $this->cache['field_data'][$id][$this->settings['col_id']][$this->settings['row_name']];

		$where = array(
			'col_id'   => $this->settings['col_id'],
			'row_id'   => $this->settings['row_id']
		);

		if ($this->var_id)
		{
			$where['var_id'] = $this->var_id;
		}
		else
		{
			$where['entry_id'] = $this->settings['entry_id'];
			$where['field_id'] = $this->field_id;
		}

		if ($this->is_draft)
		{
			$where['is_draft'] = $this->is_draft;
		}

		// save the changes
		$this->_save_field($file_ids, $where);
	}

	/**
	 * Save Selections
	 */
	private function _save_field($file_ids, $where)
	{
		// -------------------------------------------
		//  'assets_save_row' hook
		//   - Modify the row data before it gets saved to exp_assets_selections
		//
		if ($this->EE->extensions->active_hook('assets_save_row'))
		{
			$where = $this->EE->extensions->call('assets_save_row', $this, $where);
		}
 		//
 		// -------------------------------------------

		// delete previous selections
		$this->EE->db->where($where)
		             ->delete('assets_selections');

		if ($file_ids)
		{
			foreach ($file_ids as $sort_order => $file_id)
			{
				if (!empty($file_id))
				{
					$selection_data = array_merge($where, array(
						'file_id'    => $file_id,
						'sort_order' => $sort_order
					));

					$this->EE->db->insert('assets_selections', $selection_data);
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Entries
	 */
	function delete($entry_ids)
	{
		$this->EE->db->where_in('entry_id', $entry_ids)
		             ->delete('assets_selections');
	}

	/**
	 * Delete Rows
	 */
	function delete_rows($row_ids)
	{
		$this->EE->db->where_in('row_id', $row_ids)
		             ->delete('assets_selections');
	}

	/**
	 * Delete Variable
	 */
	function delete_var($var_id)
	{
		$this->EE->db->where('var_id', $var_id)
		             ->delete('assets_selections');
	}

	// --------------------------------------------------------------------

	/**
	 * Pre Process
	 */
	function pre_process($data)
	{
		// -------------------------------------------
		//  Get the exp_assets_selections rows
		// -------------------------------------------

		// did the extension get called?
		if (isset($this->cache['assets_selections_rows']) && ! $this->row_id && ! $this->var_id)
		{
			if (isset($this->cache['assets_selections_rows'][$this->row['entry_id']][$this->field_id]))
			{
				$rows = $this->cache['assets_selections_rows'][$this->row['entry_id']][$this->field_id];
			}
			else
			{
				$rows = array();
			}
		}
		else
		{
			$sql = 'SELECT DISTINCT a.* FROM exp_assets_files AS a
					INNER JOIN exp_assets_folders AS af ON af.folder_id = a.folder_id
			        INNER JOIN exp_assets_selections AS ae ON ae.file_id = a.file_id';

			if ($this->var_id)
			{
				$sql .= ' WHERE ae.var_id = '.$this->var_id;
			}
			else
			{
				$sql .= ' WHERE ae.entry_id = "'.$this->row['entry_id'].'"
				            AND ae.field_id = "'.$this->field_id.'"';
			}

			if ($this->row_id)
			{
				$sql .= ' AND ae.col_id = "'.$this->col_id.'" AND ae.row_id = "'.$this->row_id.'"';
			}

			$sql .= ' AND ae.is_draft = ' . (int) $this->is_draft;

			$sql .= ' ORDER BY ae.sort_order';

			// -------------------------------------------
 			//  'assets_data_query' hook
 			//   - Modify the row data before it gets saved to exp_assets_selections
 			//
			if ($this->EE->extensions->active_hook('assets_data_query'))
			{
				$query = $this->EE->extensions->call('assets_data_query', $this, $sql);
			}
			else
			{
				$query = $this->EE->db->query($sql);
			}
 			//
 			// -------------------------------------------

 			$rows = $query->result_array();

		}

		// -------------------------------------------
		//  Get the files
		// -------------------------------------------

		$files = array();

		foreach ($rows as $row)
		{
			try
			{
				$source = $this->EE->assets_lib->instantiate_source_type((object) $row);
				{
					if ($file = $source->get_file($row['file_id']))
					{
						$files[] = $file;
					}
				}
			}
			catch (Exception $exception)
			{
				continue;
			}
		}

		return $files;
	}

	// --------------------------------------------------------------------

	/**
	 * Apply Params
	 */
	private function _apply_params(&$data, $params)
	{
		// ignore if there are no selected files
		if (! $data) return;

		// -------------------------------------------
		//  Orderby and Sort
		// -------------------------------------------

		if (isset($params['orderby']))
		{
			$orderbys = explode('|', $params['orderby']);
			$sorts = isset($params['sort']) ? explode('|', $params['sort']) : array();

			foreach ($orderbys as $i => $orderby)
			{
				foreach ($data as $file)
				{
					$ms_arrays[$orderby][] = strtolower($file->$orderby());
				}

				$ms_params[] = $ms_arrays[$orderby];
				$ms_params[] = (isset($sorts[$i]) && $sorts[$i] == 'desc') ? SORT_DESC : SORT_ASC;
			}

			$ms_params[] =& $data;

			call_user_func_array('array_multisort', $ms_params);
		}

		else if (isset($params['sort']))
		{
			switch ($params['sort'])
			{
				case 'desc':
					$data = array_reverse($data);
					break;

				case 'random':
					shuffle($data);
					break;
			}
		}

		// -------------------------------------------
		//  Search filter params
		// -------------------------------------------

		$prop_params = array('server_path', 'subfolder', 'filename', 'extension', 'date_modified', 'kind', 'width', 'height', 'size');
		$meta_params = array_keys($data[0]->row());
		$search_params = array_merge($prop_params, $meta_params);

		foreach ($search_params as $param)
		{
			if (isset($params[$param]) && ($val = $params[$param]))
			{
				// exact match?
				if ($exact = (strncmp($val, '=', 1) == 0))
				{
					$val = substr($val, 1);
				}

				// negative match?
				if ($not = (strncmp($val, 'not ', 4) == 0))
				{
					$val = substr($val, 4);
				}

				// all required?
				$all_required = (strpos($val, '&&') !== FALSE);

				// get individual terms
				$conj = $all_required ? '&&' : '|';
				$terms = explode($conj, $val);

				foreach ($data as $i => $file)
				{
					$include_file = $all_required;

					foreach ($terms as $term)
					{
						// get the actual value
						$actual_val = in_array($param, $prop_params) ? $file->$param() : $file->row_field($param);

						// comparison match?
						if (preg_match('/^[<>]=?/', $term, $m))
						{
							$term = substr($term, strlen($m[0]));
							eval('$match = ($actual_val && ($actual_val '.$m[0].' $term));');
						}
						else
						{
							// looking for empty?
							if ($empty = ($term == 'IS_EMPTY'))
							{
								$term = '';
							}

							// exact match?
							if ($exact || $empty)
							{
								$match = (strcasecmp($actual_val, $term) == 0);
							}
							else
							{
								$match = (stripos($actual_val, $term) !== FALSE);
							}
						}

						// if all are required, exclude the file on the first non-match
						if ($all_required && ! $match)
						{
							$include_file = false;
							break;
						}

						// if one is required, include the file on the first match
						if (! $all_required && $match)
						{
							$include_file = true;
							break;
						}
					}

					// remove the file from the $data array if it should be excluded
					if ($not == $include_file)
					{
						array_splice($data, $i, 1);
					}
				}
			}
		}

		// -------------------------------------------
		//  Offset and Limit
		// -------------------------------------------

		if (isset($params['offset']) || isset($params['limit']))
		{
			$offset = isset($params['offset']) ? (int) $params['offset'] : 0;
			$limit  = isset($params['limit'])  ? (int) $params['limit']  : count($data);

			$data = array_splice($data, $offset, $limit);
		}
	}

	/**
	 * Filter by Kind
	 */
	private function _filter_by_kind($file)
	{
		return in_array($file->kind(), $this->_kinds);
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		// return the full URL if there's no tagdata
		if (! $tagdata) return $this->replace_url($data, $params);

		$var_prefix = (isset($params['var_prefix']) && $params['var_prefix']) ? rtrim($params['var_prefix'], ':') . ':' : '';

		// get the absolute number of files before we run the filters
		$vars[$var_prefix.'absolute_total_files'] = count($data);

		$this->_apply_params($data, $params);
		if (! $data) return;

		// get the filtered number of files
		$vars[$var_prefix.'total_files'] = count($data);

		// parse {total_files} and {absolute_total_files} now, since they'll never change
		$tagdata = $this->EE->TMPL->parse_variables_row($tagdata, $vars);

		$r = Assets_helper::parse_file_tag($data, $tagdata, $var_prefix);

		// -------------------------------------------
		//  Backspace param
		// -------------------------------------------

		if (isset($params['backspace']))
		{
			$chop = strlen($r) - $params['backspace'];
			$r = substr($r, 0, $chop);
		}

		return $r;
	}

	/**
	 * Catches {assets_field:manipulation} and {assets_field:tag_func:manipulation} tags
	 *
	 * @param Assets_base_file $file_info
	 * @param array $params
	 * @param mixed $tagdata
	 * @param $modifier
	 * @return bool|string
	 */
	function replace_tag_catchall($file_info, $params = array(), $tagdata = FALSE, $modifier = '')
	{
		if ($modifier && is_array($file_info))
		{
			$modifier_parts = explode(':', $modifier);

			$file = array_shift($file_info);

			if (count($modifier_parts) == 2)
			{
				$tag_func = $modifier_parts[0];
				$manipulation = $modifier_parts[1];

				return $file->$tag_func($manipulation);
			}
			else
			{
				return $this->replace_tag($file_info, $params, $tagdata);
			}
		}

		return '';
	}

	/**
	 * Display Variable Tag
	 */
	function display_var_tag($data)
	{
		if (! $this->var_id) return;

		$data = $this->pre_process($data);
		return $this->replace_tag($data, $this->EE->TMPL->tagparams, $this->EE->TMPL->tagdata);
	}

	// --------------------------------------------------------------------

	/**
	 * Replace Tag
	 */
	function replace_total_files($data, $params)
	{
		$this->_apply_params($data, $params);

		return (string) count($data);
	}

	/**
	 * Replace URL
	 */
	function replace_url($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->url();
	}

	/**
	 * Replace Server Path
	 */
	function replace_server_path($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->server_path();
	}

	/**
	 * Replace Subfolder
	 */
	function replace_subfolder($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->subfolder();
	}

	/**
	 * Replace Filename
	 */
	function replace_filename($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->filename();
	}

	/**
	 * Replace Extenison
	 */
	function replace_extension($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->extension();
	}

	/**
	 * Replace Date Modified
	 */
	function replace_date_modified($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->date_modified();
	}

	/**
	 * Replace Kind
	 */
	function replace_kind($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->kind();
	}

	/**
	 * Replace Width
	 */
	function replace_width($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->width();
	}

	/**
	 * Replace Height
	 */
	function replace_height($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->height();
	}

	/**
	 * Replace Size
	 */
	function replace_size($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		$size = $data[0]->size();

		if (isset($params['unformatted']) && $params['unformatted'] == "yes")
		{
			return $size;
		}

		$size = Assets_helper::format_filesize($size);

		return ($size == '2 GB' ? '> 2 GB' : $size);
	}

	/**
	 * Replace Asset Id
	 */
	function replace_file_id($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->file_id();
	}

	/**
	 * Replace Title
	 */
	function replace_title($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('title');
	}

	/**
	 * Replace Date
	 */
	function replace_date($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('date');
	}

	/**
	 * Replace Alt Text
	 */
	function replace_alt_text($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('alt_text');
	}

	/**
	 * Replace Caption
	 */
	function replace_caption($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('caption');
	}

	/**
	 * Replace Author
	 */
	function replace_author($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('author');
	}

	/**
	 * Replace Description
	 */
	function replace_desc($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('desc');
	}

	/**
	 * Replace Location
	 */
	function replace_location($data, $params)
	{
		$this->_apply_params($data, $params);
		if (! $data) return;

		return $data[0]->row_field('location');
	}
}
