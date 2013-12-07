<?php
//if uninstall not called from WordPress exit
if(!defined('WP_UNINSTALL_PLUGIN')) exit();

$db_consumer_key = '_png_consumer_key';
$db_access_token = '_png_access_token';
$db_request_code = '_png_request_code';
$db_format = '_png_format';
$db_tmp_consumer_key = '_png_tmp_consumer_key';
$db_tmp_request_code = '_png_tmp_request_code';

// For Single site
if(!is_multisite()){
	delete_option($db_consumer_key);
	delete_option($db_access_token);
  	delete_option($db_request_code);
  	delete_option($db_format);
  	delete_option($db_tmp_consumer_key);
  	delete_option($db_tmp_request_code);
} 
// For Multisite
else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

  	foreach($blog_ids as $blog_id){
        switch_to_blog($blog_id);
	  
	    delete_option($db_consumer_key);
    	delete_option($db_access_token);
		delete_option($db_request_code);
    	delete_option($db_format);
	    delete_option($db_tmp_consumer_key);
		delete_option($db_tmp_request_code);
		
    }
    switch_to_blog($original_blog_id);
}
?>