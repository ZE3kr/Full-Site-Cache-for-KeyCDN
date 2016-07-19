<?php
/**
 * @package Full Site Cache for KeyCDN
 * @version 2.2.0
 */
/*
Plugin Name: Full Site Cache for KeyCDN
Plugin URI: https://wordpress.org/plugins/full-site-cache-kc/
Description: This plugin allows full site acceleration for WordPress with KeyCDN, which gives you the advantages of free SSL, HTTP/2, GZIP and more.
Author: ZE3kr
Version: 2.2.0
Network: True
Author URI: https://ze3kr.com/
*/

if ( ! defined( 'ABSPATH' ) ) exit;
if(!defined('FSCKEYCDN_SETUP')){
	$fsckeycdn_realhost = $_SERVER['HTTP_HOST'];
	if(is_ssl()){
		$fsckeycdn_scheme = 'https';
	} else {
		$fsckeycdn_scheme = 'http';
	}
	if (substr($_SERVER['HTTP_HOST'],0,9)=='wp-admin-'){
		$fsckeycdn_admin = $_SERVER['HTTP_HOST'];
	} elseif (substr($_SERVER['HTTP_HOST'],0,9)=='wp-admin.'){
		$fsckeycdn_admin = $_SERVER['HTTP_HOST'];
	} elseif (substr($_SERVER['HTTP_HOST'],0,4)=='www.'){
		$fsckeycdn_admin = 'wp-admin.'.substr($_SERVER['HTTP_HOST'],4);
	} elseif ( isset($fsckeycdn_root_domain_setup) && $fsckeycdn_root_domain_setup === true && !isset(explode('.',$url['host'])[2]) ) {
		$fsckeycdn_admin = 'wp-admin.'.$_SERVER['HTTP_HOST'];
	} else {
		$fsckeycdn_admin = 'wp-admin-'.$_SERVER['HTTP_HOST'];
	}
}

$fsckeycdn_blog_id = get_current_blog_id();

if(is_array($fsckeycdn_apikey)){
	if(isset($fsckeycdn_apikey[$fsckeycdn_blog_id])){
		$fsckeycdn_apikey = $fsckeycdn_apikey[$fsckeycdn_blog_id];
	} elseif(isset($fsckeycdn_apikey['0'.substr($fsckeycdn_blog_id,0,-1).'X'])) {
		$fsckeycdn_apikey = $fsckeycdn_apikey['0'.substr($fsckeycdn_blog_id,0,-1).'X'];
	}
}

define( 'FSKEYCDN_DIR_NAME', plugin_basename( __FILE__ ) );
define( 'FSKEYCDN__FILE__', __FILE__ );

if( PHP_VERSION_ID >= 50400 ){
	$fskeycdn_path = dirname(__FILE__) . '/functions.php';
	if( file_exists( $fskeycdn_path ) ){
		require_once( $fskeycdn_path );
		add_filter( 'plugin_row_meta', 'fsckeycdn_meta', 91, 2 );
		add_filter( 'plugin_action_links', 'fsckeycdn_add_settings', 91, 2 );
		add_action( 'admin_menu', 'fsckeycdn_admin_menu', 5 );
		/* Add rewrite action */
		if(isset($fsckeycdn_x_pull_key) && isset($_SERVER['HTTP_X_PULL']) && $_SERVER['HTTP_X_PULL'] == $fsckeycdn_x_pull_key){
			add_action( 'template_redirect','fsckeycdn_minify_html', 80 );
		} elseif($fsckeycdn_realhost == $fsckeycdn_admin && !strstr($_SERVER['REQUEST_URI'],'/options-general.php')) {
			add_action( 'wp_loaded','fsckeycdn_minify_html_admin', 99 );
		}
		if(fsckeycdn_status()){
			add_action( 'init', 'fsckeycdn_register_publish_hooks', 99 );
			add_action( 'init', 'fsckeycdn_purge_button', 5 );
			add_action( 'wp', 'fsckeycdn_header' );
			add_action( 'admin_bar_menu', 'fsckeycdn_add_admin_links', 99);
			add_action( 'plugins_loaded', 'fsckeycdn_check_ce' );
			add_action( 'transition_comment_status', 'fsckeycdn_change_comment', 91, 3 );
			add_action( 'edit_comment', 'fsckeycdn_edit_comment' );
			add_action( 'pre_comment_approved', 'fsckeycdn_new_comment', 99, 2);
			add_action( 'trashed_post', 'fsckeycdn_purge_blog_cron', 91 );
			add_action( 'switch_theme', 'fsckeycdn_purge_blog_cron', 91 );
			add_action( '_core_updated_successfully', 'fsckeycdn_purge_all_cron', 91 );

			add_action('fsckeycdn_purge_id_hook', 'fsckeycdn_purge_id');
			add_action('fsckeycdn_purge_blog_hook', 'fsckeycdn_purge_blog');
			add_action('fsckeycdn_purge_all_blog_hook', 'fsckeycdn_purge_all_blog');
			add_action('fsckeycdn_purge_all_hook', 'fsckeycdn_purge_all');
			add_action('fsckeycdn_purge_tag_hook', 'fsckeycdn_purge_tag');
		}
	} else {
		function fsckeycdn_notice_meta($meta, $file) {
			if ($file == plugin_basename( __FILE__ )) {
				$meta[] = '<span style="color:red">ERROR, pleace try to reinstall this plugin.</span>';
			}
			return $meta;
		}
		add_filter( 'plugin_row_meta', 'fsckeycdn_notice_meta', 10, 2 );
	}
} else {
	function fsckeycdn_php_meta($meta, $file) {
		if ($file == plugin_basename( __FILE__ )) {
			$meta[] = '<span style="color:red">You must use this plugin with PHP version that higher than 5.4.</span>';
		}
		return $meta;
	}
	add_filter( 'plugin_row_meta', 'fsckeycdn_php_meta', 10, 2 );
}
