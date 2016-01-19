<?php
/*
Version: 0.1
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! class_exists( 'FeedlyUtil' ) ) :

class FeedlyUtil {

	const version = '0.1';
  
  	//Response ID
  	const REF_SITE_NAME = 'title';
  	const REF_SITE_URL = 'website';
  
	function __construct() {

	}

	/**
  	 * Retrieve site basic information through Feedly Cloud API
	 *
     * @param  string $url
	 */  
  	public function getSiteInfo($url){
	  
	  	if(empty($url)) return;
	  
	  	$query = 'http://cloud.feedly.com/v3/search/feeds?q=' . $url . '&count=1';
	  
	  	//Retrieve the URL using the HTTP POST method
	  	$response = wp_remote_get($query);
	  
	  	if( !is_wp_error($response) && $response['response']['code'] === 200 ){
		  
		  return json_decode($response['body']);
		  
		} else {
		  
		  return;
		  
		}
	  
	}
  
}

endif;
