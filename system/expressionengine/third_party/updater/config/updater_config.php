<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+

$config['updater_module_defaults']['file_transfer_method'] = 'local';

$config['updater_module_defaults']['ftp']['hostname'] = '';
$config['updater_module_defaults']['ftp']['username'] = '';
$config['updater_module_defaults']['ftp']['password'] = '';
$config['updater_module_defaults']['ftp']['port'] = '21';
$config['updater_module_defaults']['ftp']['passive'] = 'yes';
$config['updater_module_defaults']['ftp']['ssl'] = 'no';

$config['updater_module_defaults']['sftp']['hostname'] = '';
$config['updater_module_defaults']['sftp']['username'] = '';
$config['updater_module_defaults']['sftp']['password'] = '';
$config['updater_module_defaults']['sftp']['port'] = '22';

$config['updater_module_defaults']['path_map']['root'] = '';
$config['updater_module_defaults']['path_map']['backup'] = '';
$config['updater_module_defaults']['path_map']['system'] = '';
$config['updater_module_defaults']['path_map']['system_third_party'] = '';
$config['updater_module_defaults']['path_map']['themes'] = '';
$config['updater_module_defaults']['path_map']['themes_third_party'] = '';

$config['updater_module_defaults']['infinite_memory'] = 'yes';
