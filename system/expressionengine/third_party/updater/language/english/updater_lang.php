<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(
'updater'	=>	'Updater',
'updater_module_name'	=>	 'Updater',
'updater_module_description'=>'Updates ExpressionEngine & Installs/Updates Addons',
'nav_updater' => 'Updater',

'only_super_admins'	=>	'Only Super Admins can access this area',

// General
'ee_install'	=>	'ExpressionEngine Installation',
'addon'			=>	'Addon',
'addons'		=>	'Addons',
'info_req'		=>	'Information & Requirements',
'upload_file'	=>	'Upload File',
'extract_zip'	=>	'Extract Zip',
'backup_log'	=>	'Backup Log',
'move_files'	=>	'Move Files',
'uploading_file'=>	'Uploading please wait..',
'backup_db'		=>	'Backup Database',
'preparing_db'	=>	'Preparing Database Backup',
'preparing_files'	=>	'Preparing Files Backup',
'copy_files_loading' =>	'Copying files, please wait...',
'cleanup'		=>	'Clean Up',
'cleaning_up'	=>	'Doing some house cleaning...',
'types'			=>	'Types',
'version'		=>	'Version',
'post_process'	=>	'Post Processing',
'update_notes'  =>  'Update Notes',
'retry'         =>  'Retry',

'warning'	=>	'Warning!',
'error:no_settings'			=>	'No settings found, please head over to the settings page and review all options and click save.',
'error:test_ajax_failed'	=>	'Our test AJAX request failed! We sent an AJAX request to <strong><a href="{url}" target="_blank">{url}</a></strong> but the response was invalid.',

'upload_final_dest'	=>	'Uploading to final destination...',

'queries_executed'  =>  'SQL Queries Executed',


// FTP, SFTP
'local'		=>	'Local File Transfer',
'ftp'		=>	'FTP',
'sftp'		=>	'SFTP',
'hostname'	=>	'Hostname',
'username'	=>	'Username',
'password'	=>	'Password',
'port'		=>	'Port',
'ssl'		=>	'SSL',
'passive'	=>	'Passive Mode',
'transfer_method'	=>	'File Transfer Method',
'test_transfer_method'	=>	'Test Transfer Method',
'test_settings'	=>	'Test Settings',
'loading_wait'	=>	'Loading, please wait...',

// Actions
'connect'	=>	'Connect',
'chdir'		=>	'Change Dir',
'mkdir'		=>	'Create Dir',
'upload'	=>	'Upload',
'rename'	=>	'Rename',
'delete'	=>	'Delete',

// States
'passed'	=>	'Passed',
'not_passed'=>	'Not Passed',
'done'		=>	'Done',
'failed'	=>	'Failed',
'skipped'	=>	'Skipped',
'waiting'	=>	'Waiting',
'working'	=>	'Working',
'forced'    =>  'Forced',

// Addons
'update_addons'		=>	'Update Addons',
'install_update'	=>	'Install or Update an Addon',
'addon_file'		=>	'Addon File',
'addon_file_url'	=>	'Addon File URL',
'start'				=>	'Start',
'process_log'		=>	'Process Log',
'process_not_started'=>	'The install/update process has not started yet',
'pre_process_log'	=>	'Pre Process Log',

// Addons Log
'addon_zip'		=>	'Valid Addon ZIP File',
'addon_info'	=>	'Addon(s) Information',
'install_update'	=>	'Install/Update Addon(s)',
'install_update_short'	=>	'Install/Update: ',
'installing_addon'	=>	'Installing/Updating the addon, please wait...',
'addon_process_done'=>	'The addon(s) has been successfully installed/updated!',

// Info & Requirements
'current_version'	=>	'Current Version',
'php_zip'			=>	'PHP ZIP Extension Installed',
'post_max_size'		=>	'POST Max Size - Min: 5MB',
'upload_max_size'	=>	'Upload Max Size - Min: 5MB',
'write_system_dir'	=>	'System Dir writable?',
'backup_files'		=>	'Backup ExpressionEngine Files',
'settings_saved'	=>	'Settings Saved?',

// EE
'update_ee'		=>	'Update ExpressionEngine',
'current_ee_version'	=>	'Current Version',
'ee_file'		=>	'Update File',
'ee_file_url'	=>	'Update File URL',
'start_update'	=>	'Start Update',
'ee_zip'		=>	'Valid ExpressionEngine ZIP File',
'ee_info'		=>	'ExpressionEngine Information',
'update_init'	=>	'Update Preparation Log',
'site_off'		=>	'Put Site Offline',
'site_off_loading'=>	'Putting site offline...',
'site_off_loading'=> 'Turning off the system...',
'copy_installer'=>	'Copy Installer Files',
'copy_installer_loading' => 'Copying installer files...',
'wait_installer'	=>	'Wait Server',
'wait_installer_loading'	=>	'Waiting for server to respond.',
'wait_attempts'		=>	'Attempts: <span class="attempts">1</span>',
'update_ee_log'		=>	'ExpressionEngine Update Log',
'update_ee_loading'		=>	'Executing update routines...',
'update_modules'    =>  'Update Modules',
'update_modules_loading' => 'Executing module update routines...',
'update_ee_post'	=>	'Post Update Log',
'copy_ee_files'		=>	'Copy ExpressionEngine Files',
'cleanup_installer'	=>	'Cleanup',
'cleanup_installer_loading'=>	'Removing installer files...',
'update_ee_done'	=>	'ExpressionEngine has been updated!',

'settings'	=>	'Settings',
'update_settings'	=>	'Update Settings',
'backup_location'	=>	'Backup Location',
'config_file_perm'=>	'Set Permissions on config.php/database.php',
'upd:other_options'	=>	'Other Options',

'path_map'		=>	'Path Mapping',
'path_map_exp'	=>	'If FTP or SFTP is used these paths can be different.',

'dir:root' => 'Site Root Dir',
'dir:backup' => 'Backup Dir',
'dir:system' => 'System Dir',
'dir:system_third_party' => 'Third Party Dir',
'dir:themes' => 'Themes Dir',
'dir:themes_third_party' => 'Third Party Themes Dir',

// Errors
'error'			=>	'Error',
'show_error'	=>	'Show Error',
'error:temp_dir_write'		=>	'The temp dir is not writable.<br>Hint: EE Cache Dir',
'error:no_addonfiles'		=>	'No files where uploaded/downloaded!<br>Upload error maybe?',
'error:zip_extension'		=>	'The PHP ZIP Extension is not installed!',
'error:no_addon_detected'	=>	'No addons detected!',

'error:no_file_selected'	=>	'No file was selected/uploaded',
'error:file_upload_error'	=>	'File upload failed',
'error:move_upload'			=>	'Failed to move the uploaded file to the temp dir',
'error:zip_extract_fail'	=>	'Failed to process the ZIP',
'error:no_ee_detected'		=>	'Not a valid ExpressionEngine ZIP file',
'error:ee_version_detect'	=>	'Could not detect ExpressionEngine Version',

'error:local:not_writeable'	=>	'LOCAL: The destination dir is not writeable:<br>%s',
'error:local:mkdir_fail'	=>	'LOCAL: Failed to create the directory',
'error:local:upload_fail'	=>	'LOCAL: Failed to upload/move %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:local:rename_fail'	=>	'LOCAL: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:local:delete_fail'	=>	'LOCAL: Failed to delete %s:<br>%s',

'error:ftp:login'		=>	'FTP: Failed to login',
'error:ftp:chdir_fail'	=>	'FTP: Failed to chdir to:<br>%s',
'error:ftp:mkdir_fail'	=>	'FTP: Failed to create the dir:<br>%s',
'error:ftp:after_mkdir_fail'=>	'FTP: Failed to verify mkdir:<br>%s',
'error:ftp:upload_fail'	=>	'FTP: Failed to upload the %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:ftp:rename_fail'	=>	'FTP: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:ftp:delete_fail'	=>	'FTP: Failed to delete %s:<br>%s',

'error:sftp:login'		=>	'SFTP: Failed to login',
'error:sftp:chdir_fail'	=>	'SFTP: Failed to chdir to:<br>%s',
'error:sftp:mkdir_fail'	=>	'SFTP: Failed to create the dir:<br>%s',
'error:sftp:after_mkdir_fail'=>	'SFTP: Failed to verify mkdir:<br>%s',
'error:sftp:upload_fail'	=>	'SFTP: Failed to upload the %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:sftp:rename_fail'	=>	'SFTP: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:sftp:delete_fail'	=>	'SFTP: Failed to delete %s:<br>%s',




// END
''=>''
);

/* End of file updater_lang.php */
/* Location: ./system/expressionengine/third_party/updater/updater_lang.php */
