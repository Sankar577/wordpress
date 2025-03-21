<?php
/*
Plugin Name: Admin Activity Log
Description: Tracks admin activities such as post updates, page edits, and user logins.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit; // Prevent direct access

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/log-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/display-log.php';

// Plugin Activation Hook
function aal_create_table() {
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
register_activation_hook(__FILE__, 'aal_create_table');

// Admin Menu for Viewing Logs
function aal_add_menu() {
    add_menu_page('Admin Activity Log', 'Activity Log', 'manage_options', 'admin-activity-log', 'aal_display_logs');
}
add_action('admin_menu', 'aal_add_menu');
