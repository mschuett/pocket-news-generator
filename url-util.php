<?php
/*
Version: 0.1
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! class_exists( 'URLUtil' ) ) :

class URLUtil {

	const version = '0.1';
  
  	//Response ID
  	const REF_SITE_NAME = 'title';
  	const REF_SITE_URL = 'website';
  
  	private $domain_url;  
  	private $path_count;
  	private $paths;
  
	public function __construct($url){
	  			
	  	$url_attr = parse_url($url);
	  	$this->domain_url = $url_attr['scheme'] . '://' . $url_attr['host'];
	  
	  	if($url_attr['path']==='/'){
		  
		  	$this->path_count = 0;
		  
		}else{
		  
			$this->paths = explode('/',$url_attr['path']);
			$this->paths = array_filter($this->paths,'strlen');
		  	$this->paths = array_values($this->paths);
		  	$this->path_count = count($this->paths);
		  
		}
	}
  
  	/**
  	 * Get URL
	 *
	 */
  	public function getURL(){
	  
	  for($i=0;$i<$this->path_count;$i++){
		$new_path = $new_path . '/' . $this->paths[$i];
	  }
	  
	  return $this->domain_url . $new_path;
	  
  	}

  	/**
  	 * Move up URL path
	 *
	 */  
  	public function next(){
	  
	  if($this->path_count>0){
		
		$this->path_count = $this->path_count - 1;
		return true;
		
	  }else{
		
		return false;
		
	  }
	  
  	}
  
}

endif;
