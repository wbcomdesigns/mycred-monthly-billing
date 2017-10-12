<?php
//
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
class Mycred_Monthly_Billing_Options_Page {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function mmb_add_admin_menu() {

        add_options_page(__('MyCred Monthly Billing', $this->plugin_name), __('MyCred Monthly Billing', $this->plugin_name), 'manage_options', 'mycred_monthly_billing', array($this, 'mmb_options_page'));
    }

    public function mmb_settings_init() {

        register_setting('mmb_settings', 'mmb_settings');

        add_settings_section(
                'mmb_bp_pluginPage_section', __('BuddyPress: Group', $this->plugin_name), array($this, 'mmb_bp_settings_section_callback'), 'mmb_settings'
        );

        add_settings_field(
                'mmb_checkbox_create_bp_group', __('Monthly Billing For Creating Groups', $this->plugin_name), array($this, 'mmb_checkbox_create_bp_group_render'), 'mmb_settings', 'mmb_bp_pluginPage_section'
        );

        add_settings_field(
                'mmb_checkbox_join_bp_group', __('Monthly Billing For Joining Groups', $this->plugin_name), array($this, 'mmb_checkbox_join_bp_group_render'), 'mmb_settings', 'mmb_bp_pluginPage_section'
        );
    }

    public function mmb_checkbox_create_bp_group_render() {

        $options = get_option('mmb_settings');
        ?>
        <input type='checkbox' name='mmb_settings[mmb_checkbox_create_bp_group]' <?php checked(isset($options['mmb_checkbox_create_bp_group']), 1); ?> value='1'>
        <?php
    }

    public function mmb_checkbox_join_bp_group_render() {

        $options = get_option('mmb_settings');
        ?>
        <input type='checkbox' name='mmb_settings[mmb_checkbox_join_bp_group]' <?php checked(isset($options['mmb_checkbox_join_bp_group']), 1); ?> value='1'>
        <?php
    }

    public function mmb_bp_settings_section_callback() {

        echo __('BuddyPress: Group section', $this->plugin_name);
    }

    public function mmb_options_page() {
        ?>
        <form action='options.php' method='post'>

            <h1>MyCred Monthly Billing Options</h1>

            <?php
            settings_fields('mmb_settings');
            do_settings_sections('mmb_settings');
            submit_button();
            ?>

        </form>
        <?php
    }

}
