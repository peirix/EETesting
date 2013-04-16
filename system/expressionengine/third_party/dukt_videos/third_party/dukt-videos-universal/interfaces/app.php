<?php

/**
 * Dukt Videos
 *
 * @package		Dukt Videos
 * @version		Version 1.0
 * @author		Benjamin David
 * @copyright	Copyright (c) 2013 - DUKT
 * @link		http://dukt.net/videos/
 *
 */

namespace DuktVideos;

// ------------------------------------------------------------------------

/**
 * App Interface
 *
 */
interface iApp
{
    public static function callback_url($service_key);
    public static function get_service($service_key);
    public static function get_services();
    public static function get_video($video_url);
	public static function get_option($service, $k, $default);
	public static function set_option($service, $k, $v);
	public static function redirect($url);
	public static function current_language();
	public static function lang_line($k);
	public static function cache_path();
	public static function developer_log($msg);
	public function success($msg);
	public function problem($msg);
}

/* END App Interface */

/* End of file app.php */
/* Location: ./dukt-videos-universal/interfaces/app.php */