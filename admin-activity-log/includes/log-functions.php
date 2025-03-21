<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Log Admin Activities
function aal_log_activity($action, $author = '', $description = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'admin_activity_log';

    $user_id = get_current_user_id();
    $ip_address = $_SERVER['REMOTE_ADDR'];

    $wpdb->insert(
        $table_name,
        [
            'user_id'     => $user_id,
            'action'      => $action,
            'ip_address'  => $ip_address,
            'author'      => $author,
            'description' => $description
        ]
    );
}

// Hook into post updates
add_action('post_updated', function($post_ID, $post_after, $post_before) {
    $author = get_the_author_meta('display_name', $post_after->post_author);
    $description = "Updated post titled '{$post_after->post_title}'";

    aal_log_activity("Updated post ID: $post_ID", $author, $description);
}, 10, 3);

// Hook into user logins
add_action('wp_login', function($user_login, $user) {
    $author = $user->display_name;
    $description = "User '$user_login' successfully logged in.";

    aal_log_activity("User logged in: $user_login", $author, $description);
}, 10, 2);
