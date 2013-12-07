<?php
/*
Plugin Name: Pocket News Generator
Plugin URI: 
Description: This plugin retrieves your Pocket data based on specified condition and generates its HTML code according to specified format automatically. This makes it possible to create an entry which introduces bookmarked articles efficiently.  
Version: 0.1
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if (!class_exists('PocketNewsGenerator')){
  
class PocketNewsGenerator{
 
	/**
	 * Plugin version
	 * @var string
	 */
	static $version = '0.1';
  
 	const DB_CONSUMER_KEY = '_png_consumer_key';
	const DB_ACCESS_TOKEN = '_png_access_token';
	const DB_REQUEST_CODE = '_png_request_code';
	const DB_FORMAT = '_png_format';

  	const DB_TMP_CONSUMER_KEY = '_png_tmp_consumer_key';
	const DB_TMP_ACCESS_TOKE = '_png_tmp_access_token';
	const DB_TMP_REQUEST_CODE = '_png_tmp_request_code';
    
    /**
     * Class constarctor
     * Hook onto all of the actions and filters needed by the plugin.
     */
    function __construct() {
	    add_action('admin_menu', array(&$this, 'action_admin_menu'));
	  
	  	if (!class_exists('PocketUtil')) include_once (dirname(__FILE__) . '/pocket-util.php');
	  	$pocket_util = new PocketUtil();
	  
	  	if(is_admin()){
	  		wp_enqueue_style( 'pocket-news-generator',$pocket_util->getUrl( 'pocket-news-generator.css')  ); 
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
		  <div id="icon-options-general" class="icon32"><br></div>
		  <h2>Current Parameter</h2>
		  <p>The following describes registered parameters.</p>
		  <table class="widefat">
			<thead>
			  <tr>
				<th>Name</th>
				<th>Value</th>
			  </tr>
			</thead>
			<tbody>
			  <tr>
				<td>Consumer Key</td><td><?php echo $consumer_key ?></td>
			  </tr>
			  <tr>
				<td>Access Token</td><td><?php echo $access_token ?></td>
			  </tr>
			  <tr>
				<td>HTML Format</td><td><pre><?php echo stripslashes(htmlspecialchars($format)) ?></pre></td>
			  </tr>
			</tbody>
		  </table>
		</div>
		<br />
		<div class="wrap">
		  <div id="icon-options-general" class="icon32"><br></div>
		  <h2>Register New Parameter</h2>
		  <p>You can register or modify required parameters at the following form.</p>
		  <div class="pkt-nws-gnrtr">
			<form action="" method="post">
			  <div>
				<label>Consumer Key</label><br />
				<input type="text" class="text" name="consumer_key" size="60" value="" />
			  </div>
			  <div>
				<label>Access Token</label><br />
				<input type="text" class="text" name="access_token" size="60" value="" />
			  </div>
			  <div>
				<label>HTML Format</label><br />
				<textarea name="format" class="text" cols="60" rows="5"></textarea>
			  </div>
			  <input type="hidden" class="text" name="action" value="register" />
			  <div>
				<input type="submit" class="submit" value="Register" />
			  </div>
			</form>
			<br />
			<p>The parameter "HTML Format" indicates format of generated HTML, and the following reserved keywords are utilized to refer to retrieved Pocket data.</p>
			<table class="widefat">
				<thead>
			  		<tr>
						<th>Reserved Keyword</th>
						<th>Description</th>
			  		</tr>
				</thead>
				<tbody>
			  		<tr>
						<td>${TITLE}</td><td>Title of bookmarked item</td>
			  		</tr>
			  		<tr>
						<td>${URL}</td><td>URL of bookmarked item</td>
			  		</tr>
			  		<tr>
						<td>${EXCERPT}</td><td>Excerpt from bookmarked item</td>
			  		</tr>
				</tbody>
		  	</table>
			<br />
			<label>Format Sample</label><br />
			<textarea class="text" style="width:500px;height:260px;">
&lt;div class=&quot;pocket-post&quot;&gt;
&lt;div class=&quot;pocket-thumbnail&quot; style=&quot;float:left;&quot;&gt;
&lt;a href=&quot;${URL}&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;http://capture.heartrails.com/150x130/shadow?${URL}&quot; alt=&quot;${TITLE}&quot;&gt;&lt;/a&gt;
&lt;/div&gt;
&lt;div class=&quot;pocket-title&quot;&gt;
&lt;a href=&quot;${URL}&quot; target=&quot;_blank&quot;&gt;${TITLE}&lt;/a&gt;&lt;img border=&quot;0&quot; src=&quot;http://b.hatena.ne.jp/entry/image/${URL} alt=&quot;&quot; /&gt;
&lt;/div&gt;
&lt;div style=&quot;clear:both&quot;&gt;&lt;/div&gt;
&lt;div class=&quot;pocket-excerpt&quot;&gt;
&lt;blockquote&gt;${EXCERPT}&lt;/blockquote&gt;
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
			  echo '<p>request code: ' . $request_code . '</p>';
			  
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
			  //echo 'access token: ' . $tmp_access_token;
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
		  <div id="icon-options-general" class="icon32"><br></div>
		  <h2>Get Access Token</h2>
		  <p>You need to obtain your cusumer key and access token according to the guide below.</p>
		  <div class="pkt-nws-gnrtr">
			<h3>Step 1. Obtain a platform consumer key</h3>
			<p>You need to publish your cusumer key from the following site.</p>
			<a href="http://getpocket.com/developer/apps/" target="_blank">Pocket My Applications</a>
			<h3>Step 2. Obtain a request token</h3>
			<p>Input the obtained cosumer key and push the button below to get a request token.</p>
			<form action="" method="post">
			  <div>
				<label>Consumer Key</label><br />
				<input type="text" class="text" name="consumer_key" size="60" value="" />
			  </div>
			  <input type="hidden" class="text" name="action" value="request_code" />
			  <div>
				<input type="submit" class="submit" value="Get Request Code" />
			  </div>
			</form>
			<?php 
	  			if(isset($request_code_flag)){
					if($request_code_flag){
		  				echo '<span class="green">INFO: Request token retrieval succeeded.</span>';
					} else {
				  		echo '<span class="red">ERROR: Request token retrieval failed.</span>';
					}
				}
			?>
			<h3>Step 3. Authenticate your request token in Pocket</h3>
			<p>After the above Step 2, push the following button whitch authorizes your application's request token.</p>
			<form action="https://getpocket.com/auth/authorize" method="get">
			  <input type="hidden" class="text" name="request_token" value="<?php echo $tmp_request_code ?>" />
			  <input type="hidden" class="text" name="redirect_uri" value="<?php echo $redirect_uri_auth ?>" />
			  <div>
				<input type="submit" class="submit" value="Authorize Request Token" />
			  </div>
			</form>	
			<?php 
			  	if(isset($authentication_flag)){
					if($authentication_flag){
		  				echo '<span class="green">INFO: Authentication of request token succeeded.</span>';
					} else {
				  		echo '<span class="red">ERROR: Authentication of request token failed.</span>';
					}
				}
			?>
			<h3>Step 4. Convert a request token into a Pocket access token</h3>
			<p>After the above Step 3, push the following button in order to convert the authenticated request token into a Pocket access token.</p>
			<form action="<?php echo $redirect_uri_base ?>" method="post">
			  <input type="hidden" class="text" name="action" value="access_token" />
			  <div>
				<input type="submit" class="submit" value="Get Access Token" />
			  </div>
			</form>
			<?php 
	  			if(isset($access_token_flag)){
					if($access_token_flag){
		  				echo '<span class="green">INFO: Conversion from request token to access token succeeded.</span>';
					} else {
				  		echo '<span class="red">ERROR: Conversion from request token to access token failed.</span>';
					}
				}
			?>			
			<h3>Step 5. Register the consumer key and access token below at the section titled "Register New Parameter".</h3>
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
		<div id="icon-tools" class="icon32"><br></div>
		<h2>Retrieve Items in Pocket and Generate HTML Code</h2>
		<p>Specify search condition for Pocket data retrieval and push the button below.</p>
		<div class="pkt-nws-gnrtr">
		  <form action="" method="post">
			<div>
			  <label>State</label><br />
			  <select name="<?php echo PocketUtil::OPT_STATE ?>" class="dropdown">
				<option value="all" selected>all (both unread and archived items)</option>
				<option value="unread">only unread items</option>
				<option value="archive">only archived items</option>
			  </select>
			</div>
			<div>
			  <label>Favorite</label><br />
			  <select name="<?php echo PocketUtil::OPT_FAVORITE ?>" class="dropdown">
				<option value ="" selected>all (both un-favorited and favorited items)</option>
				<option value="0">only un-favorited items</option>
				<option value="1">only favorited items</option>
			  </select>
			</div>
			<div>
			  <label>Tag</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_TAG ?>" size="60" value="" />
			</div>
			<div>
			  <label>Content Type</label><br />
			  <select name="<?php echo PocketUtil::OPT_CONTENT_TYPE ?>" class="dropdown">
				<option value ="" selected>all</option>
				<option value="article">only articles</option>
				<option value="video">only videos or articles with embedded videos</option>
				<option value="image">only images</option>
			  </select>
			</div>
			<div>
			  <label>Sort</label><br />
			  <select name="<?php echo PocketUtil::OPT_SORT ?>" class="dropdown">
				<option value ="newest" selected>items in order of newest to oldest</option>
				<option value="oldest">items in order of oldest to newest</option>
				<option value="title">items in order of title alphabetically</option>
				<option value="site">items in order of URL alphabetically</option>
			  </select>
			</div>
			<div>
			  <label>Search</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_SEARCH ?>" size="60" value="" />
			</div>
			<div>
			  <label>Domain</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_DOMAIN ?>" size="60" value="" />
			</div>
			<div>
			  <label>Since (YYYY/MM/DD HH24:MM)</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_SINCE ?>" size="60" value="" />
			</div>
			<div>
			  <label>Count</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_COUNT ?>" size="60" value="" />
			</div>
			<div>
			  <label>Offset</label><br />
			  <input type="text" class="text" name="<?php echo PocketUtil::OPT_OFFSET ?>" size="60" value="" />
			</div>
			<input type="hidden" name="action" value="generate" />
			<div>
			  <input type="submit" class="submit" value="Generate" />
			</div>
		  </form>
		</div>
	  </div>
	  <br />
	  <?php
	  
		if($_POST["action"]==='generate'){

	  		$pocket_url='${URL}';
  			$pocket_title='${TITLE}';
			$pocket_excerpt='${EXCERPT}';
		  	$pocket_tags='${TAGS}';
		  
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
			$since = strtotime($_POST[PocketUtil::OPT_SINCE]);
		  	$count = $_POST[PocketUtil::OPT_COUNT];
		  	$offset = $_POST[PocketUtil::OPT_OFFSET];
		  	
	  		if(isset($state) && $state){
				$option_params[PocketUtil::OPT_STATE] = $state;
			}

			if(isset($favorite) && $favorite){
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
				$option_params[PocketUtil::OPT_SINCE] = $since;
			}

			if(isset($count) && $count){
				$option_params[PocketUtil::OPT_COUNT] = $count;
			}
		  
			if(isset($offset) && $offset){
				$option_params[PocketUtil::OPT_OFFSET] = $offset;
			}
		  
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
		  		echo 'since: ' . $since . '<br />';
		  		echo 'count: ' . $count . '<br />';
		  		echo 'offset: ' . $offset . '<br />';
		  			  
		  		print_r($option_params);
		  	}
		  
		  	$pocket_util = new PocketUtil();
		  	$response_body = $pocket_util->retrieveItem($consumer_key,$access_token,$option_params);
		  
		 	if(!empty($response_body)){
			  
				echo '<div class="wrap">';
			  	echo '<div id="icon-tools" class="icon32"><br /></div>';
				echo '<h2>Generated HTML Code</h2>';
			  	echo '<p>Generated HTML code is as follows. Copy and paste it into your post.</p>';
		  		echo '<div class="pkt-nws-gnrtr">';
		  		echo '<label>HTML Code</label><br />';
				echo '<textarea class="text" style="width:500px;height:300px;">';

    			foreach($response_body->list as $content){
				  
				  	$html_code = str_replace($pocket_url, $content->{PocketUtil::REF_RESOLVED_URL}, stripslashes($format));
			 		$html_code = str_replace($pocket_title, $content->{PocketUtil::REF_RESOLVED_TITLE},$html_code);
			 		$html_code = str_replace($pocket_excerpt, $content->{PocketUtil::REF_EXCERPT},$html_code);

			 		echo htmlspecialchars($html_code); 			
    			}
			
    			echo '</textarea>';
				echo '</div>';
			  	echo '</div>';

		  	} else {
			  echo '<div class="pkt-nws-gnrtr">';
			  echo '<span class="red">ERROR: Pocket data retrieval failed.</span>';
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
