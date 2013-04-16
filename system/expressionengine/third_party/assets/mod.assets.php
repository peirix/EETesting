<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Assets Module
 *
 * @package Assets
 * @author Brandon Kelly <brandon@pixelandtonic.com>, Andris Sevcenko <andris@shift.lv>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Assets
{
	public function __construct()
	{
		$this->EE = get_instance();

		$this->EE->load->add_package_path(PATH_THIRD.'assets/');
		$this->EE->load->library('assets_lib');
	}

	/**
	 * Get files and parse them
	 */
	public function files()
	{
		$tagdata = $this->EE->TMPL->tagdata;

		// Ignore if there's no tagdata
		if (!$tagdata)
		{
			return '';
		}

		$folders = $this->EE->TMPL->fetch_param('folder');
		$folders = preg_split("/\|/", $folders);
		foreach ($folders as &$folder)
		{
				$folder = $this->_get_folder_id_by_tagpath($folder);
		}

		$folder_ids = $this->EE->TMPL->fetch_param('folder_id');
		$folder_ids = preg_split("/\|/", $folder_ids);

		$folders = array_merge($folders, $folder_ids);

		// sort out required kinds if any
		$kinds = $this->EE->TMPL->fetch_param('kind', '');
		$kinds = empty($kinds) ? 'any' : $kinds;

		if ($kinds != 'any' && ! is_array($kinds))
		{
			$kinds = preg_split("/\||&&/", $kinds);
		}

		$parameters = array(
			'folders' => $folders,
			'keywords' => array_filter(preg_split("/\||&&/", (string) $this->EE->TMPL->fetch_param('keywords', ''))),
			'orderby' => $this->EE->TMPL->fetch_param('orderby', ''),
			'sort' => $this->EE->TMPL->fetch_param('sort', ''),
			'offset' => $this->EE->TMPL->fetch_param('offset', 0),
			'limit' => $this->EE->TMPL->fetch_param('limit', 100),
			'kinds' => $kinds
		);

		$files = $this->EE->assets_lib->get_files($parameters);

		if ($files)
		{
			// is there a var_prefix?
			if (($var_prefix = $this->EE->TMPL->fetch_param('var_prefix')) !== FALSE)
			{
				$var_prefix = rtrim($var_prefix, ':').':';
				$tagdata = str_replace($var_prefix, '', $tagdata);
			}

			return Assets_helper::parse_file_tag($files, $tagdata);
		}
		else
		{
			return $this->EE->TMPL->no_results();
		}
	}

	/**
	 * Get folders
	 */
	public function folders()
	{
		$tagdata = $this->EE->TMPL->tagdata;

		// Ignore if there's no tagdata
		if (!$tagdata)
		{
			return '';
		}

		$folder = $this->EE->TMPL->fetch_param('parent_folder');

		if (empty($folder) OR $folder == 'top')
		{
			$folder_id = 0;
		}
		else
		{
			$folder_id = $this->_get_folder_id_by_tagpath($folder);

			// If no folder found by designated parameter, return.
			if ( !$folder_id)
			{
				return '';
			}
		}

		$parameters = array(
			'parent_id' => $folder_id,
			'keywords' => array_filter(preg_split("/\||&&/", $this->EE->TMPL->fetch_param('keywords', ''))),
			'offset' => $this->EE->TMPL->fetch_param('offset', 0),
			'limit' => $this->EE->TMPL->fetch_param('limit', 1000),
			'recursive' => $this->EE->TMPL->fetch_param('recursive', 'no'),
			'sort' => $this->EE->TMPL->fetch_param('sort', 'asc'),
		);

		$folders = $this->EE->assets_lib->get_folders($parameters);

		// Make sure there are folders
		if ($folders)
		{
			$results = array();

			foreach ($folders as $folder)
			{
				$results[] = array('folder_name' => $folder->folder_name, 'folder_id' => $folder->folder_id);
			}

			// is there a var_prefix?
			if (($var_prefix = $this->EE->TMPL->fetch_param('var_prefix')) !== FALSE)
			{
				$var_prefix = rtrim($var_prefix, ':').':';
				$tagdata = str_replace($var_prefix, '', $tagdata);
			}

			return $this->EE->TMPL->parse_variables($tagdata, $results);
		}
		else
		{
			return $this->EE->TMPL->no_results();
		}
	}

	/**
	 * Get folder id by tag path
	 * @param $tagpath
	 * @return bool
	 */
	private function _get_folder_id_by_tagpath($tagpath)
	{
		$folder_id = FALSE;
		$pattern = '/\{(filedir|source)_([0-9]+)\}([a-z0-9_\- \/]+)?/i';

		if (preg_match($pattern, $tagpath, $matches))
		{
			try
			{
				$source_type = $matches[1];
				$source_id = $matches[2];
				$path = isset($matches[3]) ? $matches[3] : '';

				$field = 'source_id';
				switch($source_type)
				{
					case 'filedir':
						// check if filedir exists for this site
						if (!$this->EE->db->get_where('upload_prefs', array('id' => $source_id, 'site_id' => intval($this->EE->config->item('site_id'))))->row())
						{

							return FALSE;
						}
						$field = 'filedir_id';
						break;

					case 'source':
						if (!$this->EE->db->get_where('assets_sources', array('source_id' => $source_id)))
						{
							return FALSE;
						}
						break;
				}

				if (!empty($path))
				{
					$path = rtrim($path, '/') . '/';
				}

				$folder_row = $this->EE->db->get_where('assets_folders', array($field => $source_id, 'full_path' => $path))->row();
				if (empty($folder_row))
				{
					$folder_id = FALSE;
				}
				else
				{
					$folder_id = $folder_row->folder_id;
				}

			}
			catch (Exception $exception)
			{
				$folder_id = FALSE;
			}
		}

		return $folder_id;
	}
}
