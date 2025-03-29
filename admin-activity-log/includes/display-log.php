<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Display Logs
function aal_display_logs() {
    global $wpdb;
    // $table_name = $wpdb->prefix . 'admin_activity_log';
    // $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");


    // $table_name = $wpdb->prefix . 'admin_activity_log';
    $ip_table = $wpdb->prefix . 'ip_address';
    $ip_list = $wpdb->get_results("SELECT * FROM $ip_table ORDER BY ID ASC");

// Check if data exists
if (!empty($ip_list)) {
    foreach ($ip_list as $ip) {
        echo "<h1>IP Address: " . esc_html($ip->ip_address) . "</h1><br>";

    }
} else {
    echo "No records found.";
}
    // $ip=$wpdb->get_results("SELECT * FROM $ip_table ORDER BY ID asc");
$users_table = $wpdb->prefix . 'users';
$posts_table = $wpdb->prefix . 'posts';
$optinon_table= $wpdb->prefix . 'options';
$option=$wpdb->get_results("SELECT * FROM wp_options WHERE option_name LIKE '%site_health%'");

$results = $wpdb->get_results("SELECT * FROM $posts_table ORDER BY ID asc");
$results1=$wpdb->get_results("SELECT * FROM $users_table ORDER BY ID asc");
$results2=$wpdb->get_results("SELECT * FROM $optinon_table WHERE option_name NOT IN ('cron') ORDER BY option_id asc");
$comments_table = $wpdb->prefix . 'comments';
// $results2=$wpdb->get_results("SELECT * FROM $comments_table ORDER BY comment_ID asc");
if (!empty($option)) {
    foreach ($option as $op) {
        echo "<h1>IP Address: " . esc_html($op->site_health) . "</h1><br>";

    }
} else {
    echo "No records found.";
}


// $options_table = $wpdb->prefix . 'options';
echo '<div class="wrap"><h1>Activity Log</h1>';


// Include DataTables CSS & JS
echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">';
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
echo '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';

// First Table
echo '<table id="activityLogTable1" class="wp-list-table widefat fixed striped">
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

echo '</tbody></table>';

// Second Table
echo '<h1>Settings Log</h1>';
echo '<table id="activityLogTable2" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Option Name</th>
                <th>Option Value</th>
             
            </tr>
        </thead>
        <tbody>';

        if ($results2) {
            foreach ($results2 as $log) {
                echo "<tr>
                        <td>{$log->option_id}</td>
                        <td>{$log->option_name}</td>
                        <td>{$log->option_value}</td>
                      </tr>";
            }
        }
         else {
    echo '<tr><td colspan="7">No logs found.</td></tr>';
}

echo '</tbody></table></div>';

// DataTables Initialization
echo '<script>
    jQuery(document).ready(function($) {
        $("#activityLogTable1, #activityLogTable2,#activityLogTable3").DataTable();
    });
</script>';

    //   $comments_table = $wpdb->prefix . 'comments';
    //   $results2 = $wpdb->get_results("SELECT * FROM $comments_table ORDER BY comment_ID ASC");
      
    //   if (!empty($results2)) {
    //       echo "<table border='1' cellspacing='0' cellpadding='5' style='width:100%; border-collapse: collapse;'>
    //               <tr style='background-color: #f2f2f2;'>
    //                   <th>Comment ID</th>
    //                   <th>Post ID</th>
    //                   <th>Author</th>
    //                   <th>Email</th>
    //                   <th>Content</th>
    //                   <th>Date</th>
    //               </tr>";
          
    //       foreach ($results2 as $comment) {
    //           echo "<tr>
    //                   <td>{$comment->comment_ID}</td>
    //                   <td>{$comment->comment_post_ID}</td>
    //                   <td>{$comment->comment_author}</td>
    //                   <td>{$comment->comment_author_email}</td>
    //                   <td>{$comment->comment_content}</td>
    //                   <td>{$comment->comment_date}</td>
    //                 </tr>";
    //       }
          
    //       echo "</table>";
    //   } else {
    //       echo "<p>No comments found.</p>";
    //   }
  

$requests = $wpdb->get_results("SELECT ID, post_title, post_status, post_date FROM {$wpdb->prefix}posts WHERE post_type = 'user_request' ORDER BY post_date DESC");
$site_health=$wpdb->get_results("SELECT * FROM $optinon_table WHERE option_name IN ('_transient_health-check-site-status-result') ORDER BY option_id asc");
echo '<h1>User Requests</h1>';
echo '<table id="activityLogTable3" class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>User Email</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Site_health<th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>';

// if (!empty($requests)) {
//     foreach ($requests as $request) {
//         foreach ($site_health as $health) {
           
//         echo "<tr>
//                 <td>" . esc_html($request->ID) . "</td>
//                 <td>" . esc_html($request->post_title) . "</td>
//                 <td>" . esc_html($request->post_status) . "</td>
//                 <td>" . esc_html($request->post_date) . "</td>
//                 <td>" . esc_html($health->option_name) . "</td>
//                 <td>" . esc_html($health->option_value) . "</td>
//               </tr>";
//     }
// }
// } else {
//     echo '<tr><td colspan="6">No user requests found.</td></tr>';
// }
if (!empty($requests)) {
    foreach ($requests as $request) {
        foreach ($site_health as $health) {
           
        echo "<tr>
                <td>" . esc_html($request->ID) . "</td>
                <td>" . esc_html($request->post_title) . "</td>
                <td>" . esc_html($request->post_status) . "</td>
                <td>" . esc_html($request->post_date) . "</td>
                <td>" . esc_html($health->option_name) . "</td>
                <td>" . esc_html($health->option_value) . "</td>
              </tr>";
    }
}
} else {
    echo '<tr><td colspan="6">No user requests found.</td></tr>';
}
echo '</tbody></table>';
}
