<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package PluginName
 * @license GPL-2.0+
 * @link    TODO
 * @version 1.0.0
 */
class PluginName {

	/**
	* Refers to a single instance of this class.
	*
	* @var    object
	*/
	protected static $instance = null;

	/**
	* Refers to the slug of the plugin screen.
	*
	* @var    string
	*/
	protected $plugin_screen_slug = null;

	/**
	* Refers to the plugin file name plugin.
	*
	* @var    string
	*/
	// TODO: Update to the base plugin file...
	private $plugin_file = 'plugin-boilerplate.php';
	private $plugin_folder = false;
	private $plugin_slug = false;

	/**
	* Refers to the Github repo of the plugin.
	* ex: https://github.com/ShawONEX/{$repo}
	*
	* @var    string
	*/
	// TODO: Update to the repo name on github
	private $repo = null;

	/**
	* Refers to the access token for Github API
	*
	* @var    string
	*/
	// TODO: Update to the github access token for the project
	private $access_token = null;

	/**
	* Refers to the Github Result
	*
	* @var    string
	*/
	// TODO: Update to the github access token for the project
	private $api_result = null;

	/**
	* Creates or returns an instance of this class.
	*
	* @since     1.0.0
	* @return    PluginName    A single instance of this class.
	*/
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	* Initializes the plugin by setting localization, filters, and administration functions.
	*
	* @since    1.0.0
	*/
	private function __construct() {
		/*
		 * Add the options page and menu item.
		 * Uncomment the following line to enable the Settings Page for the plugin:
		 */
		//add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		/*
		 * Register admin styles and scripts
		 * If the Settings page has been activated using the above hook, the scripts and styles
		 * will only be loaded on the settings page. If not, they will be loaded for all
		 * admin pages.
		 *
		 * add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_styles' ) );
		 * add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
		 */

		// Register site stylesheets and JavaScript
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );

		/*
		 * Github specific update functions
		 * DO NOT REMOVE
		 */
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'set_plugin_transient' ) );
    add_filter( 'plugins_api', array( $this, 'set_plugin_info' ), 10, 3 );
    add_filter( 'upgrader_post_install', array( $this, 'plugin_post_install' ), 10, 3 );
		
		// update message for plugins
		$this->plugin_folder = basename( dirname( __FILE__ ) );
		$this->plugin_slug = "{$this->plugin_folder}/{$this->plugin_file}";
		add_filter ( "in_plugin_update_message-{$folder}/{$this->plugin_file}", array( $this, 'set_plugin_update_message' ) );

		/*
		 * TODO:
		 *
		 * Define the custom functionality for your plugin. The first parameter of the
		 * add_action/add_filter calls are the hooks into which your code should fire.
		 *
		 * The second parameter is the function name located within this class. See the stubs
		 * later in the file.
		 *
		 * For more information:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'TODO', array( $this, 'action_method_name' ) );
		add_filter( 'TODO', array( $this, 'filter_method_name' ) );
	}

	public function set_plugin_update_message() {
    $output = '<br /><br /><strong>Please update the update flow detailed here for updating this plugin.</strong>';
    return print $output;
	}

	private function get_repo_release_info() {
		// Only do this once
		if ( !empty( $this->api_result ) ) {
			return;
		}

		// Query the GitHub API
		$url = "https://api.github.com/repos/ShawONEX/{$this->repo}/releases";

		// We need the access token for private repos
		if ( !empty( $this->access_token ) ) {
			$url = add_query_arg( array( 'access_token' => $this->access_token ), $url );
		}

		// Get the results
		$this->api_result = wp_remote_retrieve_body( wp_remote_get( $url ) );
		if ( !empty( $this->api_result ) ) {
			$this->api_result = @json_decode( $this->api_result );
		}

		// Use only the latest release
		if ( is_array( $this->api_result ) ) {
			$this->api_result = $this->api_result[0];
		}
	}

	public function set_plugin_transient($transient) {
		// If we have checked the plugin data before, don't re-check
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get plugin & GitHub release information
		$this->get_repo_release_info();

		// Check the versions if we need to do an update
		$doUpdate = version_compare( $this->api_result->tag_name, $transient->checked[$this->plugin_slug] );

		// Update the transient to include our updated plugin data
		if ( $doUpdate == 1 ) {
			// commenting out for now, as we want to avoid running through
			// the automatic update process.

			// $package = $this->api_result->zipball_url;

			// // Include the access token for private GitHub repos
			// if ( !empty( $this->this->access_token ) ) {
			// 	$package = add_query_arg( array( "access_token" => $this->this->access_token ), $package );
			// }

			$obj = new stdClass();
			$obj->slug = $this->plugin_slug;
			$obj->new_version = $this->api_result->tag_name;
			$obj->url = $this->plugin_data['PluginURI'];
			$obj->package = false; // $package;
			$transient->response[$this->plugin_slug] = $obj;
		}

		return $transient;
	}

	public function set_plugin_info( $false, $action, $response ) {
		// Get plugin & GitHub release information
		$this->get_repo_release_info();

		// If nothing is found, do nothing
		if ( 
			empty( $response->slug ) 
			|| $response->slug != $this->plugin_slug 
		) {
			return false;
		}

		// Add our plugin information
		$response->last_updated = $this->api_result->published_at;
		$response->slug = $this->plugin_slug;
		$response->plugin_name  = $this->plugin_data['Name'];
		$response->version = $this->api_result->tag_name;
		$response->author = $this->plugin_data['AuthorName'];
		$response->homepage = $this->plugin_data['PluginURI'];

		// This is our release download zip file
		$downloadLink = false; // $this->api_result->zipball_url;

		// Include the access token for private GitHub repos
		// removed for now...
		// if ( !empty( $this->this->access_token ) ) {
		// 		$downloadLink = add_query_arg(
		// 			array( 'access_token' => $this->this->access_token ),
		// 			$downloadLink
		// 		);
		// }
		// $response->download_link = $downloadLink;

		// We're going to parse the GitHub markdown release notes, include the parser
		require_once( plugin_dir_path( __FILE__ ) . 'lib/Parsedown.php' );

		// Create tabs in the lightbox
		$response->sections = array(
			// 'description' => $this->plugin_data['Description'], // not sure we're going to need this...
			'changelog' => class_exists( 'Parsedown' )
				? Parsedown::instance()->parse( $this->api_result->body )
				: $this->api_result->body
		);

		// Gets the required version of WP if available
		$matches = null;
		preg_match( "/requires:\s([\d\.]+)/i", $this->api_result->body, $matches );
		if ( !empty( $matches ) ) {
			if ( is_array( $matches ) ) {
				if ( count( $matches ) > 1 ) {
					$response->requires = $matches[1];
				}
			}
		}

		// Gets the tested version of WP if available
		$matches = null;
		preg_match( "/tested:\s([\d\.]+)/i", $this->api_result->body, $matches );
		if ( !empty( $matches ) ) {
			if ( is_array( $matches ) ) {
				if ( count( $matches ) > 1 ) {
					$response->tested = $matches[1];
				}
			}
		}

		return $response;
	}

	// Perform additional actions to successfully install our plugin
	// probably unnecessary at the moment, but here in case we make a change
	public function plugin_post_install( $true, $hook_extra, $result ) {
		$was_activated = is_plugin_active( $this->plugin_slug );

		// Since we are hosted in GitHub, our plugin folder would have a dirname of
		// reponame-tagname change it to our original one:
		global $wp_filesystem;
		$plugin_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $this->plugin_slug );
		$wp_filesystem->move( $result['destination'], $plugin_folder );
		$result['destination'] = $plugin_folder;

		// Re-activate plugin if needed
		if ( $was_activated ) {
			$activate = activate_plugin( $this->plugin_slug );
		}

		return $result;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	 public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 * @since    1.0.0
	 */
	 public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_styles() {

		/*
		 * Check if the plugin has registered a settings page
		 * and if it has, make sure only to enqueue the scripts on the relevant screens
		 */

		if ( isset( $this->plugin_screen_slug ) ) {

			/*
			 * Check if current screen is the admin page for this plugin
			 * Don't enqueue stylesheet or JavaScript if it's not
			 */

			$screen = get_current_screen();
			if ( $screen->id == $this->plugin_screen_slug ) {
				wp_enqueue_style( 'plugin-name-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), PLUGIN_NAME_VERSION );
			}

		}

	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function register_admin_scripts() {

		/*
		 * Check if the plugin has registered a settings page
		 * and if it has, make sure only to enqueue the scripts on the relevant screens
		 */

		if ( isset( $this->plugin_screen_slug ) ) {

			/*
			 * Check if current screen is the admin page for this plugin
			 * Don't enqueue stylesheet or JavaScript if it's not
			 */

			$screen = get_current_screen();
			if ( $screen->id == $this->plugin_screen_slug ) {
				wp_enqueue_script( 'plugin-name-admin-script', plugins_url('js/admin.js', __FILE__), array( 'jquery' ), PLUGIN_NAME_VERSION );
			}

		}

	}

	/**
	 * Registers and enqueues public-facing stylesheets.
	 *
	 * @since    1.0.0
	 */
	public function register_plugin_styles() {
		wp_enqueue_style( 'plugin-name-plugin-styles', plugins_url( 'css/display.css', __FILE__ ), PLUGIN_NAME_VERSION );
	}

	/**
	 * Registers and enqueues public-facing JavaScript.
	 *
	 * @since    1.0.0
	 */
	public function register_plugin_scripts() {
		wp_enqueue_script( 'plugin-name-plugin-script', plugins_url( 'js/display.js', __FILE__ ), array( 'jquery' ), PLUGIN_NAME_VERSION );
	}

	/**
	 * Registers the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'plugin-name' to the name of your plugin
		 */
		$this->plugin_screen_slug = add_plugins_page(
			__('Page Title', 'plugin-name-locale'),
			__('Menu Text', 'plugin-name-locale'),
			'read',
			'plugin-name',
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Renders the options page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once('views/admin.php');
	}

	/*
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
	 *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// TODO: Define your action method here
	}

	/*
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since       1.0.0
	 */
	public function filter_method_name() {
		// TODO: Define your filter method here
	}

}