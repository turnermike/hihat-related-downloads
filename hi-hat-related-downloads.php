<?php
/**
 * Hi-hat Related Downloads
 *
 * @package   Hi_Hat_Related_Downloads
 * @author    Mike Turner <turner.mike@gmail.com>
 * @license   GPL-2.0+
 * @link      http://hi-hatconsulting.com
 * @copyright 2014 Hi-hat Consulting
 *
 * @wordpress-plugin
 * Plugin Name:       Hi-hat Related Downloads
 * Plugin URI:        http://hi-hatconsulting.com
 * Description:       A plugin to display related downloads to posts and pages.
 * Version:           1.0.0
 * Author:            Mike Turner
 * Author URI:        http://hi-hatconsulting.com
 * Text Domain:       hi-hat-related-downloads
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * WordPress-Plugin-Boilerplate: v2.6.1
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-hi-hat-related-downloads.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Hi_Hat_Related_Downloads', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Hi_Hat_Related_Downloads', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Hi_Hat_Related_Downloads', 'get_instance' ) );


