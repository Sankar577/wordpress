<?php
 // Exit if accessed directly

class ActivityLogEditor {
    private $wpdb;
    private $posts_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->posts_table = $wpdb->prefix . 'posts';

        $this->handle_actions();
    }

    private function handle_actions() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->delete_post(intval($_GET['id']));
            echo '<div class="notice notice-success is-dismissible"><p>Deleted successfully.</p></div>';
            return;
        }

        if (isset($_POST['update_post'])) {
            $this->update_post($_POST);
            echo '<div class="updated notice is-dismissible"><p>Post updated successfully!</p></div>';
        }

        $this->render_edit_form();
    }

    private function delete_post($id) {
        $this->wpdb->delete($this->posts_table, ['ID' => $id]);
    }

    private function update_post($data) {
        $post_id     = intval($data['post_id']);
        $post_title  = sanitize_text_field($data['post_title']);
        $post_name   = sanitize_title($data['post_name']);
        $post_type   = sanitize_text_field($data['post_type']);
        $post_date   = sanitize_text_field($data['post_date']);

        $this->wpdb->update(
            $this->posts_table,
            [
                'post_title' => $post_title,
                'post_name'  => $post_name,
                'post_type'  => $post_type,
                'post_date'  => $post_date,
            ],
            ['ID' => $post_id]
        );
    }

    private function render_edit_form() {
        $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$post_id) {
            echo "<div class='notice notice-error'><p>Invalid Post ID.</p></div>";
            return;
        }

        $post = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->posts_table} WHERE ID = %d", $post_id));

        if (!$post) {
            echo "<div class='notice notice-error'><p>Post not found.</p></div>";
            return;
        }

        ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <div class="wrap">
            <h2>Edit Post</h2>
            <form method="post">
                <input type="hidden" name="post_id" value="<?php echo esc_attr($post->ID); ?>">

                <div class="mb-3">
                    <label class="form-label">Post Title:</label>
                    <input type="text" name="post_title" class="form-control" value="<?php echo esc_attr($post->post_title); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Name (Slug):</label>
                    <input type="text" name="post_name" class="form-control" value="<?php echo esc_attr($post->post_name); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Type:</label>
                    <input type="text" name="post_type" class="form-control" value="<?php echo esc_attr($post->post_type); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Date:</label>
                    <input type="datetime-local" name="post_date" class="form-control" value="<?php echo esc_attr(date('Y-m-d\TH:i', strtotime($post->post_date))); ?>">
                </div>

                <input type="submit" name="update_post" value="Update Post" class="btn btn-primary">
            </form>

            <p class="mt-3">
                <a href="<?php echo admin_url('admin.php?page=activity-log'); ?>">← Back to List</a>
            </p>
        </div>
        <?php
    }
}
$log_editor = new ActivityLogEditor();
