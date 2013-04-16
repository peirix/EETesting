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

class Youtube extends Service implements iService {

	var $enabled						= true;
	var $is_authenticated 				= true;
	var $service_key					= "youtube";
	var $service_name 					= "YouTube";

	var $client_id 						= false;
	var $client_secret 					= false;
	var $universal_url 					= "http://www.youtube.com/embed/%s?wmode=transparent";
	var $oauth_redirect_uri 			= false;
	var $oauth_authorization_endpoint 	= 'https://accounts.google.com/o/oauth2/auth';
	var $oauth_token_endpoint 			= 'https://accounts.google.com/o/oauth2/token';

	var $api_options = array(
		'client_id' => false,
		'client_secret' => false,
		'developer_key' => false
	);
	
	var $token_options = array(
		'access_token' => false,
		'token' => false
	);
	
	// --------------------------------------------------------------------
	
	/**
	 * Construct
	 *
	 */
	public function __construct()
	{
		// overload embed_options
		
		$this->embed_options['autohide'] 		= false;
		$this->embed_options['cc_load_policy'] 	= false;
		$this->embed_options['color']			= 'red';
		$this->embed_options['controls']		= true;
		$this->embed_options['disablekb']		= false;
		$this->embed_options['enablejsapi']		= false;
		$this->embed_options['end']				= "";
		$this->embed_options['fs']				= false;
		$this->embed_options['iv_load_policy']	= 3;
		$this->embed_options['modestbranding']	= false;
		$this->embed_options['playerapiid']		= "";
		$this->embed_options['rel']				= true;
		$this->embed_options['showinfo']		= true;
		$this->embed_options['start']			= "";
		$this->embed_options['theme']			= 'dark';
	
	
		$this->model_options['youtube_thumbnail_1'] = false;
		$this->model_options['youtube_thumbnail_2'] = false;
		$this->model_options['youtube_thumbnail_3'] = false;
		$this->model_options['youtube_thumbnail_4'] = false;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Get API Object
	 *
	 * @access	public
	 * @return	object
	 */
	function api_object()
	{
		try {
			$client_id 		= $this->api_options['client_id'];
			$client_secret 	= $this->api_options['client_secret'];
			$refresh_token 	= $this->token_options['token'];
			
			require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/Client.php');
			require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/IGrantType.php');
			require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/AuthorizationCode.php');
			require_once(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/RefreshToken.php');
			
			$client = new \OAuth2\Client($client_id, $client_secret);
		
			$params = array('refresh_token' => $refresh_token);
			
		    $response = $client->getAccessToken($this->oauth_token_endpoint, 'refresh_token', $params);
		    
		    $info = $response['result'];
		    
		    if(!isset($info['access_token']))
		    {
			    return false;
		    }
	
		    $client->setAccessToken($info['access_token']);
		    
		    return $client;
   		}
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Is service authenticated
	 *
	 * @access	public
	 * @return	bool
	 */
	function is_authenticated()
	{
		if($this->token_options['token'] == false)
		{
			return false;
		}
		
		try {
			$api = $this->api_object();
			
			if(!$api)
			{
				return false;
			}
			
			$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites';
			
		    $response = $api->fetch($url);
		    
		    $result = $response['result'];
		    
		    $xml_obj = simplexml_load_string($result);
		    
		    return true;
	    }
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}	
	}
		
	// --------------------------------------------------------------------
	
	function get_profile()
	{
		$api = $this->api_object();
		
	    $response = $api->fetch('https://gdata.youtube.com/feeds/api/users/default/favorites');
	    
	    return $response;
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
	function search($q, $page, $per_page)
	{	
		$start_index = (($page - 1) * $per_page) + 1;
		$max_results = $per_page;
		
		$query = array(
			'q' => $q,
			'start-index' => $start_index,
			'max-results' => $max_results,
		);
		
		
		$method = 'GET';
		
		$url = 'http://gdata.youtube.com/feeds/api/videos';
				
		$api = $this->api_object();
		
		$header = array(
			'Content-Type' => 'application/atom+xml',
			'X-GData-Key' => 'key='.$this->options['developer_key']
		);
		
		$api->setAccessTokenType(2);
		
		$response = $api->fetch($url, $query, $method, $header);
		
	    
	    $result = $response['result'];
	    
	    $xml_obj = simplexml_load_string($result);	    

    
	    $videos = array();
	    
	    foreach($xml_obj->entry as $v)
	    {
		    $video = $this->developerdata($v);
		    
		    array_push($videos, $video);
	    }
	    
	    return $videos;
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
	function get_videos($page, $per_page)
	{
		try
		{
			$start_index = (($page - 1) * $per_page) + 1;
			$max_results = $per_page;
			
			$query = array(
				'start-index' => $start_index,
				'max-results' => $max_results,
			);
			
			
			$method = 'GET';
			
			$url = 'https://gdata.youtube.com/feeds/api/users/default/uploads';
					
			$api = $this->api_object();
			
			$header = array(
				'Content-Type' => 'application/atom+xml',
				'X-GData-Key' => 'key='.$this->options['developer_key']
			);
			
			if(!$api)
			{
				return false;
			}
			
			$api->setAccessTokenType(2);
			
			$response = $api->fetch($url, $query, $method, $header);
		    
		    $result = $response['result'];
		    
		    $xml_obj = simplexml_load_string($result);
	    
		    $videos = array();
		    
		    foreach($xml_obj->entry as $v)
		    {
			    $video = $this->developerdata($v);
			    
			    array_push($videos, $video);
		    }
		    
		    return $videos;
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
	function get_favorites($page = false, $per_page = false)
	{
		try
		{
			$query = array();
			
			if($page && $per_page)
			{
				$start_index = (($page - 1) * $per_page) + 1;
				$max_results = $per_page;
				
				$query = array(
					'start-index' => $start_index,
					'max-results' => $max_results,
				);
			}
			
			
			$method = 'GET';
			
			$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites';
					
			$api = $this->api_object();
			
			$header = array(
				'Content-Type' => 'application/atom+xml',
				'X-GData-Key' => 'key='.$this->options['developer_key']
			);
			
			if(!$api)
			{
				return false;
			}
			
			$api->setAccessTokenType(2);
			
			$response = $api->fetch($url, $query, $method, $header);
			
		    $result = $response['result'];
		    
		    $xml_obj = simplexml_load_string($result);
	    
		    $videos = array();
		    
		    foreach($xml_obj->entry as $v)
		    {
			    $video = $this->developerdata($v);  

			    array_push($videos, $video);
		    }
	
		    return $videos;
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
	function is_favorite($video_id)
	{
		$videos = $this->get_favorites(0,0);
		
		if(!$videos)
		{
			return false;
		}
		
		foreach($videos as $v)
		{

			if($v['id'] == $video_id)
			{
				return true;
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
	function add_favorite($video_id)
	{
		$method = 'POST';
		
		$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites';
		
		$query = '<?xml version="1.0" encoding="UTF-8"?><entry xmlns="http://www.w3.org/2005/Atom"><id>'.$video_id.'</id></entry>';
				
		$api = $this->api_object();
		
		$header = array(
			'Content-Type' => 'application/atom+xml',
			'X-GData-Key' => 'key='.$this->options['developer_key']
		);
		
		$api->setAccessTokenType(2);
		
		$response = $api->fetch($url, $query, $method, $header);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Remove favorite
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function remove_favorite($video_id)
	{
		// get favorites
		
		$query = array();	
		
		$method = 'GET';
		
		$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites';
				
		$api = $this->api_object();
		
		$header = array(
			'Content-Type' => 'application/atom+xml',
			'X-GData-Key' => 'key='.$this->options['developer_key']
		);
		
		if(!$api)
		{
			return false;
		}
		

		
		$api->setAccessTokenType(2);
		
		$response = $api->fetch($url, $query, $method, $header);
		
	    $result = $response['result'];
	    
	    $xml_obj = simplexml_load_string($result);
    
	    $videos = array();
	    
	    $favorite_id = false;

	    foreach($xml_obj->entry as $v)
	    {
	    	$yt = $v->children('http://gdata.youtube.com/schemas/2007');
	    	$v_id = $v->id;
	    	$v_id = substr($v_id, strrpos($v_id, "/") + 1);

	    	if($v_id == $video_id)
	    	{
		    	$favorite_id = (string) $yt->favoriteId;	    	
	    	}
	    }


		// remove favorite
		
		if($favorite_id)
		{
			$method = 'DELETE';
			
			$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites/'.$favorite_id;

			$query = '';
			
			$response = $api->fetch($url, $query, $method, $header);
		}
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
		try {
			$yt = $v->children('http://gdata.youtube.com/schemas/2007');
			$media = $v->children('http://search.yahoo.com/mrss/');
	
			$player = $media->group->player->attributes();
			
			
		    // statistics
		    
			$statistics_view_count =  0;
			
			if($yt->statistics)
			{
				$statistics = $yt->statistics->attributes();
					
				if(isset($statistics['viewCount']))
				{
					$statistics_view_count = $statistics['viewCount'];
				}
			}
			
	
		    // duration
		    
			$media = $v->children('http://search.yahoo.com/mrss/');    	    
		    
		    $yt = $media->children('http://gdata.youtube.com/schemas/2007');
		    
			$duration = $yt->duration->attributes();
	
	
			// author
			
			$author = $v->author;
			
			
			// extract video id from video feed url
			
			$video_id = substr($v->id, strrpos($v->id, "/") + 1);
			
			$video = array();
		    
			$video['id']						= $video_id;
			$video['service_key']				= $this->service_key;
			$video['service_name']				= $this->service_name;
		    
		    $video['url'] 						= 'http://youtu.be/'.$video_id;
		    $video['title'] 					= (string) $v->title;
		    $video['description'] 				= nl2br($media->group->description[0]);
		    $video['date'] 						= strtotime($v->published);
		    $video['plays'] 					= (int) $statistics_view_count;
			$video['duration_seconds'] 			= (int) $duration['seconds'];
			$video['duration'] 					= $this->duration($video['duration_seconds']);
												
			$video['author_username'] 			= (string) $author->name;
			$video['author_name'] 				= (string) $author->name;
			$video['author_url'] 				= "http://youtube.com/user/".$author->name;
												
		    $video['thumbnail'] 				= (string) $media->group->thumbnail[1]->attributes();
		    $video['thumbnail_large'] 			= (string) $media->group->thumbnail[0]->attributes();
		    									
		    $video['youtube_thumbnail_1'] 		= (string) $media->group->thumbnail[0]->attributes();
		    $video['youtube_thumbnail_2'] 		= (string) $media->group->thumbnail[1]->attributes();
		    $video['youtube_thumbnail_3'] 		= (string) $media->group->thumbnail[2]->attributes();
		    $video['youtube_thumbnail_4'] 		= (string) $media->group->thumbnail[3]->attributes();	    
		    
		    return $video;
	    }
	    catch(\Exception $e)
	    {
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
			return false;
	    }
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
		try {
			$url = 'https://gdata.youtube.com/feeds/api/videos/'.$video_id;
			
			if($api = $this->api_object())
			{
				$response = $api->fetch($url);
		
			    $result = $response['result'];
			    
			    $xml_obj = simplexml_load_string($result);	    	
				
			   
			    $video = $this->developerdata($xml_obj);
		
			    return $video;
		    }
		    
		    return false;
	    }
		catch(\Exception $e)
		{
			$this->handle_exception($e, __CLASS__, __FUNCTION__);
		}
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

		$regexp = array('/^https?:\/\/(www\.youtube\.com|youtube\.com|youtu\.be).*\/(watch\?v=)?(.*)/', 3);
		


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

		$service_key = $this->service_key;
		$service = $this;
		
		$client_id 		= $this->api_options['client_id'];
		$client_secret 	= $this->api_options['client_secret'];
		
		$refresh_token 	= $app->get_option($service_key, 'token');
		
		$lib->session_set_userdata('success_url', $this->success_url);
		$lib->session_set_userdata('problem_url', $this->problem_url);

		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/Client.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/IGrantType.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/AuthorizationCode.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/RefreshToken.php');
		
		$state = array();
		$state['oauth2_admin_redirect'] = $_SERVER['REQUEST_URI'];
		$state = json_encode($state);

		
		$state = strtr(base64_encode($state), '+/=', '-_,');
		
		$extra_parameters = array(
			'scope' => "https://gdata.youtube.com",
			'state' => $state,
			'approval_prompt' => 'force',
			'access_type' => 'offline'
		);

		$client = new \OAuth2\Client($client_id, $client_secret);

		if($refresh_token)
		{
		    $api = $this->api_object();
		    
		    if(!$api)
		    {
			    $app->problem($app->lang_line('couldnt_connect')." ".$this->service_name);
			    $app->redirect($this->problem_url);
		    }
		    else
		    {
			    $app->redirect($this->success_url);
		    }
		}
		elseif (!isset($_GET['code']))
		{
		    $auth_url = $client->getAuthenticationUrl($this->oauth_authorization_endpoint, $this->redirect_url, $extra_parameters);
		    //echo $auth_url;

		    $fp = @fopen($auth_url, 'r');
		    
		    
		    if(!$fp)
		    {
			    $app->problem($app->lang_line('couldnt_connect')." ".$this->service_name);
			    $app->redirect($this->problem_url);
		    }	    
		    
		    $app->redirect($auth_url);
		}
		else
		{
			// see connect_callback()
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Connect Callback
	 *
	 * @access	public
	 * @return	void
	 */	
	public function connect_callback($lib, $app)
	{

		$client_id 		= $this->api_options['client_id'];
		$client_secret 	= $this->api_options['client_secret'];
		
		$refresh_token 	= $app->get_option($this->service_key, 'token');
		

		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/Client.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/IGrantType.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/AuthorizationCode.php');
		require(DUKT_VIDEOS_UNIVERSAL_PATH.'libraries/sdks/OAuth2/GrantType/RefreshToken.php');
		
		$state = array();
		$state['oauth2_admin_redirect'] = $_SERVER['REQUEST_URI'];
		$state = json_encode($state);

		
		$state = strtr(base64_encode($state), '+/=', '-_,');
		
		$extra_parameters = array(
			'scope' => "https://gdata.youtube.com",
			'state' => $state,
			'approval_prompt' => 'force',
			'access_type' => 'offline'
		);

		$client = new \OAuth2\Client($client_id, $client_secret);
	
	    $params = array('code' => $_GET['code'], 'redirect_uri' => $this->redirect_url);
	    
	    $response = $client->getAccessToken($this->oauth_token_endpoint, 'authorization_code', $params);

	    $info = $response['result'];
		

		$success_url = $lib->session_userdata('success_url');
		$problem_url = $lib->session_userdata('problem_url');
		
		
	    if(isset($info['refresh_token']))
	    {
			$this->token_options['token'] = $info['refresh_token'];
			
			if($this->check_developer_key())
			{
			    $app->set_option($this->service_key, 'token', $info['refresh_token']);
			    $app->set_option($this->service_key, 'enabled', 1);
			    
			    $app->success($app->lang_line('api_configuration_success'));
			    
			    $app->redirect($success_url);	
			}
			else
			{
			    $app->problem($app->lang_line('wrong_developer_key'));	
			    $app->redirect($problem_url);
			}
				    
	    }
	    elseif(isset($info['error']))
	    {		
		    $app->problem($app->lang_line('api_configuration_problem')." : ".$info['error']);	    
		    $app->redirect($problem_url);
	    }
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Check Developer Key
	 *
	 * @access	private
	 * @return	bool
	 */	
	private function check_developer_key()
	{

		$method = 'GET';
		
		$url = 'https://gdata.youtube.com/feeds/api/users/default/favorites';
		
		$query = '';

		$api = $this->api_object();
		
		$header = array(
			'Content-Type' => 'application/atom+xml',
			'X-GData-Key' => 'key='.$this->api_options['developer_key']
		);

		$api->setAccessTokenType(2);
		
		$response = $api->fetch($url, $query, $method, $header);

	    
	    if(isset($response['code']) && $response['code'] == "200")
	    {
		    return true;
	    }
	    
	    return false;
	}
}


/* END Youtube Class */

/* End of file youtube.php */
/* Location: ./dukt-videos-universal/libraries/services/youtube.php */