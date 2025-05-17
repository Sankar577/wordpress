<?php
if (!defined('ABSPATH')) exit;

class SettingEditor {
    private $wpdb;
    private $option_table;
    private $option_id;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->option_table = $wpdb->prefix . 'options';
        $this->option_id = isset($_GET['option_id']) ? intval($_GET['option_id']) : 0;

        $this->handle_actions();
    }

    private function handle_actions() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && $this->option_id) {
            $this->delete_option($this->option_id);
            echo '<div class="notice notice-success is-dismissible"><p>Option deleted successfully.</p></div>';
            return;
        }

        if (!$this->option_id) {
            echo "<div class='notice notice-error'><p>Invalid Option ID.</p></div>";
            return;
        }

        if (isset($_POST['submit'])) {
            $this->update_option();
        }

        $this->render_form();
    }

    private function delete_option($id) {
        $this->wpdb->delete($this->option_table, ['option_id' => $id]);
    }

    private function update_option() {
        check_admin_referer('update_option');

        $new_value = sanitize_text_field($_POST['option_value']);
        $this->wpdb->update(
            $this->option_table,
            ['option_value' => $new_value],
            ['option_id' => $this->option_id]
        );

        echo "<div class='notice notice-success'><p>Option updated successfully.</p></div>";
    }

    private function render_form() {
        $option = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->option_table} WHERE option_id = %d", $this->option_id)
        );

        if (!$option) {
            echo "<div class='notice notice-error'><p>Option not found.</p></div>";
            return;
        }

        ?>
        <div class="wrap">
            <h1>Edit Option: <?php echo esc_html($option->option_name); ?></h1>
            <form method="post">
                <?php wp_nonce_field('update_option'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label>Option Name</label></th>
                        <td>
                            <input type="text" name="option_name" value="<?php echo esc_attr($option->option_name); ?>" readonly class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Option Value</label></th>
                        <td>
                            <textarea name="option_value" class="large-text" rows="5"><?php echo esc_textarea($option->option_value); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Update Option'); ?>
            </form>
        </div>
        <?php
    }
}
