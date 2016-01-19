<?php
/*
Version: 0.1
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! class_exists( 'PocketUtil' ) ) :

class PocketUtil {

	const version = '0.1';
  
  	//Option ID
  	const OPT_STATE = 'state';
  	const OPT_FAVORITE = 'favorite';
  	const OPT_TAG = 'tag';
  	const OPT_CONTENT_TYPE = 'contentType';
  	const OPT_SORT = 'sort';
  	const OPT_DETAIL_TYPE = 'detailType';
	const OPT_SEARCH = 'search';
  	const OPT_DOMAIN = 'domain';
  	const OPT_SINCE = 'since';
  	const OPT_COUNT = 'count';
  	const OPT_OFFSET = 'offset';
  
  	//Response ID
  	const REF_ITEM_ID = 'item_id';
  	const REF_RESOLVED_ID = 'resolved_id';
  	const REF_GIVEN_URL = 'given_url';
  	const REF_RESOLVED_URL = 'resolved_url';
  	const REF_GIVEN_TITLE = 'given_title';
  	const REF_RESOLVED_TITLE = 'resolved_title';
  	const REF_FAVORITE = 'favorite';
  	const REF_STATUS = 'status';
  	const REF_EXCERPT = 'excerpt';
  	const REF_IS_ARTICLE = 'is_article';
  	const REF_HAS_IMAGE = 'has_image';
  	const REF_HAS_VIDEO = 'has_video';
  	const REF_WORD_COUNT = 'word_count';
  	const REF_TAGS = 'tags';
  	const REF_AUTHORS = 'authors';
  	const REF_IMAGES = 'images';
  	const REF_VIDEOS = 'videos';
  
	function __construct() {

	}

    public function getUrl( $path = '' ) {
		return plugins_url( ltrim( $path, '/' ), __FILE__ );
	}
  
  	function getBaseUrl($url){
	  	$result = parse_url($url);
	  	return $result['scheme']."://".$result['host'];
	}
  
  	function getSiteName($url){
	  
	  	$base_url = self::getBaseUrl($url);
	  
	  	$html = file_get_contents ($base_url);
	  
	  	mb_detect_order("utf-8, euc-jp, sjis, jis, ascii");
	  	$charset = strtolower(mb_detect_encoding($html));
	  	$html = str_replace("\r", "", $html);
	  	$html = str_replace("\n", "", $html);
	  	
	  	if ($charset <> "utf-8") {
		  	$html = mb_convert_encoding($html, "UTF-8", $charset);
		}
	  	
	  	$pattern = "<title>(.*)<\/title>";
	  
	  	if (preg_match ( "/".$pattern."/i", $html, $match )) {
		  	$name = $match[1];
		  	return $name;
		} else {
		  	return;
		} 
	  
	}
  
    public function getPath( $path = '' ) {
		return dirname(__FILE__) . '/' . $path;
	}

   /**
  	* Retrieve item(s) from a user’s Pocket list through Pocket API
	*
    * @param  string $consumer_key
    * @param  string $access_token
	* @param  array	 $option_params
	*/
  	public function retrieveItem($consumer_key,$access_token,$option_params = array()){
        $required_params = array(
            'consumer_key'  => $consumer_key,
            'access_token'  => $access_token
        );
	  	
	  //echo 'hogehoge';
	  
	  	//HTTP body
		$body = array_merge($required_params,$option_params);
	  
	  	//HTTP header
        $header = array(
        	'Content-Type'=>'application/json; charset=UTF8',
        	'X-Accept'=>'application/json');

        $options = array(
        	'headers' => $header,
        	'body' => json_encode($body)
        	);

	  	//Method URL
        $query = 'https://getpocket.com/v3/get';
	  
	  	//Retrieve the URL using the HTTP POST method
	  	$response = wp_remote_post($query,$options);
	  
	  	if( !is_wp_error($response) && $response['response']['code'] === 200 ){
		  //echo $response['body'];
		  return json_decode($response['body']);
		} else {
		  return;
		}
  	}
  
    /**
     * Get the initial request code for the OAuth process
     *
     * @param  string $consumer_key
     */
    public function getRequestCode($consumer_key,$redirect_uri){
        $required_params = array(
            'consumer_key'  => $consumer_key,
            'redirect_uri'  => $redirect_uri
        );
	  
	  	//HTTP body
        $body = $required_params;

	  	//HTTP header
        $header = array(
        	'Content-Type'=>'application/json; charset=UTF8',
        	'X-Accept'=>'application/json');

        $options = array(
        	'headers' => $header,
        	'body' => json_encode($body)
        	);

	  	//Method URL
        $query = 'https://getpocket.com/v3/oauth/request';

	  	//Retrieve the URL using the HTTP POST method
        $response = wp_remote_post($query,$options);

        if( !is_wp_error($response) && $response['response']['code'] === 200 ){
		 	$response_body = json_decode($response['body']);
		 	$request_token = $response_body->code;
		 	return $request_token;
		}else{
		  	return;
		}
    }

    /**
     * Get an access token through the Pocket API
     *
     * @param  string $consumer_key
     * @param  string $request_token
     */
    public function getAccessToken($consumer_key, $request_token){
       	$required_params = array(
            'consumer_key'  => $consumer_key,
            'code'  => $request_token
        );
	  
		//HTTP body
        $body = $required_params;

	  	//HTTP header
        $header = array(
        	'Content-Type'=>'application/json; charset=UTF8',
        	'X-Accept'=>'application/json');

        $options = array(
        	'headers' => $header,
        	'body' => json_encode($body)
        	);
	  
		//Method URL
        $query = 'https://getpocket.com/v3/oauth/authorize';
	  
	  	//Retrieve the URL using the HTTP POST method
	  	$response = wp_remote_post($query,$options);

		if( !is_wp_error($response) && $response['response']['code'] === 200 ){
		  	$response_body = json_decode($response['body']);
		  	$access_token = $response_body->access_token;
		  	return $access_token;
		}else{
		  	return;
		}	  
    }
}

endif;
