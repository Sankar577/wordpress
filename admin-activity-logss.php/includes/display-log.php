<?php
add_action('admin_init', function() {
    if (isset($_POST['export_csv'])) {
        $display = new ActivityLogDisplay();
        $display->export_post_logs();
    }

    if (isset($_POST['export_csv1'])) {
        $display = new ActivityLogDisplay();
        $display->export_settings_logs();
    }
});
if (class_exists('ActivityLogDisplay')) {
    $display = new ActivityLogDisplay();
    $display->render_logs_page();
} else {
    echo 'ActivityLogDisplay class not found.';
}


class ActivityLogDisplay {
    
    private $wpdb;
    private $users_table;
    private $posts_table;
    private $options_table;
    private $ip_table;
    private $comments_table;

    public function __construct() {
          
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->users_table = $wpdb->prefix . 'users';
        $this->posts_table = $wpdb->prefix . 'posts';
        $this->options_table = $wpdb->prefix . 'options';
        $this->ip_table = $wpdb->prefix . 'ip_address';
        $this->comments_table = $wpdb->prefix . 'comments';

      
    }

    

    public function export_post_logs() {
        $results = $this->wpdb->get_results("SELECT * FROM $this->posts_table ORDER BY ID ASC");
        $users = $this->wpdb->get_results("SELECT * FROM $this->users_table ORDER BY ID ASC");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=activity-log.csv');
        $output = fopen('php://output', 'w');

        fputcsv($output, ['Post Name', 'ID', 'Type', 'Title', 'Post Date', 'User Login', 'Email']);

        foreach ($results as $log) {
            $user_login = '';
            $user_email = '';
            foreach ($users as $user) {
                if ($user->ID == $log->post_author) {
                    $user_login = $user->user_login;
                    $user_email = $user->user_email;
                    break;
                }
            }

            fputcsv($output, [
                $log->post_name,
                $log->ID,
                $log->post_type,
                $log->post_title,
                $log->post_date,
                $user_login,
                $user_email
            ]);
        }

        fclose($output);
        exit;
    }

    public function export_settings_logs() {
        $results = $this->wpdb->get_results("SELECT * FROM $this->options_table WHERE option_name NOT IN ('cron') ORDER BY option_id ASC");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=settings-log.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Option Name', 'Option Value']);

        foreach ($results as $log) {
            fputcsv($output, [
                $log->option_id,
                $log->option_name,
                $log->option_value
            ]);
        }

        fclose($output);
        exit;
    }

    public function render_logs_page() {
        $this->handle_hide_show_logs();

        if (get_transient('hide_activity_logs')) {
            echo '<form method="post"><input type="submit" name="show_logs" class="button button-primary" value="Show Logs"></form>';
            echo '<div class="updated"><p>The logs are currently Clear. Click "Show Logs" to make them visible again.</p></div>';
            return;
        }

        $this->render_ip_address_section();
        $this->render_post_logs_section();
        $this->render_settings_logs_section();
        $this->render_user_requests_section();
        $this->render_users_section();
    }

    private function handle_hide_show_logs() {
        if (isset($_POST['hide_logs'])) {
            set_transient('hide_activity_logs', true, 3600);
            echo '<div class="updated"><p>Logs are now cleared.</p></div>';
        }

        if (isset($_POST['show_logs'])) {
            delete_transient('hide_activity_logs');
            echo '<div class="updated"><p>Logs are now visible again.</p></div>';
        }

        if (!get_transient('hide_activity_logs')) {
            echo '<form method="post"><input type="submit" name="hide_logs" class="button button-danger" value="Clear Logs" onclick="return confirm(\'Are you sure you want to clear all logs?\');" style="margin-bottom:10px;"></form>';
        }
    }

    private function render_ip_address_section() {
        $ips = $this->wpdb->get_results("SELECT * FROM $this->ip_table ORDER BY ID ASC");
        if (!empty($ips)) {
            foreach ($ips as $ip) {
                echo "<h1>IP Address: " . esc_html($ip->ip_address) . "</h1><br>";
            }
        } else {
            echo "No IP records found.";
        }
    }

    public function render_post_logs_section() {
        $posts = $this->wpdb->get_results("SELECT * FROM $this->posts_table ORDER BY ID ASC");
        $users = $this->wpdb->get_results("SELECT * FROM $this->users_table ORDER BY ID ASC");

        echo '<div class="wrap"><h1>Activity Log</h1>';
        echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">';
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
        echo '<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>';

        echo '<form method="post"><input type="submit" name="export_csv" class="button button-primary" value="Export to CSV" style="margin-bottom:10px;"></form>';
        echo '<table id="activityLogTable1" class="wp-list-table widefat fixed striped"><thead><tr>
            <th>Post Name</th><th>ID</th><th>Type</th><th>Title</th><th>Post Date</th><th>User Login</th><th>Email</th><th>Action</th></tr></thead><tbody>';

        foreach ($posts as $post) {
            $user_login = '';
            $user_email = '';
            foreach ($users as $user) {
                if ($user->ID == $post->post_author) {
                    $user_login = $user->user_login;
                    $user_email = $user->user_email;
                    break;
                }
            }

            $edit_url = admin_url('admin.php?page=edit-log&id=' . $post->ID);
            $delete_url = admin_url("admin.php?page=edit-log&action=delete&id={$post->ID}");

            echo "<tr>
                <td>{$post->post_name}</td>
                <td>{$post->ID}</td>
                <td>{$post->post_type}</td>
                <td>{$post->post_title}</td>
                <td>{$post->post_date}</td>
                <td>{$user_login}</td>
                <td>{$user_email}</td>
                <td><a href='{$edit_url}'>Edit</a> | <a href='{$delete_url}' onclick=\"return confirm('Delete this setting?');\">Delete</a></td>
            </tr>";
        }

        echo '</tbody></table>';
    }

    private function render_settings_logs_section() {
        $results = $this->wpdb->get_results("SELECT * FROM $this->options_table WHERE option_name NOT IN ('cron') ORDER BY option_id ASC");

        echo '<form method="post"><input type="submit" name="export_csv1" class="button button-primary" value="Export Settings to CSV"></form>';
        echo '<h1>Settings Log</h1>';
        echo '<table id="activityLogTable2" class="wp-list-table widefat fixed striped"><thead><tr>
            <th>ID</th><th>Option Name</th><th>Option Value</th><th>Action</th></tr></thead><tbody>';

        foreach ($results as $row) {
            $edit_url = admin_url("admin.php?page=edit-setting&option_id={$row->option_id}");
            $delete_url = admin_url("admin.php?page=edit-setting&action=delete&option_id={$row->option_id}");
            echo "<tr>
                <td>{$row->option_id}</td>
                <td>{$row->option_name}</td>
                <td>{$row->option_value}</td>
                <td><a href='{$edit_url}'>Edit</a> | <a href='{$delete_url}' onclick=\"return confirm('Delete this setting?');\">Delete</a></td>
            </tr>";
        }

        echo '</tbody></table>';
          echo '<script>
        jQuery(document).ready(function($) {
            $("#activityLogTable1").DataTable();
        });
    </script>';
    }

    private function render_user_requests_section() {
        $requests = $this->wpdb->get_results("SELECT ID, post_title, post_status, post_date FROM {$this->posts_table} WHERE post_type = 'user_request' ORDER BY post_date DESC");
        $site_health = $this->wpdb->get_results("SELECT * FROM {$this->options_table} WHERE option_name IN ('_transient_health-check-site-status-result') ORDER BY option_id ASC");

        echo '<h1>User Requests</h1><table id="activityLogTable3" class="wp-list-table widefat fixed striped"><thead>
            <tr><th>Request ID</th><th>User Email</th><th>Status</th><th>Requested At</th><th>Site Health</th><th>Value</th></tr></thead><tbody>';

        foreach ($requests as $req) {
            echo "<tr>
                <td>{$req->ID}</td>
                <td>{$req->post_title}</td>
                <td>{$req->post_status}</td>
                <td>{$req->post_date}</td>";

            if (!empty($site_health)) {
                foreach ($site_health as $sh) {
                    echo "<td>{$sh->option_name}</td><td>{$sh->option_value}</td>";
                }
            } else {
                echo "<td>-</td><td>-</td>";
            }
            echo "</tr>";
        }

        echo '</tbody></table>';
    }

    private function render_users_section() {
        $users = $this->wpdb->get_results("SELECT ID, user_login, user_email, user_registered FROM $this->users_table");

        if (!empty($users)) {
            echo '<h1>Users</h1><table class="wp-list-table widefat fixed striped"><thead><tr>
                <th>User ID</th><th>Username</th><th>Email</th><th>Registered</th><th>Last Login</th></tr></thead><tbody>';

            foreach ($users as $user) {
                echo "<tr>
                    <td>{$user->ID}</td>
                    <td>{$user->user_login}</td>
                    <td>{$user->user_email}</td>
                    <td>{$user->user_registered}</td>
                    <td>" . (isset($user->last_login) ? esc_html($user->last_login) : 'Not set') . "</td>
                </tr>";
            }

            echo '</tbody></table>';
        } else {
            echo 'No users found.';
        }
    }
}

