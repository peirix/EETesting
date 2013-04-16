<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updater Transfer File
 *
 * @package			DevDemon_Updater
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Updater_transfer
{

	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->settings = $this->EE->updater_helper->grab_settings();
		$this->EE->load->helper('file');

		$this->EE->debug_updater = ($this->EE->config->item('updater_debug') == 'yes') ? TRUE : FALSE ;
	}

	// ********************************************************************************* //

	public function init()
	{
		$this->method = $this->settings['file_transfer_method'];
		$this->map = $this->settings['path_map'];

		if ($this->method == 'ftp')
		{
			$ftps = $this->settings['ftp'];

			if ($ftps['passive'] == 'yes') $ftps['passive'] = TRUE;
			else $ftps['passive'] = FALSE;

			if ($ftps['ssl'] == 'yes') $ftps['ssl'] = TRUE;
			else $ftps['ssl'] = FALSE;

			if (defined('PATH_THIRD')) require_once(PATH_THIRD.'updater/libraries/devdemon_ftp.php');
			else require_once(dirname(dirname(__FILE__)).'/libraries/devdemon_ftp.php');
			$this->FTP = new Devdemon_ftp($ftps);

			// Just in case
			if ($ftps['ssl'] == TRUE) $this->FTP->ssl = TRUE;

			if ( ! is_resource($this->FTP->conn_id))
			{
				// FTP CONNECT
				if (!$this->FTP->connect())
				{
					throw new Exception($this->EE->lang->line('error:ftp:login'), 1);
				}
			}
		}
		elseif ($this->method == 'sftp')
		{
			$SFTPs = $this->settings['sftp'];

			if (defined('PATH_THIRD')) require_once(PATH_THIRD.'updater/libraries/phpseclib/Net/SFTP.php');
			else require_once(dirname(dirname(__FILE__)).'/libraries/phpseclib/Net/SFTP.php');
			$this->SFTP = new Net_SFTP($SFTPs['hostname'], $SFTPs['port']);

			if ( !($this->SFTP->bitmap & NET_SSH2_MASK_LOGIN) )
			{
				if ($this->SFTP->login($SFTPs['username'], $SFTPs['password']) != TRUE)
				{
					throw new Exception($this->EE->lang->line('error:sftp:login'), 1);
				}
			}
		}
	}

	// ********************************************************************************* //

	public function dir_exists($dest_dir, $name)
	{
		$dest_dir = $this->map[$dest_dir];

		$method = '_dir_exists_'.$this->method;
		return $this->{$method}($dest_dir, $name);
	}

	// ********************************************************************************* //

	private function _dir_exists_local($dest_dir, $name)
	{
		if (is_really_writable($dest_dir) === FALSE)
		{

		}

		if (@is_dir($dest_dir.$name) === FALSE)
   		{
   			return FALSE;
   		}

		return TRUE;
	}

	// ********************************************************************************* //

	private function _dir_exists_ftp($dest_dir, $name)
	{
		if (!$this->FTP->changedir($dest_dir.$name))
		{
			return FALSE;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	private function _dir_exists_sftp($dest_dir, $name)
	{
		if ($this->SFTP->rawlist($dest_dir.$name) === FALSE)
		{
			return FALSE;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function mkdir($dest_dir, $name)
	{
		$dest_dir = $this->map[$dest_dir];

		$method = '_mkdir_'.$this->method;
		$this->{$method}($dest_dir, $name);
	}

	// ********************************************************************************* //

	private function _mkdir_local($dest_dir, $name)
	{
		if (is_really_writable($dest_dir) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
		}

		if (@is_dir($dest_dir.$name) === FALSE)
   		{
   			if (@mkdir($dest_dir.$name, 0775, true) === FALSE)
			{
				throw new Exception($this->EE->lang->line('error:local:mkdir_fail'), 1);
			}

   			@chmod($dest_dir.$name, 0775);
   		}

		return TRUE;
	}

	// ********************************************************************************* //

	private function _mkdir_ftp($dest_dir, $name)
	{
		if (!$this->FTP->changedir($dest_dir))
		{
			throw new Exception( sprintf($this->EE->lang->line('error:ftp:chdir_fail'), $dest_dir) , 1);
		}

		// Get dir array
		$arr = explode('/', $name);
		$temp = '';

		// Loop over all parts of the dir
		foreach ($arr as $tdir)
		{
			$temp .= $tdir.'/';

			// Does it already exist?
			if (!$this->FTP->changedir($dest_dir.$temp))
			{
				// Create the dir
				if (!$this->FTP->mkdir($dest_dir.$temp))
				{
					throw new Exception( sprintf($this->EE->lang->line('error:ftp:mkdir_fail'), $dest_dir.$temp) , 1);
				}
			}
		}

		// Lets verify just to be sure!
		if (!$this->FTP->changedir($dest_dir.$name))
		{
			throw new Exception( sprintf($this->EE->lang->line('error:ftp:after_mkdir_fail'), $dest_dir.$name) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	private function _mkdir_sftp($dest_dir, $name)
	{
		if ($this->SFTP->rawlist($dest_dir) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:sftp:chdir_fail'), $dest_dir) , 1);
		}

		// Get dir array
		$arr = explode('/', $name);
		$temp = '';

		// Loop over all parts of the dir
		foreach ($arr as $tdir)
		{
			$temp .= $tdir.'/';

			// Does it already exist?
			if ($this->SFTP->rawlist($dest_dir.$temp) === FALSE)
			{
				// Create the dir
				if (!$this->SFTP->mkdir($dest_dir.$temp))
				{
					throw new Exception( sprintf($this->EE->lang->line('error:sftp:mkdir_fail'), $dest_dir.$temp) , 1);
				}
			}
		}

		// Lets verify just to be sure!
		if (!$this->SFTP->chdir($dest_dir.$name))
		{
			throw new Exception( sprintf($this->EE->lang->line('error:sftp:after_mkdir_fail'), $dest_dir.$name) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function upload($dest_dir, $source, $dest, $type='dir', $force_copy=FALSE)
	{
		$dest_dir = $this->map[$dest_dir];

		// Remove those pesky \/ stuff
		$dest_dir = str_replace('\\/', '/', $dest_dir);
		$dest = str_replace('\\/', '/', $dest);
		$source = str_replace('\\/', '/', $source);

		$method = '_upload_'.$this->method;
		$this->{$method}($dest_dir, $source, $dest, $type, $force_copy);
	}

	// ********************************************************************************* //

	public function _upload_local($dest_dir, $source, $dest, $type, $force_copy)
	{
		if (is_really_writable($dest_dir) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
		}

		if ($force_copy === TRUE)
		{
			if ($type == 'dir')
			{
				// Lets make sure the last slash is there!
				$dest = preg_replace("/(.+?)\/*$/", "\\1/",  $dest);
				$source = preg_replace("/(.+?)\/*$/", "\\1/",  $source);
				$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);

				if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_COPY: {$source}  --  {$dest_dir}{$dest}");

				$this->recurse_copy($source, $dest_dir.$dest);
				return TRUE;
			}
			else
			{
				if (@copy($source, $dest_dir.$dest) === FALSE)
				{
					throw new Exception( sprintf($this->EE->lang->line('error:local:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
				}

				return TRUE;
			}
		}

		if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_COPY/RENAME: {$source}  --  {$dest_dir}{$dest}");

		if ($this->recurse_copy($source, $dest_dir.$dest) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _upload_ftp($dest_dir, $source, $dest, $type)
	{
		if ($type == 'dir')
		{
			// Lets make sure the last slash is there!
			$dest = preg_replace("/(.+?)\/*$/", "\\1/",  $dest);
			$source = preg_replace("/(.+?)\/*$/", "\\1/",  $source);
			$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);

			if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_DIR_MIRROR: {$source}  --  {$dest_dir}{$dest}");

			if (!$this->FTP->mirror($source, $dest_dir.$dest))
			{
				throw new Exception( sprintf($this->EE->lang->line('error:ftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
			}
		}
		else
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_FILE_UPLOAD: {$source}  --  {$dest_dir}{$dest}");

			if (!$this->FTP->upload($source, $dest_dir.$dest))
			{
				throw new Exception( sprintf($this->EE->lang->line('error:ftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _upload_sftp($dest_dir, $source, $dest, $type)
	{
		if ($type == 'dir')
		{
			$dest = preg_replace("/(.+?)\/*$/", "\\1/",  $dest);
			$source = preg_replace("/(.+?)\/*$/", "\\1/",  $source);
			$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);

			if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_DIR_MIRROR: {$source}  --  {$dest_dir}{$dest}");

			if (!$this->sftp_mirror($source, $dest_dir.$dest))
			{
				throw new Exception( sprintf($this->EE->lang->line('error:sftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
			}
		}
		else
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_FILE_UPLOAD: {$source}  --  {$dest_dir}{$dest}");

			if (!$this->SFTP->put($dest_dir.$dest, $source, NET_SFTP_LOCAL_FILE))
			{
				throw new Exception( sprintf($this->EE->lang->line('error:sftp:upload_fail'), $type, $source, $dest_dir.$dest) , 1);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function rename($dest_dir, $old, $new, $type='dir')
	{
		$dest_dir = $this->map[$dest_dir];

		$method = '_rename_'.$this->method;
		$this->{$method}($dest_dir, $old, $new, $type);
	}

	// ********************************************************************************* //

	public function _rename_local($dest_dir, $old, $new, $type)
	{
		if (is_really_writable($dest_dir) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
		}

		if (file_exists($dest_dir.$old) === FALSE) return TRUE;

		if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_RENAME: {$dest_dir}{$old} \n {$dest_dir}{$new}");

		if ($type == 'dir')
		{
			@chmod($dest_dir.$old);
		}

		if (@rename($dest_dir.$old, $dest_dir.$new) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _rename_ftp($dest_dir, $old, $new, $type)
	{
		if ($type == 'dir')
		{
			// Make sure we have a trailing slash
			//$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
			//$old = preg_replace("/(.+?)\/*$/", "\\1/",  $old);
			//$new = preg_replace("/(.+?)\/*$/", "\\1/",  $new);

			if ($this->FTP->changedir($dest_dir.$old) === FALSE)
			{
				return TRUE;
			}

			// CHMOD
			$this->FTP->chmod($dest_dir.$old, 0777);
		}

		if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_RENAME: {$dest_dir}{$old}  --  {$dest_dir}{$new}");

		if ($this->FTP->rename($dest_dir.$old, $dest_dir.$new) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:ftp:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _rename_sftp($dest_dir, $old, $new, $type)
	{
		if ($type == 'dir')
		{
			// Make sure we have a trailing slash
			//$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
			//$old = preg_replace("/(.+?)\/*$/", "\\1/",  $old);
			//$new = preg_replace("/(.+?)\/*$/", "\\1/",  $new);

			if ($this->SFTP->rawlist($dest_dir.$old) === FALSE)
			{
				return TRUE;
			}

			// CHMOD
			$this->SFTP->chmod(0777, $dest_dir.$old);
		}

		if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_RENAME: {$dest_dir}{$old}  --  {$dest_dir}{$new}");

		if ($this->SFTP->rename($dest_dir.$old, $dest_dir.$new) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:sftp:rename_fail'), $dest_dir.$old, $dest_dir.$new) , 1);
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function delete($dest_dir, $dest, $type='dir')
	{
		$dest_dir = $this->map[$dest_dir];

		$method = '_delete_'.$this->method;
		$this->{$method}($dest_dir, $dest, $type);
	}

	// ********************************************************************************* //

	public function _delete_local($dest_dir, $dest, $type)
	{
		if (is_really_writable($dest_dir) === FALSE)
		{
			throw new Exception( sprintf($this->EE->lang->line('error:local:not_writeable'), $dest_dir) , 1);
		}

		if (file_exists($dest_dir.$dest) == FALSE) return TRUE;

		if ($type == 'dir')
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_DIR_DELETE: {$dest_dir}{$dest}");

			@chmod($dest_dir.$dest, 0777);

			delete_files($dest_dir.$dest, TRUE);

			if (@rmdir($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:local:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}
		else
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("LOCAL_FILE_DELETE: {$dest_dir}{$dest}");

			if (@unlink($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:local:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _delete_ftp($dest_dir, $dest, $type)
	{
		if ($type == 'dir')
		{
			// Make sure we have a trailing slash
			$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
			$dest = preg_replace("/(.+?)\/*$/", "\\1/",  $dest);

			if ($this->FTP->changedir($dest_dir.$dest) === FALSE)
			{
				return TRUE;
			}
		}

		if ($type == 'dir')
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_DIR_DELETE: {$dest_dir}{$dest}");

			// CHMOD
			$this->FTP->chmod($dest_dir.$dest, 0777);

			if ($this->FTP->delete_dir($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:ftp:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}
		else
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("FTP_FILE_DELETE: {$dest_dir}{$dest}");

			if ($this->FTP->delete_file($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:ftp:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	public function _delete_sftp($dest_dir, $dest, $type)
	{
		if ($type == 'dir')
		{
			// Make sure we have a trailing slash
			$dest_dir = preg_replace("/(.+?)\/*$/", "\\1/",  $dest_dir);
			$dest = preg_replace("/(.+?)\/*$/", "\\1/",  $dest);

			if ($this->SFTP->rawlist($dest_dir.$dest) === FALSE)
			{
				return TRUE;
			}
		}

		if ($type == 'dir')
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_DIR_DELETE: {$dest_dir}{$dest}");

			// CHMOD
			$this->SFTP->chmod(0777, $dest_dir.$dest);

			if ($this->sftp_delete_dir($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:sftp:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}
		else
		{
			if ($this->EE->debug_updater) $this->EE->firephp->log("SFTP_FILE_DELETE: {$dest_dir}{$dest}");

			if ($this->SFTP->delete($dest_dir.$dest) === FALSE)
			{
				throw new Exception( sprintf($this->EE->lang->line('error:sftp:delete_fail'), $type, $dest_dir.$dest) , 1);
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	bool
	 */
	private function sftp_mirror($locpath, $rempath)
	{
		if ( ! isset($this->SFTP))
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$locpath = preg_replace("/(.+?)\/*$/", "\\1/",  $locpath);

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ($this->SFTP->rawlist($rempath) === FALSE)
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->SFTP->mkdir($rempath) OR $this->SFTP->rawlist($rempath) === FALSE)
				{
					return FALSE;
				}
			}

			// Recursively read the local directory
			while (FALSE !== ($file = readdir($fp)))
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->sftp_mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif (substr($file, 0, 1) != ".")
				{
					$this->SFTP->put($rempath.$file, $locpath.$file, NET_SFTP_LOCAL_FILE);
				}
			}
			return TRUE;
		}

		return FALSE;
	}

	// ********************************************************************************* //

	private function sftp_delete_dir($filepath)
	{
		if ( ! isset($this->SFTP))
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);

		$list = $this->SFTP->nlist($filepath);

		if ($list != FALSE OR count($list) > 0)
		{
			foreach ($list as $item)
			{
				if ($item == '.' OR $item == '..') continue;

				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if ( ! @$this->SFTP->delete($filepath.$item))
				{
					$this->sftp_delete_dir($filepath.$item.'/');
				}
			}
		}

		$result = $this->SFTP->rmdir($filepath);

		if ($result === FALSE)
		{
			return FALSE;
		}

		return TRUE;
	}

	// ********************************************************************************* //

	private function recurse_copy($source, $dest)
	{
		$source = rtrim($source, '/\\');
		$dest = rtrim($dest, '/\\');

		if (is_dir($source))
		{
			$dir_handle=opendir($source);

			while($file=readdir($dir_handle))
			{
				if ($file != '.' && $file != '..')
				{
	                if (is_dir($source.'/'.$file))
	                {
						@mkdir($dest.'/'.$file);
	                    $this->recurse_copy($source.'/'.$file, $dest.'/'.$file);
	                }
	                else
	                {
	                	//$this->EE->firephp->log("LOCAL_COPY: {$source}/{$file}  --  {$dest}/{$file}");
	                    copy($source.'/'.$file, $dest.'/'.$file);
	                }
	            }
	        }
	        closedir($dir_handle);
	    } else {
	    	//$this->EE->firephp->log("LOCAL_COPY: {$source}  --  {$dest}");
	        copy($source, $dest);
	    }

	}

	// ********************************************************************************* //

} // END CLASS

/* End of file updater_transfer.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_transfer.php */
