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

class Vimeo extends Service implements iService {

	var $enabled 			= true;
	var $is_authenticated 	= true;
	var $service_key 		= "vimeo";
	var $service_name 		= "Vimeo";
	var $api_key 			= false;
	var $api_secret 		= false;
	var $universal_url 		= "http://player.vimeo.com/video/%s";
	
	var $api_options = array(
		'api_key' => false,
		'api_secret' => false
	);

	var $token_options = array(
		'oauth_request_token' => false,
		'oauth_request_token_secret' => false,
		'access_token' => false,
		'access_token_secret' => false
	);
	
	// --------------------------------------------------------------------
	
	/**
	 * Construct
	 *
	 */
	public function __construct()
	{
		// overload embed_options
	
		$this->embed_options['byline'] = 1;
		$this->embed_options['color'] = false;
		$this->embed_options['portrait'] = 1;
		$this->embed_options['title'] = 1;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get API Object
	 *
	 * @access	private
	 * @return	object
	 */
	private function api_object()
	{
		$vimeo = false;
		
		if (class_exists('phpVimeo') === false)
		{
			require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/vimeo/vimeo.php');
		}
		
		$api_key 						= $this->api_options['api_key'];
		$api_secret 					= $this->api_options['api_secret'];
		$oauth_request_token			= $this->token_options['oauth_request_token'];
		$oauth_request_token_secret 	= $this->token_options['oauth_request_token_secret'];
		$access_token 					= $this->token_options['access_token'];
		$access_token_secret 			= $this->token_options['access_token_secret'];
		
		
		$vimeo = new \phpVimeo($api_key, $api_secret);

		$vimeo->setToken($access_token, $access_token_secret);
        
        return $vimeo;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Is service authenticated
	 *
	 * @access	public
	 * @return	bool
	 */
	public function is_authenticated()
	{
		try {
			$vimeo = $this->api_object();
			
			$method = 'vimeo.test.login';

			$params = array(

			);

			$r = @$vimeo->call($method, $params);
			
			if(!$r)
			{
				return false;
			}

			return true;
		}
		catch(\Exception $e)
		{
			return false;
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get profile
	 *
	 * @access	public
	 * @return	object
	 */
	public function get_profile()
	{
		$profile = array(
			'display_name' => "",
			'username' => ""
		);	
		
		try {
			$videos = array();

			$vimeo = $this->api_object();
			
			$method = 'vimeo.people.getInfo';

			$params = array();


			//$vimeo->enableCache('file', $this->cache_path(), 5);

			$r = @$vimeo->call($method, $params);

			if($r)
			{
				$profile['display_name'] = $r->person->display_name;
				$profile['username'] = $r->person->username;
			}
			else
			{
				$error = $method;
				$error .= print_r($r, true);
				
				throw new \Exception($error);
			}
		}
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}
		
		return $profile;
	}

	// --------------------------------------------------------------------	

	/**
	 * Search videos
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @param	integer
	 * @return	array
	 */
	public function search($q, $page, $per_page)
	{
		try {
			$videos = array();

			$vimeo = $this->api_object();

			$params = array(
				'query' 		=> $q,
				'full_response'	=> 1,
				'page' 			=> $page,
				'per_page'		=> $per_page,
				'format' 		=> 'php'
			);

			$r = @$vimeo->call('vimeo.videos.search', $params);

			if($r)
			{
				if(isset($r->videos->video))
				{
					foreach($r->videos->video as $v)
					{
						$video = $this->developerdata($v);
						array_push($videos, $video);
					}

					return $videos;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get my videos
	 *
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	array
	 */
	public function get_videos($page, $per_page)
	{

		try {
			$videos = array();

			$vimeo = $this->api_object();

			$params = array(
				'full_response'	=> 1,
				'format' 		=> 'php',
				'page'			=> $page,
				'per_page'			=> $per_page
			);

			$r = @$vimeo->call('vimeo.videos.getUploaded', $params);

			if($r)
			{
				if(isset($r->videos->video))
				{
					foreach($r->videos->video as $v)
					{
						$video = $this->developerdata($v);

						array_push($videos, $video);
					}

					return $videos;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get favorite videos
	 *
	 * @access	public
	 * @param	integer
	 * @param	integer
	 * @return	array
	 */
	public function get_favorites($page, $per_page)
	{
		try {

			$videos = array();

			$vimeo = $this->api_object();

			$params = array(
				'full_response'	=> 1,
				'format' 		=> 'php',
			);

			if($page > 0)
			{
				$params['page'] = $page;
			}

			if($per_page > 0)
			{
				$params['per_page'] = $per_page;
			}

			$r = @$vimeo->call('vimeo.videos.getLikes', $params);

			if($r)
			{
				if(isset($r->videos->video))
				{
					foreach($r->videos->video as $v)
					{

						$video = $this->developerdata($v);

						array_push($videos, $video);
					}

					return $videos;
				}
			}
		}
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Is favorite ?
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_favorite($video_id)
	{
		$videos = $this->get_favorites(0,0);

		if($videos)
		{
			foreach($videos as $v)
			{

				if($v['id'] == $video_id)
				{
					return true;
				}
			}
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Add favorite
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function add_favorite($video_id)
	{
		$vimeo = $this->api_object();

		$params = array(
			'video_id' 		=> $video_id,
			'like'			=> 1
		);

		$vimeo->call('vimeo.videos.setLike', $params);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Remove favorite
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function remove_favorite($video_id)
	{
		$vimeo = $this->api_object();

		$params = array(
			'video_id' 		=> $video_id,
			'like'			=> 0
		);

		$vimeo->call('vimeo.videos.setLike', $params);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get video developer data
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	private function developerdata($v)
	{

		$video = array();
		
		$video['id']						= $v->id;
		$video['service_key']				= $this->service_key;
		$video['service_name']				= $this->service_name;
		
		$video['url']				= 'http://vimeo.com/'.$v->id;
		$video['title'] 			= (string) $v->title;
		$video['description'] 		= (string) nl2br($v->description);
		$video['author_username']	= (string) $v->owner->username;
		$video['author_name']		= (string) $v->owner->display_name;
		$video['author_url']		= (string) $v->owner->profileurl;
		$video['date'] 				= (string) $v->upload_date;
		$video['plays'] 			= (string) $v->number_of_plays;
		$video['duration_seconds']	= (string) $v->duration;
		$video['duration'] 		   	= $this->duration($video['duration_seconds']);
		$video['thumbnail'] 		= (string) $v->thumbnails->thumbnail[0]->_content;
		
		$video['thumbnail_large'] = end($v->thumbnails->thumbnail);
		$video['thumbnail_large'] = $video['thumbnail_large']->_content;
		
		foreach($v->thumbnails->thumbnail as $k => $thumbnail)
		{
			$video_variable_key = $this->service_key."_thumbnail_".($k + 1);
			
			$video[$video_variable_key] = $thumbnail->_content;
		}

		$video['date'] = strtotime($video['date']);
			
		return $video;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get video meta data
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	public function metadata($video_id)
	{
		// api call

		try {

			$videos = array();

			$vimeo = $this->api_object();

			$method = 'vimeo.videos.getInfo';

			$params = array(
				'video_id' => $video_id
			);

			$r = @$vimeo->call($method, $params);

			if($r)
			{
				$v = $r->video[0];

				// assign new variables
				$video = $this->developerdata($v);				
								
				return $video;
			}
		}
		catch(Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}

		return false;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get video id from url
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function get_video_id($url)
	{

		// check if url works with this service and extract video_id
		$video_id = false;

		$regexp = array('/^https?:\/\/(www\.)?vimeo\.com\/([0-9]*)/', 2);


		if(preg_match($regexp[0], $url, $matches, PREG_OFFSET_CAPTURE) > 0)
		{

			// regexp match key

			$match_key = $regexp[1];


			// define video id

			$video_id = $matches[$match_key][0];


			// Fixes the youtube &feature_gdata bug

			if(strpos($video_id, "&"))
			{
				$video_id = substr($video_id, 0, strpos($video_id, "&"));
			}
		}

		// here we should have a valid video_id or false if service not matching

		return $video_id;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Connect
	 *
	 * @access	public
	 * @return	void
	 */
	public function connect($lib, $app)
	{	

		if(!isset($_GET['oauth_verifier']))
		{
			// STEP 1: Get a Request Token
	        // $callback_url = urlencode($callback_url);
			// $api_obj->setToken('', '');
			
			$lib->session_set_userdata('oauth_request_token', null);
			$lib->session_set_userdata('oauth_request_token_secret', null);

            $app->set_option($this->service_key, 'access_token', null);
            $app->set_option($this->service_key, 'access_token_secret', null);
            
			$this->options['access_token'] = '';
			$this->options['access_token_secret'] = '';
			
			$api_obj = $this->api_object();

			$api_obj->setToken('', '');
			
			$success_url = $this->success_url;
			$problem_url = $this->problem_url;
			
			$redirect_url = $this->redirect_url;
			
			$lib->session_set_userdata('success_url', $success_url);
			$lib->session_set_userdata('problem_url', $problem_url);

	        $token = $api_obj->getRequestToken($redirect_url);

	        if(isset($token['oauth_token']))
	        {

				$lib->session_set_userdata('oauth_request_token', $token['oauth_token']);
				$lib->session_set_userdata('oauth_request_token_secret', $token['oauth_token_secret']);

				
				// STEP 2: Authorize the Request Token
				
		        $authorize_link = $api_obj->getAuthorizeUrl($token['oauth_token'], 'write');
		        
		        $app->redirect($authorize_link);
	        }
	        else
	        {
				$vimeo = $this->api_object();
				
				$method = 'vimeo.test.login';
	
				$params = array(
	
				);
				
				try {
					$r = @$vimeo->call($method, $params);
					
					if(!$r)
					{
					    $app->problem($app->lang_line('couldnt_connect')." ".$this->service_name);
					}
					else
					{
					    $app->problem($app->lang_line('wrong_api'));	
					}
				}
				catch(\Exception $e)
				{
					$app->problem($app->lang_line('wrong_api'));	
				}	        

		        $app->redirect($this->problem_url);
	        }

		}
		else
		{
			// STEP 3 : Exchange the Authorized Request Token for a Long-Term Access Token.
			// see connect_callback()
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Connect Callback
	 * Exchange the Authorized Request Token for a Long-Term Access Token.
	 *
	 * @access	public
	 * @return	void
	 */	
	public function connect_callback($lib, $app)
	{
		$api = $this->api_object();
		
		$success_url 			= $lib->session_userdata('success_url');
		$problem_url 			= $lib->session_userdata('problem_url');

		$request_token 			= $lib->session_userdata('oauth_request_token');	
		$request_token_secret 	= $lib->session_userdata('oauth_request_token_secret');

        $api->setToken($request_token, $request_token_secret);
        
        $token = $api->getAccessToken($_GET['oauth_verifier']);	 
    
        if(isset($token['oauth_token']))
        {	            
            $app->set_option($this->service_key, 'oauth_request_token', $request_token);
            $app->set_option($this->service_key, 'oauth_request_token_secret', $request_token_secret);
            
            $app->set_option($this->service_key, 'access_token', $token['oauth_token']);
            $app->set_option($this->service_key, 'access_token_secret', $token['oauth_token_secret']);
		    
		    $app->set_option($this->service_key, 'enabled', 1);
		    
            $app->success($app->lang_line('api_configuration_success'));
            $app->redirect($success_url); 
        }
        else
        {
		    $app->problem($app->lang_line('wrong_api'));
		    $app->redirect($problem_url); 
        }


	}
}

/* END Vimeo Class */

/* End of file vimeo.php */
/* Location: ./dukt-videos-universal/libraries/services/vimeo.php */