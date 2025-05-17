<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AdminActivityLogger {
    private $log_table;
    private $ip_table;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->log_table = $wpdb->prefix . 'admin_activity_log';
        $this->ip_table  = $wpdb->prefix . 'ip_address';

        add_action('init', [$this, 'maybe_create_ip_table']);
        add_action('init', [$this, 'track_ip_address']);

        add_action('post_updated', [$this, 'log_post_update'], 10, 3);
        add_action('wp_login',    [$this, 'log_user_login'], 10, 2);
    }

    public function log_activity($action, $author = '', $description = '') {
        $user_id = get_current_user_id();
        $ip_address = $this->get_ip_address();

        $this->wpdb->insert(
            $this->log_table,
            [
                'user_id'     => $user_id,
                'action'      => $action,
                'ip_address'  => $ip_address,
                'author'      => $author,
                'description' => $description
            ]
        );
    }

    public function log_post_update($post_ID, $post_after, $post_before) {
        $author = get_the_author_meta('display_name', $post_after->post_author);
        $description = "Updated post titled '{$post_after->post_title}'";
        $this->log_activity("Updated post ID: $post_ID", $author, $description);
    }

    public function log_user_login($user_login, $user) {
        $author = $user->display_name;
        $description = "User '$user_login' successfully logged in.";
        $this->log_activity("User logged in: $user_login", $author, $description);
    }

    public function maybe_create_ip_table() {
        $this->wpdb->query("CREATE TABLE IF NOT EXISTS {$this->ip_table} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip_address VARBINARY(16) NOT NULL,
            visit_time DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function track_ip_address() {
        $ip = $this->get_ip_address();

        $exists = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->ip_table} WHERE ip_address = %s",
            $ip
        ));

        if (!$exists) {
            $this->wpdb->insert($this->ip_table, ['ip_address' => $ip]);
        }
    }

    public function get_ip_address() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return ($ip === '::1') ? '127.0.0.1' : $ip;
    }
}

new AdminActivityLogger();
