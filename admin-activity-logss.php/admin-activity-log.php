<?php
/*
Plugin Name: Admin Activity Logss
Description: Tracks admin activities such as post updates, page edits, and user logins.
Version: 6.7.0
Author: Sankar
*/

if (!defined('ABSPATH')) exit;

final class Admin_Activity_Log {

    public function __construct() {
        $this->define_constants();
        $this->includes();

        // Hook into WordPress
        register_activation_hook(__FILE__, [$this, 'create_table']);
        add_action('admin_menu', [$this, 'register_menu_pages']);
        add_action('admin_menu', [$this, 'register_hidden_setting_page']);
    }

    private function define_constants() {
        define('AAL_PLUGIN_PATH', plugin_dir_path(__FILE__));
    }

    private function includes() {
        require_once AAL_PLUGIN_PATH . 'includes/log-functions.php';
    }

    public function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'admin_activity_log';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            action TEXT NOT NULL,
            ip_address VARCHAR(100) NOT NULL,
            author VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function register_menu_pages() {
        // Main log list
        add_menu_page(
            
            'Activity Log',
            'Activity Log',
            'manage_options',
            'activity-log',
            [$this, 'display_logs_page'],
            'dashicons-visibility',
            30
        );

        // Hidden edit log page
        add_submenu_page(
            null,
            'Edit Log',
            'Edit Log',
            'manage_options',
            'edit-log',
            [$this, 'edit_log_page']
        );
    }

    public function register_hidden_setting_page() {
        add_submenu_page(
            null,
            'Edit Setting',
            'Edit Setting',
            'manage_options',
            'edit-setting',
            [$this, 'edit_setting_page']
        );
    }

    public function display_logs_page() {
          require_once plugin_dir_path(__FILE__) . 'includes/display-log.php';
    }

    public function edit_log_page() {
        include AAL_PLUGIN_PATH . 'includes/edit-log.php';
    }

    public function edit_setting_page() {
        include AAL_PLUGIN_PATH . 'includes/edit-setting.php';
    }
}

// Initialize the plugin
new Admin_Activity_Log();


