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
// add_action('after_setup_theme', function() {
//     global $wpdb;
//     $column_name = 'ip_address';

//     $check_column = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}options LIKE '$column_name'");

//     if (empty($check_column)) {
//         $wpdb->query("ALTER TABLE {$wpdb->prefix}options ADD `$column_name` TEXT NOT NULL");
//         error_log("Column '$column_name' added successfully.");
//     } else {
//         error_log("Column '$column_name' already exists.");
//     }
// });

global $wpdb;
$table_name = $wpdb->prefix . 'ip_address';
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($ip_address === '::1') {
    $ip_address = '127.0.0.1';
}
// Create table if it doesn't exist
$wpdb->query("CREATE TABLE IF NOT EXISTS $table_name (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARBINARY(16) NOT NULL,
    visit_time DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Check if the IP address already exists
$exists = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table_name WHERE ip_address = %s",
    $ip_address
));

if (!$exists) {
    $wpdb->insert($table_name, [
        'ip_address' => $ip_address
    ]);
}


