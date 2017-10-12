<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Mycred_Monthly_Billing
 * @subpackage Mycred_Monthly_Billing/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mycred_Monthly_Billing
 * @subpackage Mycred_Monthly_Billing/includes
 * @author     WBCom Designs <vapvarun@gmail.com>
 */
class Mycred_Monthly_Billing {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Mycred_Monthly_Billing_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'mycred-monthly-billing';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Mycred_Monthly_Billing_Loader. Orchestrates the hooks of the plugin.
     * - Mycred_Monthly_Billing_i18n. Defines internationalization functionality.
     * - Mycred_Monthly_Billing_Admin. Defines all hooks for the admin area.
     * - Mycred_Monthly_Billing_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mycred-monthly-billing-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mycred-monthly-billing-i18n.php';

        /**
         * The class responsible for defining custom settings
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mycred-monthly-billing-settings.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-mycred-monthly-billing-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-mycred-monthly-billing-public.php';

        $this->loader = new Mycred_Monthly_Billing_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Mycred_Monthly_Billing_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Mycred_Monthly_Billing_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Mycred_Monthly_Billing_Admin($this->get_plugin_name(), $this->get_version(), $this->get_loader());
        $plugin_admin_page = new Mycred_Monthly_Billing_Options_Page($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_init', $plugin_admin, 'mmb_require_check');
        $this->loader->add_action('admin_menu', $plugin_admin_page, 'mmb_add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin_page, 'mmb_settings_init');
        $this->loader->add_action('mycred_init', $plugin_admin, 'mycred_pro_hourly_payouts');
        $this->loader->add_action('groups_join_group', $plugin_admin, 'groups_joining_custom');
        $this->loader->add_action('bp_user_can_create_groups', $plugin_admin, 'bp_user_can_create_groups_custom', 10, 2);
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Mycred_Monthly_Billing_Public($this->get_plugin_name(), $this->get_version(), $this->get_loader());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Mycred_Monthly_Billing_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
