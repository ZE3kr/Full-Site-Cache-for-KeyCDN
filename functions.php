<?php
function fsckeycdn_id(){
	global $fsckeycdn_id,$fsckeycdn_blog_id;
	if(isset($fsckeycdn_id)){
		if(is_array($fsckeycdn_id) && isset($fsckeycdn_id[$fsckeycdn_blog_id])){
			return $fsckeycdn_id[$fsckeycdn_blog_id];
		} elseif(!is_multisite()) {
			return $fsckeycdn_id;
		}
	} else {
		return get_option( 'fsckeycdn_id', false );
	}
	return false;
}

function fsckeycdn_purge(){
	global $fsckeycdn_purge,$fsckeycdn_blog_id;
	if(isset($fsckeycdn_purge)){
		if(is_array($fsckeycdn_purge) && isset($fsckeycdn_purge[$fsckeycdn_blog_id])){
			return $fsckeycdn_purge[$fsckeycdn_blog_id];
		} else {
			return $fsckeycdn_purge;
		}
	} else {
		return 1;
	}
}

function fsckeycdn_status(){
	global $fsckeycdn_id,$fsckeycdn_apikey,$fsckeycdn_blog_id;
	if( is_array($fsckeycdn_id) && isset($fsckeycdn_id[$fsckeycdn_blog_id]) && $fsckeycdn_id[$fsckeycdn_blog_id] === false ) {
		return false;
	}
	if( ((is_array($fsckeycdn_id) && isset($fsckeycdn_id[$fsckeycdn_blog_id])) || ($fsckeycdn_blog_id == 1 && isset($fsckeycdn_id)) || get_option( 'fsckeycdn_id', false )) && isset($fsckeycdn_apikey) ){
		if(isset($fsckeycdn_id[$fsckeycdn_blog_id]) && $fsckeycdn_id[$fsckeycdn_blog_id] === false){
			return false;
		}
		return true;
	} else {
		return false;
	}
}

function fsckeycdn_wp_config(){
	global $fsckeycdn_id;
	if(isset($fsckeycdn_id)){
		return true;
	} else {
		return false;
	}
}

function fsckeycdn_check($zone,$name,$key){
	foreach($zone as $each){
		if($each[$key] == $name){
			return $each;
		}
	}
	return false;
}

function fsckeycdn_check_ce(){
	global $fsckeycdn_ce;
	$fsckeycdn_ce = wp_parse_args(
		get_option('cache'),
		[
			'new_post'		=> 0,
			'new_comment' 	=> 0,
		]
	);
	if(has_action('ce_clear_cache')){
		remove_action( 'admin_bar_menu', [ 'Cache_Enabler','add_admin_links' ], 20, 1 );
		remove_action( 'init', [ 'Cache_Enabler' ,'register_publish_hooks' ] );
	}
}

function fsckeycdn_register_publish_hooks(){
	// get post types
	$post_types = get_post_types(
		['public' => true]
	);

	// check if empty
	if ( empty($post_types) ) {
		return;
	}

	// post type actions
	foreach ( $post_types as $post_type ) {
		add_action( 'publish_' .$post_type, 'fsckeycdn_delete_purge', 91, 2 );
		add_action( 'publish_future_' .$post_type, 'fsckeycdn_purge_blog', 91 );
	}
}

function fsckeycdn_meta($meta, $file) {
	$fsckeycdn_status = fsckeycdn_status();
	if ($file == FSKEYCDN_DIR_NAME) {
		$meta[] = '<a href="mailto:support@tlo.xyz" target="_blank">Feedback &amp; Support</a>';
		if( $fsckeycdn_status ){
			$meta[] = '<span style="color:green">Setup Success</span>';
		} else {
			$meta[] = '<span style="color:red">KeyCDN Disabled</span>';
		}
	}
	return $meta;
}

function fsckeycdn_header(){
	global $fsckeycdn_x_pull_key, $fsckeycdn_scheme, $fsckeycdn_realhost, $fsckeycdn_blog_id,$fsckeycdn_admin;
	/* Set redirect if user not logged in */
	if(substr($_SERVER['REQUEST_URI'],0,11) != '/robots.txt' && $_SERVER['SCRIPT_NAME'] == '/index.php' && $_SERVER['HTTP_X_PULL'] != $fsckeycdn_x_pull_key && !is_user_logged_in()) {
		if($fsckeycdn_admin == $fsckeycdn_realhost) {
			header('HTTP/1.1 302 Moved Temporarily');
			header('Location: '.wp_login_url($fsckeycdn_scheme.'://'.$fsckeycdn_realhost.$_SERVER['REQUEST_URI']));
			exit();
		}
	}
	$post_ID = $GLOBALS['post']->ID;
	if(is_home()||is_front_page()||is_search()||is_feed()){
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' index-'.$fsckeycdn_blog_id.' blog-'.$fsckeycdn_blog_id);
	} elseif(is_search()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' index-'.$fsckeycdn_blog_id.' blog-'.$fsckeycdn_blog_id);
	} elseif(is_date()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' date-'.$fsckeycdn_blog_id.'-'.get_query_var('year','0').'-'.get_query_var('monthnum','0').'-'.get_query_var('day','0').' blog-'.$fsckeycdn_blog_id);
	} elseif(is_category()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' cat-'.$fsckeycdn_blog_id.'-'.get_query_var('cat').' blog-'.$fsckeycdn_blog_id);
	} elseif(is_tag()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' tag-'.$fsckeycdn_blog_id.'-'.get_query_var('tag_id').' blog-'.$fsckeycdn_blog_id);
	} elseif(is_author()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' author-'.$fsckeycdn_blog_id.'-'.get_query_var('author').' blog-'.$fsckeycdn_blog_id);
	} elseif(is_archive()) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' blog-'.$fsckeycdn_blog_id);
	} elseif(strstr($_SERVER['REQUEST_URI'],'/sitemap') && strstr($_SERVER['REQUEST_URI'],'.xml')) {
		header('Cache-Tag: wordpress archive-'.$fsckeycdn_blog_id.' index-'.$fsckeycdn_blog_id.' blog-'.$fsckeycdn_blog_id);
	} elseif($post_ID){
		header('Cache-Tag: wordpress page-'.$fsckeycdn_blog_id.' id-'.$fsckeycdn_blog_id.'-'.$post_ID.' blog-'.$fsckeycdn_blog_id);
	} else {
		header('Cache-Tag: wordpress blog-'.$fsckeycdn_blog_id);
	}
}

function fsckeycdn_delete_purge( $post_ID, $post ) {
	global $fsckeycdn_apikey, $fsckeycdn_blog_id, $fsckeycdn_ce;
	$fsckeycdn_purge = fsckeycdn_purge();
	$fsckeycdn_id = fsckeycdn_id();

	// check if post id or post is empty
	if ( empty($post_ID) OR empty($post) ) {
		return;
	}

	// check post status
	if ( ! in_array( $post->post_status, ['publish', 'future'] ) ) {
		return;
	}

	if($fsckeycdn_ce){
		if( isset($_POST['_clear_post_cache_on_update']) && !(int)$_POST['_clear_post_cache_on_update'] ){
			$fsckeycdn_purge = 2;
		} elseif($fsckeycdn_ce['new_post']) {
			$fsckeycdn_purge = 2;
		}
	}

	$url = 'https://'.$fsckeycdn_apikey.':@api.keycdn.com/zones/';
	$purge = false;
	if($fsckeycdn_purge == 1){
		$purge = ['archive-'.$fsckeycdn_blog_id, 'id-'.$fsckeycdn_blog_id.'-'.$post_ID];
	} elseif($fsckeycdn_purge == 2) {
		$purge = ['blog-'.$fsckeycdn_blog_id];
	} elseif($fsckeycdn_purge == 4) {
		$purge = ['id-'.$fsckeycdn_blog_id.'-'.$post_ID];
	} elseif($fsckeycdn_purge != 5) {
		$tag = wp_get_object_terms($post_ID, 'post_tag', ["fields" => 'ids']);
		$tag = array_map(create_function('$item', 'return "tag-$fsckeycdn_blog_id-$item";'), $tag);
		$cat = wp_get_object_terms($post_ID, 'category', ["fields" => 'ids']);
		$cat = array_map(create_function('$item', 'return "cat-$fsckeycdn_blog_id-$item";'), $cat);
		$day = get_the_date( 'Y-n-j', $post_ID );
		$month = get_the_date( 'Y-n-0', $post_ID );
		$year = get_the_date( 'Y-0-0', $post_ID );
		$author = $post->post_author;

		$purge = array_merge([ 'index-'.$fsckeycdn_blog_id, 'author-'.$fsckeycdn_blog_id.'-'.$author, 'date-'.$fsckeycdn_blog_id.'-'.$day, 'date-'.$fsckeycdn_blog_id.'-'.$month, 'date-'.$fsckeycdn_blog_id.'-'.$year, 'id-'.$fsckeycdn_blog_id.'-'.$post_ID], $cat, $tag);
	}

	if($purge){
		if(has_action('ce_clear_cache')){
			do_action('ce_clear_cache');
		}
		wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones/purgetag/'.$fsckeycdn_id.'.json',[
			'method' => 'DELETE',
			'body' => ['tags' => $purge],
			'timeout' => 20,
		]);
	}
}

function fsckeycdn_purge_id( $post_ID ) {
	global $fsckeycdn_apikey, $fsckeycdn_blog_id, $fsckeycdn_ce;
	$fsckeycdn_purge = fsckeycdn_purge();
	$fsckeycdn_id = fsckeycdn_id();

	// check if post id or post is empty
	if ( empty($post_ID) ) {
		return;
	}

	$url = 'https://'.$fsckeycdn_apikey.':@api.keycdn.com/zones/';
	$purge = ['id-'.$fsckeycdn_blog_id.'-'.$post_ID];

	wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones/purgetag/'.$fsckeycdn_id.'.json',[
		'method' => 'DELETE',
		'body' => ['tags' => $purge],
		'timeout' => 20,
	]);
}

function fsckeycdn_purge_blog_cron() {
	wp_schedule_single_event(time(), 'fsckeycdn_purge_blog_hook');
}

function fsckeycdn_purge_blog() {
	// Purge a specific blog.
	global $fsckeycdn_apikey,$fsckeycdn_blog_id;
	$zone = fsckeycdn_id();
	return wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones/purgetag/'.$zone.'.json',[
		'method' => 'DELETE',
		'body' => ['tags' => ['blog-'.$fsckeycdn_blog_id,],],
		'timeout' => 20,
	]);
}

function fsckeycdn_purge_all_blog_cron() {
	wp_schedule_single_event(time(), 'fsckeycdn_purge_all_blog_hook');
}

function fsckeycdn_purge_all_blog() {
	// Purge the whole blogs (for multisite Sub-directories install) but static file (CSS, JS, and media).
	global $fsckeycdn_apikey;
	$zone = fsckeycdn_id();
	return wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones/purgetag/'.$zone.'.json',[
		'method' => 'DELETE',
		'body' => ['tags' => ['wordpress',],],
		'timeout' => 20,
	]);
}

function fsckeycdn_purge_all_cron() {
	wp_schedule_single_event(time(), 'fsckeycdn_purge_all_hook');
}

function fsckeycdn_purge_all() {
	// Purge the whole blog include static file.
	global $fsckeycdn_apikey;
	$zone = fsckeycdn_id();
	return wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones/purge/'.$zone.'.json',['method' => 'GET','timeout' => 20,]);
}

function fsckeycdn_change_comment_cron($after_status, $before_status, $comment) {
	wp_schedule_single_event(time(), 'fsckeycdn_change_comment_hook');
}

function fsckeycdn_change_comment($after_status, $before_status, $comment) {
	global $fsckeycdn_ce;
	// check if changes occured
	if ( $after_status != $before_status ) {
		if ( $fsckeycdn_ce && $fsckeycdn_ce['new_comment'] ) {
			fsckeycdn_purge_blog();
		} else {
			fsckeycdn_purge_id( $comment->comment_post_ID );
		}
	}
}

function fsckeycdn_edit_comment_cron($after_status, $before_status, $comment) {
	wp_schedule_single_event(time(), 'fsckeycdn_edit_comment_hook');
}

function fsckeycdn_edit_comment($id) {
	global $fsckeycdn_ce;
	// clear complete cache if option enabled
	if ( $fsckeycdn_ce && $fsckeycdn_ce['new_comment'] ) {
		fsckeycdn_purge_blog();
	} else {
		fsckeycdn_purge_id(
			get_comment($id)->comment_post_ID
		);
	}
}

function fsckeycdn_new_comment_cron($after_status, $before_status, $comment) {
	wp_schedule_single_event(time(), 'fsckeycdn_new_comment_hook');
}

function fsckeycdn_new_comment($approved, $comment) {
	global $fsckeycdn_ce;
	// check if comment is approved
	if ( $approved === 1 ) {
		if ( $fsckeycdn_ce && $fsckeycdn_ce['new_comment'] ) {
			fsckeycdn_purge_blog();
		} else {
			fsckeycdn_purge_id( $comment['comment_post_ID'] );
		}
	}

	return $approved;
}

function fsckeycdn_minify_html_admin(){
	// Set URL rewrite for admin page.
	ob_start('fsckeycdn_compress_admin');
}

function fsckeycdn_compress_admin($html){
	global $fsckeycdn_realhost,$fsckeycdn_admin;
	$html = str_replace('://'.$_SERVER['HTTP_HOST'],'://'.$fsckeycdn_admin,$html);
	$html = str_replace('%3A%2F%2F'.$_SERVER['HTTP_HOST'],'%3A%2F%2F'.$fsckeycdn_admin,$html);
	$html = str_replace(':\/\/'.$_SERVER['HTTP_HOST'],':\/\/'.$fsckeycdn_admin,$html);
	return $html;
}

function fsckeycdn_minify_html(){
	// Set URL rewrite for KeyCDN page.
	ob_start('fsckeycdn_compress');
}

function fsckeycdn_unparse_url($parsed_url) { 
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
	$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
	return $scheme.$host.$path.$query; 
}

function fsckeycdn_rewrite_url($asset) {
	global $fsckeycdn_cdn_domain;
	$url = parse_url($asset[0]);
	if($url['host'] == $_SERVER['HTTP_HOST'] && substr($url['path'],0,12)=='/wp-content/' || substr($url['path'],0,16)=='/wp-includes/js/' ) {
		$url['host'] = $fsckeycdn_cdn_domain;
		return fsckeycdn_unparse_url($url);
	}
	return $asset[0];
}

function fsckeycdn_compress($html){
	global $fsckeycdn_cdn_domain,$fsckeycdn_scheme,$fsckeycdn_admin;
	$html = str_replace('://'.$fsckeycdn_admin,'://'.$_SERVER['HTTP_HOST'],$html);
	$html = str_replace('%3A%2F%2F'.$fsckeycdn_admin,'%3A%2F%2F'.$_SERVER['HTTP_HOST'],$html);
	$html = str_replace(':\/\/'.$fsckeycdn_admin,':\/\/'.$_SERVER['HTTP_HOST'],$html);

	if(isset($fsckeycdn_cdn_domain)){
		// regex rule to match full URL
		$regex_rule = '@(?i)\\b((?:https?://)(?:[^\\s()<>]+|\\(([^\\s()<>]+|(\\([^\\s()<>]+\\)))*\\))+(?:\\(([^\\s()<>]+|(\\([^\\s()<>]+\\)))*\\)|[^\\s`!()\\[\\]{};:\'".,<>?«»“”‘’]))@';

		// call the cdn rewriter callback
		$html = preg_replace_callback($regex_rule, 'fsckeycdn_rewrite_url', $html);
	}

	return $html;
}

function fsckeycdn_add_settings( $links, $file ) {
	if ( $file == FSKEYCDN_DIR_NAME ) {
		$posk_links = '<a href="'.get_admin_url().'options-general.php?page=full-site-cache-kc">'.__('Settings').'</a>';
		// Make sure the 'Settings' link at first
		array_unshift( $links, $posk_links );
	}
	return $links;
}

function fsckeycdn_add_admin_links($wp_admin_bar){
	if ( !fsckeycdn_status() || is_network_admin() ){
		return;
	}

	// check user role
	if ( ! is_admin_bar_showing() OR ! current_user_can('manage_options') ) {
		return;
	}

	// add admin purge link
	if(is_admin()){
		$wp_admin_bar->add_menu([
			'id' => 'clear-cache',
			'href' => wp_nonce_url( add_query_arg('_cache', 'clear'), '_cache__clear_nonce'),
			'parent' => 'top-secondary',
			'title' => '<span class="ab-item">Clear Cache</span>',
			'meta' => [ 'title' => esc_html__('clear Cache', 'cache') ],
		]);
	} else {
		$wp_admin_bar->add_menu([
			'id' => 'clear-cache',
			'href' => wp_nonce_url( add_query_arg('_cache', 'clear', admin_url('options-general.php?page=full-site-cache-kc')), '_cache__clear_nonce'),
			'parent' => 'top-secondary',
			'title' => '<span class="ab-item">Clear Cache</span>',
			'meta' => [ 'title' => esc_html__('clear Cache', 'cache') ],
		]);
	}
}

function fsckeycdn_purge_button($data){
	// check if clear request
	if ( empty($_GET['_cache']) OR $_GET['_cache'] !== 'clear' ) {
		return;
	}

	// validate nonce
	if ( empty($_GET['_wpnonce']) OR ! wp_verify_nonce($_GET['_wpnonce'], '_cache__clear_nonce') ) {
		return;
	}

	if ( !fsckeycdn_status() || is_network_admin() ){
		return;
	}

	// check user role
	if ( ! is_admin_bar_showing() OR ! apply_filters('user_can_clear_cache', current_user_can('manage_options')) ) {
		return;
	}
	global $fsckeycdn_purge_return;
	if(has_action('ce_clear_cache')){
		do_action('ce_clear_cache');
	}
	if(is_network_admin()){
		$fsckeycdn_purge_return = fsckeycdn_purge_all_blog();
	} else {
		$fsckeycdn_purge_return = fsckeycdn_purge_blog();
	}

	if(has_action('ce_clear_cache')){
		remove_action( 'admin_notices', ['Cache_Enabler', 'clear_notice'] );
		remove_action( 'init', ['Cache_Enabler', 'process_clear_request'] );
	}
	add_action( 'admin_notices', 'fsckeycdn_purge_notice', 20 );
}

function fsckeycdn_purge_notice(){
	global $fsckeycdn_purge_return;
	if(is_wp_error( $fsckeycdn_purge_return )){
		wp_die( $fsckeycdn_purge_return->get_error_message() );
	}
	$fsckeycdn_purge_return = json_decode($fsckeycdn_purge_return['body'], true);
	echo fsckeycdn_return_notice( $fsckeycdn_purge_return, true)[0];
}

function fsckeycdn_return_notice($array,$dismissible) {
	if($dismissible){
		$add_class = ' is-dismissible';
		$add_html = '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
	}
	if($array['status'] == 'success'){
		$notice = <<<HTML
<div id="setting-error-settings_updated" class="updated settings-error notice{$add_class}" style="display:block;"> 
	<p><strong>Success: {$array['description']}</strong></p>{$add_html}
</div>
HTML;
		return [$notice,true];
	} elseif(isset($array['description'])) {
		$notice = <<<HTML
<div id="setting-error-settings_updated" class="error settings-error notice{$add_class}" style="display:block;"> 
	<p><strong>Error: {$array['description']}</strong></p>{$add_html}
</div>
HTML;
	} else {
		$notice = <<<HTML
<div id="setting-error-settings_updated" class="error settings-error notice{$add_class}" style="display:block;"> 
	<p><strong>Error: Cannot connect to KeyCDN API server.</strong></p>{$add_html}
</div>
HTML;
	}
	return [$notice,false];
}

function fsckeycdn_control_options() {
	$fsckeycdn_status = fsckeycdn_status();
?>
<div class="wrap">
	<h1>WP KeyCDN Settings</h1>
	<?php
	global $fsckeycdn_realhost,$fsckeycdn_apikey,$fsckeycdn_scheme,$fsckeycdn_user_id,$fsckeycdn_x_pull_key,$fsckeycdn_blog_id,$fsckeycdn_default_settings,$fsckeycdn_plugins_url,$fsckeycdn_ce,$fsckeycdn_id,$fsckeycdn_admin,$fsckeycdn_amane;
	if( is_array($fsckeycdn_id) && isset($fsckeycdn_id[$fsckeycdn_blog_id]) && $fsckeycdn_id[$fsckeycdn_blog_id] === false ) {
		wp_die( 'WP KeyCDN has disabled for this site.' );
	}
	$fsckeycdn_wp_config = fsckeycdn_wp_config();
	$keycdn_id = fsckeycdn_id();
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	$url = parse_url(get_site_url());
	$zone_name = substr(preg_replace('/[^a-z0-9]+/','', strtolower($_SERVER['HTTP_HOST'])),0,20);
	$urlhostarray = explode('.',$url['host']);
	$httphostarray = explode('.',$url['host']);
	if(!(isset($fsckeycdn_amane) && $fsckeycdn_amane === true)){
		if(!isset($urlhostarray[2])){
			wp_die( 'This plugin does not support enable in a blog that use root domain (e.g. example.com). <a target="blank" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Extra-Settings-For-Root-Domain">How to fix it?</a>' );
		} elseif(!isset($httphostarray[2])){
			wp_die( 'This plugin does not support enable in a blog that use root domain (e.g. example.com). <a target="blank" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Extra-Settings-For-Root-Domain">How to fix it?</a>' );
		}
	}
	if(isset($url['path'])&&$url['path']!='/'){
		wp_die( 'This plugin does not support enable in a blog that not installed in root path.' );
	}
	if(filter_var($url['host'], FILTER_VALIDATE_IP)) {
		wp_die( 'This plugin does not support enable or configuration in a blog that use IP but not a domain.' );
	}
	$default_settings = [
		'status' => 'active',
		'type' => 'pull',
		'forcedownload' => 'disabled',
		'cors' => 'disabled',
		'gzip' => 'enabled',
		'expire' => '0',
		'http2' => 'enabled',
		'securetoken' => 'disabled',
		'cacheignorecachecontrol' => 'enabled',
		'cacheignorequerystring' => 'disabled',
		'cachestripcookies' => 'enabled',
		'cachecanonical' => 'disabled',
		'cacherobots' => 'disabled',
		'cachehostheader' => 'disabled',
		'cachecookies' => 'enabled',
	];
	if($fsckeycdn_status && ((isset($_GET['setup']) && $_GET['setup'] != 'yes') || !isset($_GET['setup']))){
		if(isset($_POST['resetzone']) && $_POST['resetzone'] == 'everything' && is_super_admin() && !$fsckeycdn_wp_config){
			delete_option( 'fsckeycdn_id' );
			wp_die( '<div id="setting-error-settings_updated" class="updated settings-error notice"><p><strong>Success: KeyCDN disabled for this site successfully, but not fully disabled. <a href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#How-to-fully-disable-this-plugin" target="_blank">How to fully disable it?</a></strong></p></div>' );
		}
		if(isset($_POST['purge']) && $_POST['purge'] == 'everything' && is_super_admin()){
			if(has_action('ce_clear_cache')){
				do_action('ce_clear_cache');
			}
			$purge_return = fsckeycdn_purge_all();
			if(is_wp_error( $purge_return )){
				wp_die( $purge_return->get_error_message() );
			}
			$purge_return = json_decode($purge_return['body'], true);
			echo fsckeycdn_return_notice($purge_return,true)[0];
		}
		if(is_super_admin()){ ?>
			<table class="form-table"><tbody>
				<tr>
					<th scope="row">X-Pull Key</th>
					<td><code><?php echo $fsckeycdn_x_pull_key;?></code></td>
				</tr>
			</tbody></table>
			<form method="post" action="options-general.php?page=full-site-cache-kc" style="display: inline;">
				<input type="hidden" name="purge" value="everything" />
				<input type="submit" value="Purge Everything" class="button" />
			</form>
			<?php if(!$fsckeycdn_wp_config) { ?>
				<form method="post" action="options-general.php?page=full-site-cache-kc" style="display: inline;">
					<input type="hidden" name="resetzone" value="everything" />
					<input type="submit" value="Disable KeyCDN" class="button" onclick='javascript:return confirm("Are you sure to Disable KeyCDN?");' />
				</form>
			<?php } ?>
			<p>
				<a target="_blank" href="https://app.keycdn.com/zones/edit/<?php echo $keycdn_id;?>">Setup Zone in KeyCDN</a> | <a target="_blank" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Advance-Options">Advance Options</a>
			<?php if(has_action('ce_clear_cache')){ ?>
				 | <a href="options-general.php?page=cache-enabler">Cache Behavior Settings</a>
			</p>
			<?php } else { ?>
			</p>
			<p>To improve performance, pleace disable all others cache plugin and use <a target="_blank" href="https://wordpress.org/plugins/cache-enabler/">Cache Enabler - WordPress Cache</a> instead (Optional), that plugin works perfect with this plugin, and you can change cache the behavior settings. <a href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#About-%E2%80%9CCache-Enabler%E2%80%9D-plugin" target="_blank">Learn more…</a></p>
			<?php }
		}
	} elseif(isset($_GET['setup']) && $_GET['setup'] == 'yes' && !$fsckeycdn_wp_config) {
		if(!is_super_admin()){
			wp_die( 'KeyCDN is disabled, only super admin can enable KeyCDN.' );
		}
		if(!($fsckeycdn_realhost == $fsckeycdn_admin && isset($fsckeycdn_x_pull_key) && isset($fsckeycdn_apikey) && isset($fsckeycdn_user_id) )) {
			$check_settings = false;
			if($_GET['login']=='yes'){
				$check_settings = true;
			}
			if($_POST['fsckeycdn_apikey'] && is_numeric($_POST['fsckeycdn_user_id']) && $_POST['fsckeycdn_default_sslcert']){
				$check_return = wp_remote_request('https://'.$_POST['fsckeycdn_apikey'].'@api.keycdn.com/zones.json',['method' => 'GET','timeout' => 20,]);
				if(is_wp_error( $check_return )){
					wp_die( $check_return->get_error_message() );
				}
				$check_return = json_decode($check_return['body'],true);
				if($check_return['status'] == 'success'){
					$check_settings = true;
				} else {
					echo fsckeycdn_return_notice($check_return,true)[0];
				}
			}
			if(!$check_settings){
			?>
				<p>Before that, you need to have a KeyCDN account, if you didn’t have one, you can <a target="_blank" href="https://app.keycdn.com/signup?a=7126">sign up by this link</a> and get 250GB of free traffic.</p>
				<p>If you already created a KeyCDN Zone for this plugin, pleace delete it. This plugin will automatically create one for you.</p>
				<form method="post" action="options-general.php?page=full-site-cache-kc&amp;setup=yes">
					<table class="form-table"><tbody>
						<tr>
							<th scope="row"><label for="fsckeycdn_apikey">KeyCDN Secret API Key</label></th>
							<td>
								<input name="fsckeycdn_apikey" type="text" id="fsckeycdn_apikey" value="<?php echo $_POST['fsckeycdn_apikey'];?>" class="regular-text">
								<p class="description">Change it to your own Secret API Key, pleace check this twice, if you enter a wrong Secret API Key, your server IP might be blocked, you can <a target="_blank" href="https://app.keycdn.com/users/authSettings">find your own Secret API Key at here</a>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="fsckeycdn_user_id">KeyCDN User ID</label></th>
							<td>
								<input name="fsckeycdn_user_id" type="text" id="fsckeycdn_user_id" value="<?php echo $_POST['fsckeycdn_user_id'];?>" class="regular-text">
								<p class="description">Change it to your User ID, use decimal but not hex, you can <a target="_blank" href="https://app.keycdn.com/users/settings">find it at here</a></p>
							</td>
						</tr>
						<tr>
							<th width="33%" scope="row"><label for="fsckeycdn_default_sslcert">SSL Type</label></th>
							<td>
								<select name="fsckeycdn_default_sslcert" id="fsckeycdn_default_sslcert">
									<option value="letsencrypt" selected="selected">Let’s Encrypt SSL</option>
									<option value="shared">Shared SSL</option>
								</select>
								<p class="description">Choose “Let’s Encrypt SSL” if you want to get a FREE certificate automatically. If you want to use Custom SSL, choose “Shared SSL”, and after setup you can change it in KeyCDN.</p>
							</td>
						</tr>
					</tbody></table>
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save"></p>
				</form>
			<?php
				wp_die();
			}
			$new_admin_page = $fsckeycdn_scheme.'://'.$fsckeycdn_admin.'/wp-login.php?redirect_to='.urlencode($_SERVER['REQUEST_URI']);
			?>
			<h3>Enabling the KeyCDN</h3>
			<p>Complete the following steps to enable the features for KeyCDN.</p>
			<div class="updated inline"><p><strong>Caution:</strong> I recommend you back up your existing <code>wp-config.php</code> file.</p></div>
			<ol>
				<li>
					<p>Add the following to your <code>wp-config.php</code> file in <code><?php echo ABSPATH;?></code> <strong>above</strong> the line reading <code>/* That’s all, stop editing! Happy blogging. */</code>:</p>
					<textarea class="code" readonly="readonly" cols="100" rows="12">/* Start WP KeyCDN code */
$fsckeycdn_apikey = '<?php echo $_POST['fsckeycdn_apikey'];?>';
$fsckeycdn_user_id = <?php echo $_POST['fsckeycdn_user_id'];?>;
$fsckeycdn_x_pull_key = '<?php echo wp_generate_password( 15, false, false ); ?>'; // A random key.
$fsckeycdn_variable_key = true;
$fsckeycdn_default_settings['sslcert'] = '<?php echo $_POST['fsckeycdn_default_sslcert'];?>';
<?php if(is_ssl()){ $ssl_settings = 'true'; } else { $ssl_settings = 'false'; } echo '$fsckeycdn_useHTTPS = '.$ssl_settings.';'; ?>

require_once('<?php echo plugin_dir_path( FSKEYCDN__FILE__ );?>include.php');
/* End WP KeyCDN code */</textarea>
					<p>You can find advance options for it <a target="_blank" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Advance-Options">here</a>.</p>
				</li>
				<li>
					<p>Add following DNS records, <strong>replacing</strong> existing records, BIND format DNS:</p>
					<textarea class="code" readonly="readonly" cols="100" rows="4"><?php echo $fsckeycdn_admin; ?> 300 IN A/CNAME [YOUR_ORIGIN_SERVER]

<?php echo $_SERVER['HTTP_HOST']; ?> 300 IN CMANE <?php echo $zone_name.'-'.dechex($_POST['fsckeycdn_user_id']).'.kxcdn.com'; ?></textarea>
					<p>In order to go to the Log In, you may have to add the new domain <code><?php echo $fsckeycdn_admin;?></code> to your hosting service.</p>
					<h3>How to set up DNS?</h3>
					<ol>
						<li><a target="_blank" href="https://www.keycdn.com/support/create-cname-dns-record-in-cpanel/">Create a CNAME DNS Record in cPanel</a></li>
						<li><a target="_blank" href="https://www.keycdn.com/support/how-to-create-a-cname-in-cloudflare/">Create a CNAME in CloudFlare</a></li>
					</ol>
				</li>
			</ol>
			<p>Once you complete these steps, your KeyCDN is configured but not enabled. You will have to log in again, and then go to the next step.</p>
			<a target="_blank" class="button-primary" href="<?php echo $new_admin_page;?>">Log In</a>
		<?php
		} else {
			update_option( 'siteurl', $fsckeycdn_scheme.'://'.$fsckeycdn_realhost );
			$zone_check['id'] = get_option( 'fsckeycdn_id', false, false );
			if($zone_check['id']){
				$list_return['status'] = 'success';
			} else {
				$list_return = wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones.json',['method' => 'GET','timeout' => 20,]);
				if(is_wp_error( $list_return )){
					wp_die( $list_return->get_error_message() );
				}
				$list_return = json_decode($list_return['body'],true);
				$zone_check = fsckeycdn_check($list_return['data']['zones'],$zone_name,'name');
				if($list_return['status'] == 'success'){
					$zone_check = fsckeycdn_check($list_return['data']['zones'],$zone_name,'name');
				}
			}
			if($list_return['status'] == 'success'){
				if(!$zone_check){
					$default_settings['name'] = $zone_name;
					$default_settings['originurl'] = $fsckeycdn_scheme.'://'.$fsckeycdn_realhost;
					$default_settings['cachemaxexpire'] = 10080;
					$default_settings['sslcert'] = 'shared';
					$default_settings['cachexpullkey'] = $fsckeycdn_x_pull_key;
					$default_settings = array_merge($default_settings,$fsckeycdn_default_settings);
					$add_body = http_build_query($default_settings);
					$add_return = wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zones.json',[
						'method' => 'POST',
						'body' => $add_body,
						'timeout' => 20,
					]);
					if(is_wp_error( $add_return )){
						wp_die( $add_return->get_error_message() );
					}
					$add_return = json_decode($add_return['body'],true);
					$notice = fsckeycdn_return_notice($add_return,true);
					if($notice[1]){
						$setup_zonealiases = true;
						update_option( 'fsckeycdn_id', $add_return['data']['zone']['id'] );
					}
					echo $notice[0];
				} else {
					update_option( 'fsckeycdn_id', $zone_check['id'] );
					$aliaslist_return = wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zonealiases.json',['method' => 'GET','timeout' => 20,]);
					$alias_check = fsckeycdn_check($aliaslist_return['data']['zonealiases'],$_SERVER['HTTP_HOST'],'name');
					if(!$alias_check){
						$notice = <<<HTML
<div id="setting-error-settings_updated" class="error settings-error notice is-dismissible"> 
	<p><strong>Zone was found.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
</div>
HTML;
						echo $notice;
					} else {
						$notice = <<<HTML
<p><strong>Success: You finished setup!</strong></p>
HTML;
						wp_die( $notice );
					}
				}
				if($setup_zonealiases){
					$addzonealiases = [
						'zone_id' => $add_return['data']['zone']['id'],
						'name' => $_SERVER['HTTP_HOST'],
					];
					$addzonealiases_body = http_build_query($addzonealiases);
					$addzonealiases_return = wp_remote_request('https://'.$fsckeycdn_apikey.'@api.keycdn.com/zonealiases.json',[
						'method' => 'POST',
						'body' => $addzonealiases_body,
						'timeout' => 20,
					]);
					if(is_wp_error( $addzonealiases_return )){
						wp_die( $addzonealiases_return->get_error_message() );
					}
					$addzonealiases_return = json_decode($addzonealiases_return['body'], true);
					$notice = fsckeycdn_return_notice($addzonealiases_return);
					if($notice[1]){
						wp_die( $notice[0].'<p><strong>Success: You finished setup!</strong></p>' );
					} else {
						echo $notice[0];
					}
				}
			} elseif(isset($list_return['description'])){
				$notice = <<<HTML
<div id="setting-error-settings_updated" class="error settings-error notice"> 
	<p><strong>Error: {$list_return['description']}. Please check your API Key settings.</strong></p>
</div>
<a class="button-primary" href="options-general.php?page=full-site-cache-kc&amp;setup=yes">Refresh This Page to Continue/Retry</a>
HTML;
				wp_die( $notice );
			} else {
				$notice .= <<<HTML
<div id="setting-error-settings_updated" class="error settings-error notice"> 
	<p><strong>Error: Cannot connect to KeyCDN API server.</strong></p>
</div>
<a class="button-primary" href="options-general.php?page=full-site-cache-kc&amp;setup=yes">Refresh This Page to Continue/Retry</a>
HTML;
				wp_die( $notice );
			}
			 ?>
			<h3>Enabling the KeyCDN</h3>
			<p>You need to add Zonealias by your self:</p>
			<ol>
				<li>
					<p>Add/Replace following DNS Records. BIND format DNS:</p>
					<textarea class="code" readonly="readonly" cols="100" rows="2"><?php echo $_SERVER['HTTP_HOST']; ?> 300 IN CMANE <?php echo $zone_name.'-'.dechex($fsckeycdn_user_id).'.kxcdn.com'; ?></textarea>
					<h3>How to set up DNS?</h3>
					<ol>
						<li><a target="_blank" href="https://www.keycdn.com/support/create-cname-dns-record-in-cpanel/">Create a CNAME DNS Record in cPanel</a></li>
						<li><a target="_blank" href="https://www.keycdn.com/support/how-to-create-a-cname-in-cloudflare/">Create a CNAME in CloudFlare</a></li>
					</ol>
				</li>
				<li>
					<p>Go to <a href="https://app.keycdn.com/zonealiases/add" target="_blank">KeyCDN Add Zonealias page</a>, set Alias to <code><?php echo $_SERVER['HTTP_HOST'];?></code>, choose Zone to <code><?php echo $zone_name;?></code>, and add.</p>
					<h3>How to Create a Zonealias?</h3>
					<ol>
						<li><a target="_blank" href="https://www.keycdn.com/support/create-a-zonealias/">Create a Zonealias</a></li>
						<li><a target="_blank" href="https://www.keycdn.com/support/delete-a-zonealias/">Delete a Zonealias</a></li>
					</ol>
				</li>
			</ol>
			<p>Once you complete these steps, your KeyCDN is enabled and configured.</p>
		<?php }
	} elseif(isset($_GET['setup']) && $_GET['setup'] == 'generate'){
		?>
		<h2>Generate Configuration (for Manual Setup)</h2>
		<p>This page can automatically generate configuration for your site.</p>
		<p><code>wp-config.php</code> configuration:</p>
<textarea style="width: 100%; height: 16em;" onclick="this.focus();this.select()" readonly="readonly" class="code">/* Start KeyCDN code */
$fsckeycdn_apikey = 'APIKEY'; // Change it to your own Secret API Key.
$fsckeycdn_x_pull_key = '<?php echo wp_generate_password( 15, false, false ); ?>'; // A random key
$fsckeycdn_id = [
	<?php echo $fsckeycdn_blog_id; ?> => 10001, // Change the value to KeyCDN Zone ID.
]; // The key (<?php echo $fsckeycdn_blog_id; ?>) is blog id. the value is KeyCDN Zone ID.
<?php if(is_ssl()){ $ssl_settings = 'true; // Your server support HTTPS (even if you don’t have a trasted SSL certificate), enable it will let KeyCDN automatically use Let’s Encrypt SSL certificate (for FREE).'; } else { $ssl_settings = 'false; // Your server is not support SSL, vist this page use SSL to enable this (even if you don’t have a trasted SSL certificate).'; } echo '$fsckeycdn_useHTTPS = '.$ssl_settings; ?>

require_once('<?php echo plugin_dir_path( FSKEYCDN__FILE__ );?>include.php'); // This plugin need run some scripts before everything, so you need to add this, if you use a different location for plugins, change it.
/* End KeyCDN code */</textarea>
		<p>BIND format DNS configuration:</p>
<textarea style="width: 100%; height: 5em;" onclick="this.focus();this.select()" readonly="readonly" class="code"><?php echo $fsckeycdn_admin; ?> 300 IN A/CNAME [YOUR_ORIGIN_SERVER]

<?php echo $_SERVER['HTTP_HOST']; ?> 300 IN CMANE [KeyCDN Zonealias]</textarea>
	<?php } else {
		if(!is_super_admin()){
			wp_die( 'KeyCDN is disabled, only super admin can enable KeyCDN.' );
		} ?>
		<p>You didn’t enable KeyCDN yet!</p>
		<?php if(!$fsckeycdn_wp_config) { ?><a class="button-primary" href="options-general.php?page=full-site-cache-kc&amp;setup=yes">Setup Online</a><?php } ?>
		<a class="button" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Manual-Setup" target="_blank">Read Document and Setup Manually</a>
		<a class="button" href="options-general.php?page=full-site-cache-kc&amp;setup=generate">Generate Configuration (for Manual Setup)</a>
		<p>Before use this plugin, you need:</p>
		<ol>
			<li>Disable all others cache plugin and use <a target="_blank" href="https://wordpress.org/plugins/cache-enabler/">Cache Enabler - WordPress Cache</a> instead (Optional).</li>
			<li>Have a KeyCDN account, if you didn’t have one, you can <a target="_blank" href="https://app.keycdn.com/signup?a=7126">sign up by this link</a> and get 250GB of free traffic.</li>
		</ol>
		<h3>Why this plugin</h3>
		<style>td {text-align: center;}</style>
		<table>
			<thead>
				<tr><th></th><th>This plugin</th><th>Others (WP Super Cache, W3 Total Cache…)</th></tr>
			</thead>
			<tbody>
				<tr><th>Reduce Page Generate Time</th><td>Yes</td><td>Yes</td></tr>
				<tr><th>Cache Type</th><td>Layer 7 Cache (CDN)</td><td>Server-side Cache</td></tr>
				<tr><th>Reduce Server Request and Save Bandwidth</th><td>Yes</td><td>No</td></tr>
				<tr><th>Loading Speed</th><td>Always Fastest</td><td>Limited Faster (depends on visitor physical distance)</td></tr>
				<tr><th>SSL, HTTP/2, GZIP, Let’s Encrypt</th><td>Yes</td><td>Depend on your server</td></tr>
				<tr><th>Build-in CDN</th><td>Yes</td><td>No</td></tr>
			</tbody>
		</table>
		<h3>About KeyCDN</h3>
		<ol>
			<li><a target="_blank" href="https://www.keycdn.com/features?a=7126">Features</a></li>
			<li><a target="_blank" href="https://www.keycdn.com/network?a=7126">Network</a></li>
			<li><a target="_blank" href="https://www.keycdn.com/benefits?a=7126">Benefits</a></li>
			<li><a target="_blank" href="https://www.keycdn.com/pricing?a=7126">Pricing</a></li>
		</ol>
	<?php } ?>
	<hr>
	<a href="https://wordpress.org/support/plugin/full-site-cache-kc" class="button">Support Forum</a>
	<?php if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){ ?>
		<p>Detected you are using CloudFlare, <a target="_blank" href="https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Extra-Settings-For-CloudFlare">you need to do some Extra Settings For CloudFlare after you actived KeyCDN.</a></p>
	<?php } ?>
</div>
<?php
}
function fsckeycdn_admin_menu() {
	add_options_page('WP KeyCDN Settings', 'WP KeyCDN', 'administrator', 'full-site-cache-kc', 'fsckeycdn_control_options');
}
