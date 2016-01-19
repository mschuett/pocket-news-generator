<?php
/*
Plugin Name: Pocket News Generator
Plugin URI: http://wordpress.org/plugins/pocket-news-generator/
Description: This plugin retrieves your Pocket data based on specified condition and generates its HTML code according to specified format automatically. This makes it possible to create an entry which introduces bookmarked articles efficiently.  
Version: 0.2.0
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: pocket-news-generator
Domain Path: /languages
*/

if (!class_exists('PocketNewsGenerator')){
  
class PocketNewsGenerator{
 
	/**
	 * Plugin version
	 * @var string
	 */
	static $version = '0.2.0';
  
  	const DOMAIN = 'pocket-news-generator';
  
 	const DB_CONSUMER_KEY = '_png_consumer_key';
	const DB_ACCESS_TOKEN = '_png_access_token';
	const DB_REQUEST_CODE = '_png_request_code';
	const DB_FORMAT = '_png_format';

  	const DB_TMP_CONSUMER_KEY = '_png_tmp_consumer_key';
	const DB_TMP_ACCESS_TOKE = '_png_tmp_access_token';
	const DB_TMP_REQUEST_CODE = '_png_tmp_request_code';

	const FRMT_SITE_NAME = '${SITE_NAME}';
  	const FRMT_SITE_URL = '${SITE_URL}';
  
  	const FRMT_POST_URL = '${POST_URL}';
  	const FRMT_POST_TITLE = '${POST_TITLE}';
	const FRMT_POST_EXCERPT = '${POST_EXCERPT}';
  	const FRMT_POST_IMAGE = '${POST_IMAGE}';
  	const FRMT_POST_TAGS = '${POST_TAGS}';
  
    /**
     * Class constarctor
     * Hook onto all of the actions and filters needed by the plugin.
     */
    function __construct() {
	  	load_plugin_textdomain(self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages');

	    add_action('admin_menu', array(&$this, 'action_admin_menu'));
	  
		require_once (dirname(__FILE__) . '/pocket-util.php');
	  	require_once(dirname(__FILE__) . '/feedly-util.php');	  		  
		require_once(dirname(__FILE__) . '/url-util.php');
		require_once(dirname(__FILE__) . '/OpenGraph.php');
	  
	  	if(is_admin()){
			wp_enqueue_style('pocket-news-generator', plugins_url(ltrim('pocket-news-generator.css', '/'), __FILE__));
		}
	}
  
    /**
     * Adds options & management pages to the admin menu.
     *
     * Run using the 'admin_menu' action.
     */
    public function action_admin_menu() {
	    $page = add_options_page('Pocket News Generator', 'Pocket News Generator', 8, 'pocket_news_generator_options_page',array(&$this, 'option_page'));
	    $page = add_management_page('Pocket News Generator', 'Pocket News Generator', 8, 'pocket_news_generator_generation_page', array(&$this, 'code_generation_page'));
    }

    /**
     * Displays the manage page for the plugin.
     */
  	public function option_page(){
	  
	  	$redirect_uri_base = admin_url() . 'options-general.php?page=pocket_news_generator_options_page';
	  	$redirect_uri_auth = admin_url() . 'options-general.php?page=pocket_news_generator_options_page&action=authentication&status=success';
	  
  	  	if(isset($_POST["action"]) && $_POST["action"]==='register'){
		  
		  	$consumer_key = $_POST["consumer_key"];
		  	$access_token = $_POST["access_token"];
		  	$format = $_POST["format"];
		  
			if(isset($consumer_key) && $consumer_key){
			  	update_option(self::DB_CONSUMER_KEY,$consumer_key);
			}
			if(isset($access_token) && $access_token){
			  	update_option(self::DB_ACCESS_TOKEN,$access_token);
			}
			if(isset($format) && $format){
			  	update_option(self::DB_FORMAT,$format);
			}
	  	}
	  
	  	$consumer_key = get_option(self::DB_CONSUMER_KEY);
		$access_token = get_option(self::DB_ACCESS_TOKEN);
		$format = get_option(self::DB_FORMAT);
	  
	  	?>
	  	<div class="wrap">
		  <h2 class="pkt-nws-gnrtr"><?php _e('Current Parameter', self::DOMAIN) ?></h2>
		  <p><?php _e('The following describes registered parameters.', self::DOMAIN) ?></p>
		  <table class="widefat">
			<thead>
			  <tr>
				<th><?php _e('Name', self::DOMAIN) ?></th>
				<th><?php _e('Value', self::DOMAIN) ?></th>
			  </tr>
			</thead>
			<tbody>
			  <tr>
				<td><?php _e('Consumer Key', self::DOMAIN) ?></td><td><?php echo $consumer_key ?></td>
			  </tr>
			  <tr>
				<td><?php _e('Access Token', self::DOMAIN) ?></td><td><?php echo $access_token ?></td>
			  </tr>
			  <tr>
				<td><?php _e('HTML Format', self::DOMAIN) ?></td><td><pre><?php echo stripslashes(htmlspecialchars($format)) ?></pre></td>
			  </tr>
			</tbody>
		  </table>
		</div>
		<br />
		<div class="wrap">
		  <h2 class="pkt-nws-gnrtr"><?php _e('Register New Parameter', self::DOMAIN) ?></h2>
		  <p><?php _e('You can register or modify required parameters at the following form.', self::DOMAIN) ?></p>
		  <div class="pkt-nws-gnrtr">
			<form action="" method="post">
			  <div>
				<label><?php _e('Consumer Key', self::DOMAIN) ?></label><br />
				<input type="text" class="text" name="consumer_key" size="60" value="" />
			  </div>
			  <div>
				<label><?php _e('Access Token', self::DOMAIN) ?></label><br />
				<input type="text" class="text" name="access_token" size="60" value="" />
			  </div>
			  <div>
				<label><?php _e('HTML Format', self::DOMAIN) ?></label><br />
				<textarea name="format" class="text" cols="60" rows="5"></textarea>
			  </div>
			  <input type="hidden" class="text" name="action" value="register" />
			  <div>
				<input type="submit" class="button button-primary" value="<?php _e('Register', self::DOMAIN) ?>" />
			  </div>
			</form>
			<br />
			<p><?php _e('The parameter "HTML Format" indicates format of generated HTML, and the following reserved keywords are utilized to refer to retrieved Pocket data.', self::DOMAIN) ?></p>
			<table class="widefat">
				<thead>
			  		<tr>
						<th><?php _e('Reserved Keyword', self::DOMAIN) ?></th>
						<th><?php _e('Description', self::DOMAIN) ?></th>
			  		</tr>
				</thead>
				<tbody>
			  		<tr>
						<td>${POST_TITLE}</td><td><?php _e('Title of bookmarked item', self::DOMAIN) ?></td>
			  		</tr>
			  		<tr>
						<td>${POST_URL}</td><td><?php _e('URL of bookmarked item', self::DOMAIN) ?></td>
			  		</tr>
			  		<tr>
						<td>${POST_EXCERPT}</td><td><?php _e('Excerpt from bookmarked item', self::DOMAIN) ?></td>
			  		</tr>
			  		<tr>
					  <td>${SITE_NAME}</td><td><?php _e('Web site name giving bookmarked item', self::DOMAIN) ?></td>
			  		</tr>
			  		<tr>
					  <td>${SITE_URL}</td><td><?php _e('Web site URL giving bookmarked item', self::DOMAIN) ?></td>
			  		</tr>
				</tbody>
		  	</table>
			<br />
			<label><?php _e('Format Sample', self::DOMAIN) ?></label><br />
			<textarea class="text" style="width:500px;height:260px;">
&lt;div class=&quot;pocket-post&quot;&gt;
&lt;div class=&quot;pocket-site-name&quot; style=&quot;width: 100%; font-size: 120%; padding: 5px; margin: 10px 0; background-color: #50bcb6;&quot;&gt;
&lt;a href=&quot;${SITE_URL}&quot; target=&quot;_blank&quot; style=&quot;text-decoration: none; color: #fff;&quot;&gt;${SITE_NAME}&lt;/a&gt;
&lt;/div&gt;
&lt;div class=&quot;pocket-thumbnail&quot; style=&quot;float:left;&quot;&gt;
&lt;a href=&quot;${POST_URL}&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;http://capture.heartrails.com/150x130/shadow?${POST_URL}&quot; alt=&quot;${POST_TITLE}&quot;&gt;&lt;/a&gt;
&lt;/div&gt;
&lt;div class=&quot;pocket-title&quot;&gt;
&lt;a href=&quot;${POST_URL}&quot; target=&quot;_blank&quot;&gt;${POST_TITLE}&lt;/a&gt;&lt;img border=&quot;0&quot; src=&quot;http://b.hatena.ne.jp/entry/image/${POST_URL}&quot; alt=&quot;&quot; /&gt;
&lt;/div&gt;
&lt;div style=&quot;clear:both&quot;&gt;&lt;/div&gt;
&lt;div class=&quot;pocket-excerpt&quot;&gt;
&lt;blockquote&gt;${POST_EXCERPT}&lt;/blockquote&gt;
&lt;/div&gt;
&lt;/div&gt; 
</textarea>
		  </div>		  
		</div>
		<br />
		<?php
	  
	  	if(isset($_POST["action"]) && $_POST["action"]==='request_code'){
			
		  	$pocket_util = new PocketUtil();
		
			$tmp_consumer_key = $_POST["consumer_key"];
		  	$tmp_request_code = $pocket_util->getRequestCode($tmp_consumer_key,$redirect_uri_base);
		  		  		  	
		  	if(!empty($tmp_request_code)){
			  //echo '<p>request code: ' . $request_code . '</p>';
			  
			  update_option(self::DB_TMP_REQUEST_CODE,$tmp_request_code);
			  update_option(self::DB_TMP_CONSUMER_KEY,$tmp_consumer_key);
			  
			  $request_code_flag = true;
			   
			} else {
			  $request_code_flag = false;
			}
		  
	  	} else if(isset($_GET["action"]) && $_GET["action"]==='authentication'){
		  
		  	if(isset($_GET["status"]) && $_GET["status"]==='success'){
			  $authentication_flag = true;
			} else {
			  $authentication_flag = false;
			}
		  
		} else if(isset($_POST["action"]) && $_POST["action"]==='access_token'){
			
		  	$pocket_util = new PocketUtil();
		  
			$tmp_consumer_key = get_option(self::DB_TMP_CONSUMER_KEY);
		  	$tmp_request_code = get_option(self::DB_TMP_REQUEST_CODE);
			$tmp_access_token = $pocket_util->getAccessToken($tmp_consumer_key,$tmp_request_code);
		  	
			if(!empty($tmp_access_token)){
			  $result_consumer_key = $tmp_consumer_key;
			  $result_access_token = $tmp_access_token;
			  $access_token_flag = true;
			} else {
			  $access_token_flag = false;
			}
		
		  	delete_option(self::DB_TMP_REQUEST_CODE);
		  	delete_option(self::DB_TMP_CONSUMER_KEY);
		  
	  	}
	  	   
	  	?>
		<div class="wrap">
		  <h2 class="pkt-nws-gnrtr"><?php _e('Get Consumer Key and Access Token', self::DOMAIN) ?></h2>
		  <p><?php _e('You need to obtain your cusumer key and access token according to the guide below.', self::DOMAIN) ?></p>
		  <div class="pkt-nws-gnrtr">
			<h3><?php _e('Step 1. Obtain a platform consumer key', self::DOMAIN) ?></h3>
			<p><?php _e('You need to publish your cusumer key from the following site.', self::DOMAIN) ?></p>
			<a href="http://getpocket.com/developer/apps/" target="_blank">Pocket My Applications</a>
			<h3><?php _e('Step 2. Obtain a request token', self::DOMAIN) ?></h3>
			<p><?php _e('Input the obtained cosumer key and push the button below to get a request token.', self::DOMAIN) ?></p>
			<form action="" method="post">
			  <div>
				<label>Consumer Key</label><br />
				<input type="text" class="text" name="consumer_key" size="60" value="" />
			  </div>
			  <input type="hidden" class="text" name="action" value="request_code" />
			  <div>
				<input type="submit" class="button button-primary" value="<?php _e('Get Request Code', self::DOMAIN) ?>" />
			  </div>
			</form>
			<?php 
	  			if(isset($request_code_flag)){
					if($request_code_flag){
		  				echo '<span class="green">' . __('INFO: Request token retrieval succeeded.', self::DOMAIN) . '</span>';
					} else {
				  		echo '<span class="red">' . __('ERROR: Request token retrieval failed.', self::DOMAIN) . '</span>';
					}
				}
			?>
			<h3><?php _e('Step 3. Authenticate your request token in Pocket', self::DOMAIN) ?></h3>
			<p><?php _e('After the above Step 2, push the following button whitch authorizes your application\'s request token.', self::DOMAIN) ?></p>
			<form action="https://getpocket.com/auth/authorize" method="get">
			  <input type="hidden" class="text" name="request_token" value="<?php echo $tmp_request_code ?>" />
			  <input type="hidden" class="text" name="redirect_uri" value="<?php echo $redirect_uri_auth ?>" />
			  <div>
				<input type="submit" class="button button-primary" value="<?php _e('Authorize Request Token', self::DOMAIN) ?>" />
			  </div>
			</form>	
			<?php 
			  	if(isset($authentication_flag)){
					if($authentication_flag){
		  				echo '<span class="green">' . __('INFO: Authentication of request token succeeded.', self::DOMAIN) . '</span>';
					} else {
				  		echo '<span class="red">' . __('ERROR: Authentication of request token failed.', self::DOMAIN) . '</span>';
					}
				}
			?>
			<h3><?php _e('Step 4. Convert a request token into a Pocket access token', self::DOMAIN) ?></h3>
			<p><?php _e('After the above Step 3, push the following button in order to convert the authenticated request token into a Pocket access token.', self::DOMAIN) ?></p>
			<form action="<?php echo $redirect_uri_base ?>" method="post">
			  <input type="hidden" class="text" name="action" value="access_token" />
			  <div>
				<input type="submit" class="button button-primary" value="<?php _e('Get Access Token', self::DOMAIN) ?>" />
			  </div>
			</form>
			<?php 
	  			if(isset($access_token_flag)){
					if($access_token_flag){
		  				echo '<span class="green">' . __('INFO: Conversion from request token to access token succeeded.', self::DOMAIN) . '</span>';
					} else {
				  		echo '<span class="red">' . __('ERROR: Conversion from request token to access token failed.', self::DOMAIN) . '</span>';
					}
				}
			?>			
			<h3><?php _e('Step 5. Register the consumer key and access token below".', self::DOMAIN) ?></h3>
			<p><?php _e('After the above Step 4, register the following parameters at the section titled "Register New Parameter.',  self::DOMAIN) ?></p>
			<p>Consumer Key: <span class="red"><?php echo $result_consumer_key ?></span></p>
			<p>Access Token: <span class="red"><?php echo $result_access_token ?></span></p>
		  </div>
		</div>
		<?php
	}

    /**
     * Displays the page for generating HTML code for pocket entry
	 */
	public function code_generation_page(){
	  
	  ?>
	  <div class="wrap">
		<h2 class="pkt-nws-gnrtr"><?php _e('Retrieve Items in Pocket and Generate HTML Code', self::DOMAIN) ?></h2>
		<p><?php _e('Specify search condition for Pocket data retrieval and push the button below.', self::DOMAIN) ?></p>
		<div class="pkt-nws-gnrtr">
		  <form action="" method="post">
			<div>
			  <label><?php _e('State', self::DOMAIN) ?></label><br />
			  <select name="<?php echo PocketUtil::OPT_STATE ?>" class="dropdown">
				<option value="all" selected><?php _e('all (both unread and archived items)', self::DOMAIN) ?></option>
				<option value="unread"><?php _e('only unread items', self::DOMAIN) ?></option>
				<option value="archive"><?php _e('only archived items', self::DOMAIN) ?></option>
			  </select>
			</div>
			<div>
			  <label><?php _e('Favorite', self::DOMAIN) ?></label><br />
			  <select name="<?php echo PocketUtil::OPT_FAVORITE ?>" class="dropdown">
				<option value ="" selected><?php _e('all (both un-favorited and favorited items)', self::DOMAIN) ?></option>
				<option value="0"><?php _e('only un-favorited items', self::DOMAIN) ?></option>
				<option value="1"><?php _e('only favorited items', self::DOMAIN) ?></option>
			  </select>
			</div>
			<div>
			  <label><?php _e('Tag', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_TAG ?>" size="60" value="" />
			</div>
			<div>
			  <label><?php _e('Content Type', self::DOMAIN) ?></label><br />
			  <select name="<?php echo PocketUtil::OPT_CONTENT_TYPE ?>" class="dropdown">
				<option value ="" selected><?php _e('all', self::DOMAIN) ?></option>
				<option value="article"><?php _e('only articles', self::DOMAIN) ?></option>
				<option value="video"><?php _e('only videos or articles with embedded videos', self::DOMAIN) ?></option>
				<option value="image"><?php _e('only images', self::DOMAIN) ?></option>
			  </select>
			</div>
			<div>
			  <label><?php _e('Sort', self::DOMAIN) ?></label><br />
			  <select name="<?php echo PocketUtil::OPT_SORT ?>" class="dropdown">
				<option value ="newest" selected><?php _e('items in order of newest to oldest', self::DOMAIN) ?></option>
				<option value="oldest"><?php _e('items in order of oldest to newest', self::DOMAIN) ?></option>
				<option value="title"><?php _e('items in order of title alphabetically', self::DOMAIN) ?></option>
				<option value="site"><?php _e('items in order of URL alphabetically', self::DOMAIN) ?></option>
			  </select>
			</div>
			<div>
			  <label><?php _e('Search', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_SEARCH ?>" size="60" value="" />
			</div>
			<div>
			  <label><?php _e('Domain', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_DOMAIN ?>" size="60" value="" />
			</div>
			<div>
			  <label><?php _e('Since (YYYY-MM-DD HH24:MM)', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_SINCE ?>" size="60" value="" />
			</div>
			<div>
			  <label><?php _e('Count', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_COUNT ?>" size="60" value="" />
			</div>
			<div>
			  <label><?php _e('Offset', self::DOMAIN) ?></label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_OFFSET ?>" size="60" value="" />
			</div>
			<input type="hidden" name="action" value="generate" />
			<div>
			  <input type="submit" class="button button-primary" value="<?php _e('Generate', self::DOMAIN) ?>" />
			</div>
		  </form>
		</div>
	  </div>
	  <br />
	  <?php
	  
		if($_POST["action"]==='generate'){
		  
		  	$consumer_key = get_option(self::DB_CONSUMER_KEY);
			$access_token = get_option(self::DB_ACCESS_TOKEN);
		  	$format = get_option(self::DB_FORMAT);
		  
		  	$state = $_POST[PocketUtil::OPT_STATE];
		  	$favorite = $_POST[PocketUtil::OPT_FAVORITE];
			$tag = $_POST[PocketUtil::OPT_TAG];
			$contentType = $_POST[PocketUtil::OPT_CONTENT_TYPE];
			$sort = $_POST[PocketUtil::OPT_SORT];
  			$search = $_POST[PocketUtil::OPT_SEARCH];
		  	$domain = $_POST[PocketUtil::OPT_DOMAIN];
			$since = $_POST[PocketUtil::OPT_SINCE];
		  	$count = $_POST[PocketUtil::OPT_COUNT];
		  	$offset = $_POST[PocketUtil::OPT_OFFSET];
		  	
	  		if(isset($state) && $state){
				$option_params[PocketUtil::OPT_STATE] = $state;
			}

			if($favorite==0 || $favorite==1){
				$option_params[PocketUtil::OPT_FAVORITE] = $favorite;
			}
    
			if(isset($tag) && $tag){
				$option_params[PocketUtil::OPT_TAG] = $tag;
			}

			if(isset($contentType) && $contentType){
				$option_params[PocketUtil::OPT_CONTENT_TYPE] = $contentType;
			}	

			if(isset($sort) && $sort){
				$option_params[PocketUtil::OPT_SORT] = $sort;
			}

	  		if(isset($search) && $search){
				$option_params[PocketUtil::OPT_SEARCH] = $search;
			}
		  
	  		if(isset($domain) && $domain){
				$option_params[PocketUtil::OPT_DOMAIN] = $domain;
			}
		  
			if(isset($since) && $since){
			  
			  $gmt_time = get_gmt_from_date($since);
			  
			  $option_params[PocketUtil::OPT_SINCE] = strtotime($gmt_time);
			}

			if(isset($count) && $count){
				$option_params[PocketUtil::OPT_COUNT] = $count;
			}
		  
			if(isset($offset) && $offset){
				$option_params[PocketUtil::OPT_OFFSET] = $offset;
			}
		  
			$option_params[PocketUtil::OPT_DETAIL_TYPE] = 'complete';
		  
		  	//$debug_mode = true;
		  
			if(isset($debug_mode) && $debug_mode){
		  
		  		echo 'consumer_key: ' . $consumer_key . '<br />';
		  		echo 'access_token: ' . $access_token . '<br />';
			  	echo 'state: ' . $state . '<br />';
		  		echo 'favorite: ' . $favorite . '<br />';
		  		echo 'tag: ' . $tag . '<br />';
		  		echo 'contentType: ' . $contentType . '<br />';
		  		echo 'sort: ' . $sort . '<br />';
		  		echo 'search: ' . $search . '<br />';
			  	echo 'domain: ' . $domain . '<br />';
			  	echo 'since: JST-> ' . $since . ' GMT-> ' . $gmt_time . '<br />';
		  		echo 'count: ' . $count . '<br />';
		  		echo 'offset: ' . $offset . '<br />';
			  	
			  	echo 'Default timezone: ' . date_default_timezone_get() . '<br />';	
			  	echo 'Local timezone: ' . get_option('timezone_string') . '<br />';
			  	echo 'Difference in time from default timezone: ' . get_option('gmt_offset') . '<br />';
			  
			  	echo 'Array of option parameters:<br />';  
		  		print_r($option_params);
		  	}
		  
		  	$pocket_util = new PocketUtil();
		  	$pocket_body = $pocket_util->retrieveItem($consumer_key,$access_token,$option_params);
		  
		  	$feedly_util = new FeedlyUtil();
		  
		 	if(!empty($pocket_body)){
			  
			  	//Check if there is mached data.
			  	if(!empty($pocket_body->list)){ 
			  
					echo '<div class="wrap">';
					echo '<h2 class="pkt-nws-gnrtr">' . __('Generated HTML Code', self::DOMAIN) . '</h2>';
			  		echo '<p>' . __('Generated HTML code is as follows. Copy and paste it into your post.', self::DOMAIN) . '</p>';
		  			echo '<div class="pkt-nws-gnrtr">';
		  			echo '<label>' . __('HTML Code', self::DOMAIN) . '</label><br />';
					echo '<textarea class="text" style="width:500px;height:300px;">';
			  
    				foreach($pocket_body->list as $pocket_item){
				  	  
				  		//Post URL
				  		$html_code = str_replace(self::FRMT_POST_URL,$pocket_item->{PocketUtil::REF_RESOLVED_URL}, stripslashes($format));
				  	
				  		//Post title
				  		$html_code = str_replace(self::FRMT_POST_TITLE,$pocket_item->{PocketUtil::REF_RESOLVED_TITLE},$html_code);

				  		//Site name and site URL
				  		if(strpos($format, self::FRMT_SITE_NAME) !== false || strpos($format, self::FRMT_SITE_URL) !== false){
						  
					  		$url_util = new URLUtil($pocket_item->{PocketUtil::REF_RESOLVED_URL});
					  	
					  		$feedly_info_flag = false;

					  		do{
					  			$feedly_body = $feedly_util->getSiteInfo($url_util->getURL());
						  	
						  		$site_name = $feedly_body->results[0]->{FeedlyUtil::REF_SITE_NAME};
								$site_url = $feedly_body->results[0]->{FeedlyUtil::REF_SITE_URL};
						  
								if(isset($site_name) && isset($site_url)){
								  
									$html_code = str_replace(self::FRMT_SITE_NAME,$site_name,$html_code);
									$html_code = str_replace(self::FRMT_SITE_URL,$site_url,$html_code);
							  		
								  	$feedly_info_flag = true;
							  		break;
								  
								}
						 	
						  	//Check next path existence
					  		}while($url_util->next());
					  
					  		if(!$feedly_info_flag){
						  				  	
				  				//Site URL (URL of top page) retrieval
				  				$site_url = $pocket_util->getBaseUrl($pocket_item->{PocketUtil::REF_RESOLVED_URL});
				  				$html_code = str_replace(self::FRMT_SITE_URL,$site_url,$html_code);
						  
						   		//Information retrieval of OGP
					  			$graph = OpenGraph::fetch($pocket_item->{PocketUtil::REF_RESOLVED_URL});
						  
						  		if(isset($graph->site_name)){
								  
							  		// Content of og:site_name
							  		$html_code = str_replace(self::FRMT_SITE_NAME,$graph->site_name,$html_code);
								  
								} else {
								  
						  			// Content of title in top page
						  			$site_name = $pocket_util->getSiteName($pocket_item->{PocketUtil::REF_RESOLVED_URL});
						  			$html_code = str_replace(self::FRMT_SITE_NAME,$site_name,$html_code);
								}
				
					  		}
					  	
					  
						}
				  
				  		//Post excerpt
				  		if(strpos($format, self::FRMT_POST_EXCERPT) !== false){
				  		
				  			if(isset($pocket_item->{PocketUtil::REF_EXCERPT})){
							  
					  			// Content of excerpt in Pocket data
								$html_code = str_replace(self::FRMT_POST_EXCERPT,$pocket_item->{PocketUtil::REF_EXCERPT},$html_code);
							  
							} else {
							  
					  			//Information retrieval of OGP
					  			$graph = OpenGraph::fetch($pocket_item->{PocketUtil::REF_RESOLVED_URL});

						 		// Content of og:description
				  				$html_code = str_replace(self::FRMT_POST_EXCERPT,$graph->description,$html_code);
							  
							}
						}
				  
				  		//Post image
				  		if(strpos($format, self::FRMT_POST_IMAGE) !== false){
						  
							if(isset($graph->image)){
							  
					  			//Information retrieval of OGP
					  			$graph = OpenGraph::fetch($pocket_item->{PocketUtil::REF_RESOLVED_URL});

					  			$html_code = str_replace(self::FRMT_POST_IMAGE,$graph->image,$html_code);
							  
							} 
						}
				  
			 			echo htmlspecialchars($html_code); 			
    				}
			
    				echo '</textarea>';
					echo '</div>';
			  		echo '</div>';

			  	} else {
				  
			  		echo '<div class="pkt-nws-gnrtr">';
				  	echo '<span class="red">' . __('INFO: Pocket data matching your specified condition was not found.', self::DOMAIN) . '</span>';
			  		echo '</div>';
				  
			  	}
				
		  	} else {
			  
			  echo '<div class="pkt-nws-gnrtr">';
			  echo '<span class="red">' . __('ERROR: Pocket data retrieval failed.', self::DOMAIN) . '</span>';
			  echo '</div>';
			  
		  	}

		}
	}
  
  	public static function init() {

		static $instance = null;

		if ( !$instance )
			$instance = new PocketNewsGenerator;

		return $instance;

	}

}

PocketNewsGenerator::init();
}

?>
