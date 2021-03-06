<?php
/**
 * @package PluginName
 * @license GPL-2.0+
 * @link    TODO
 * @version 1.0.0
 *
 * Plugin Name: TODO
 * Description: TODO
 * Version: 1.0.0
 * Author: Corus Entertainment
 * License: GPLv2 or later
 * Text Domain: TODO
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * The following constant is used to define a constant for this plugin to make it
 * easier to provide cache-busting functionality on loading stylesheets
 * and JavaScript.
 *
 * After you've defined these constants, do a find/replace on the constants
 * used throughout the rest of this file.
 */
// TODO: Replace 'PLUGIN_NAME' wih the name of your class
if ( ! defined( 'PLUGIN_NAME_VERSION' ) ) {

	// TODO: Make sure that this version correspondings to the value in the 'Version' in the header
	define( 'PLUGIN_NAME_VERSION', '1.0.0' );

}

// TODO: replace `class-plugin-boilerplate.php` with the name of the actual plugin's class file
require_once( plugin_dir_path( __FILE__ ) . 'class-plugin-boilerplate.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
// TODO: replace PluginName with the name of the plugin defined in `class-plugin-boilerplate.php`
register_activation_hook( __FILE__, array( 'PluginName', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PluginName', 'deactivate' ) );

// TODO: replace PluginName with the name of the plugin defined in `class-plugin-boilerplate.php`
PluginName::get_instance();