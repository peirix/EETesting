<?php

if (! defined('ML_NAME'))
{
	define('ML_NAME', 'Missing Link');
	define('ML_ID', 'missing_link');
	define('ML_VERSION',  '1.4.0');
	define('ML_DESC', 'Manage your site from the frontend.');
	define('ML_DOCS', 'http://www.vayadesign.net/software/missing-link/docs');
	define('ML_SUPPORT', 'https://vayadesign.tenderapp.com');
}

$config['name'] = ML_NAME;
$config['version'] = ML_VERSION;
$config['nsm_addon_updater']['versions_xml']='http://www.vayadesign.net/rss/missing-link';