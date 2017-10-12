<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Mycred_Monthly_Billing
 * @subpackage Mycred_Monthly_Billing/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mycred_Monthly_Billing
 * @subpackage Mycred_Monthly_Billing/admin
 * @author     WBCom Designs <vapvarun@gmail.com>
 */
class Mycred_Monthly_Billing_Admin {

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
    public function __construct($plugin_name, $version, $loader) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->loader = $loader;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mycred_Monthly_Billing_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mycred_Monthly_Billing_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mycred-monthly-billing-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mycred_Monthly_Billing_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mycred_Monthly_Billing_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mycred-monthly-billing-admin.js', array('jquery'), $this->version, false);
    }

    /* Function to check Main MyCred plugin is active or not
      If not active it will not get active */

    public function mmb_require_check() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if (!is_plugin_active('mycred/mycred.php')) {
            if (!function_exists('mmb_require_notice')) {
                add_action('admin_notices', array($this, 'mmb_require_notice'));
            }
            deactivate_plugins(plugin_basename(__FILE__), true);
        }
    }

    /* Function to display the notice for the admin to activate MyCred plugin */

    public function mmb_require_notice() {

        echo '<div id="message" class="updated fade"><p style="line-height: 150%">';

        echo __('<strong>MyCred</strong> plugin is not activated please activate it first.', $this->plugin_name);

        echo '</p></div>';
    }

    // Restrict Users to create group
    public function bp_user_can_create_groups_custom($can_create, $restricted) {

        if (current_user_can('manage_options')) {
            $can_create = true;
        } else {
            $can_create = false;
        }
        return $can_create;
    }

    /**
     * Monthly payouts
     * On the first page load on the first day of each month
     * award 10 points to all users with the role "Subscriber".
     * @version 1.0
     */
    public function mycred_pro_hourly_payouts() {

        if (defined('DOING_AJAX') && DOING_AJAX)
            return;

        $options = get_option('mmb_settings');
        if (isset($options['mmb_checkbox_join_bp_group']) && $options['mmb_checkbox_join_bp_group'] == 1) {

            global $wpdb;
            $grp_members_tbl = $wpdb->prefix . "bp_groups_members";
            $Settings = get_option('mycred_pref_hooks');
            $join_group_point = $Settings['hook_prefs']['hook_bp_groups']['join']['creds'];

            $this_month = date('n');

            if (get_option('skipp_first_hourly_payout') != 'first_join') {
                if (get_option('mycred_hourly_payout', 0) != $this_month) {

                    // Grab all users for the set role
                    $users = get_users(array(
                        'role' => 'subscriber', // The role
                        'fields' => array('ID')
                    ));

                    // If users were found
                    if ($users) {

                        $type = 'mycred_default';
                        $mycred = mycred($type);
                        // Loop though users
                        foreach ($users as $user) {

                            // Make sure user is not excluded
                            if ($mycred->exclude_user($user->ID)) {
                                continue;
                            }
                            // Make sure users only get this once per month
                            if ($mycred->has_entry('hourly_payout', $this_month, $user->ID, '', $type)) {
                                continue;
                            }

                            $user_group_count = get_user_meta($user->ID, 'total_group_count', true);

                            $mycred_default_count = mycred_get_users_cred($user->ID, 'mycred_default');

                            if ($user_group_count != 0) {

                                if ($join_group_point < 0) {
                                    $cond_join_group_point = -$join_group_point;
                                } else {
                                    $cond_join_group_point = $join_group_point;
                                }
                                $cond_join_group_point_chek = $mycred_default_count - $cond_join_group_point;
                                if ($mycred_default_count >= $cond_join_group_point) {
                                    if ($cond_join_group_point_chek <= $cond_join_group_point) {
                                        $user_info = get_userdata($user->ID);
                                        $to = $user_info->user_email;
                                        $subject = 'Your balance is insufficient for next month transaction.';
                                        $body = 'Your balance ( ' . $cond_join_group_point_chek . ' ) is insufficient for next month transaction! You will be left from groups. So Please Recharge your account.';
                                        $headers = array(
                                            'Content-Type: text/html; charset=UTF-8',
                                            'From: My Network Hub <admin@mynetworkhub.com>',
                                        );
                                        wp_mail($to, $subject, $body, $headers);
                                    }
                                    // Payout
                                    $mycred->add_creds(
                                            'hourly_payout', $user->ID, $join_group_point, 'Monthly %_plural% payout', $this_month, '', $type
                                    );
                                } else {

                                    $qry = "SELECT group_id FROM $grp_members_tbl WHERE user_id = $user->ID";
                                    $groups_ids = $wpdb->get_results($qry);
                                    if (count($groups_ids) != 0) {
                                        foreach ($groups_ids as $index => $group_id) {
                                            groups_remove_member($group_id->group_id, $user->ID);
                                            groups_leave_group($group_id->group_id, $user->ID);
                                        }
                                        $qry = "DELETE FROM $grp_members_tbl WHERE user_id = $user->ID";
                                        $wpdb->get_results($qry);
                                        update_user_meta($user->ID, 'skipp_first_hourly_payout', 'removed_join');
                                    }
                                }
                            }
                        }
                        update_option('mycred_hourly_payout', $this_month);
                    }
                }
            }
        }
    }

    public function groups_joining_custom($group_id, $user_id) {
        update_user_meta($user_id, 'skipp_first_hourly_payout', 'first_join');
    }

}
