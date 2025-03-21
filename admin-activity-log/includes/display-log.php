<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Display Logs
function aal_display_logs() {
    global $wpdb;
    // $table_name = $wpdb->prefix . 'admin_activity_log';
    // $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");


    // $table_name = $wpdb->prefix . 'admin_activity_log';
$users_table = $wpdb->prefix . 'users';
$posts_table = $wpdb->prefix . 'posts';
$results = $wpdb->get_results("SELECT * FROM $posts_table ORDER BY ID asc");
$results1=$wpdb->get_results("SELECT * FROM $users_table ORDER BY ID asc");
// $comments_table = $wpdb->prefix . 'comments';
// $options_table = $wpdb->prefix . 'options';
echo '<div class="wrap"><h1>Activity Log</h1>';

// Include DataTables CSS & JS
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">';
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';

// Table Structure
echo '<table id="activityLogTable" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Post Name</th>
                <th>ID</th>
                <th>Type</th>
                <th>Title</th>
                <th>Post Date</th>
                <th>User Login</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>';

if ($results) {
    foreach ($results as $log) {
        foreach ($results1 as $user) {
            if ($user->ID == $log->post_author) {
                $user_login = $user->user_login;
                $user_email = $user->user_email;
                break;
            }
        }
        echo "<tr>
                <td>{$log->post_name}</td>
                <td>{$log->ID}</td>
                <td>{$log->post_type}</td>
                <td>{$log->post_title}</td>
                <td>{$log->post_date}</td>
                <td>{$user_login}</td>
                <td>{$user_email}</td>
              </tr>";
    }
} else {
    echo '<tr><td colspan="7">No logs found.</td></tr>';
}

echo '</tbody></table></div>';

// DataTables Initialization Script
echo '<script>
        jQuery(document).ready(function($) {
            $("#activityLogTable").DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "pageLength": 7
            });
        });
      </script>';
}