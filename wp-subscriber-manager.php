<?php
/*
Plugin Name: WP Subscriber Manager
Description: Subscriber management with double opt-in, CSV import/export, cookie-based form display, and honeypot spam protection.
Version: 3.1
Author: jkurzner
Author URI: https://mindshaft.com
Plugin URI: https://github.com/jkurzner/wp-subscriber-manager
*/

// Define plugin version constant
define('WSM_VERSION', '3.1');

// Enable auto-updates from GitHub
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/YOUR-USERNAME/wp-subscriber-manager/', // CHANGE THIS to your GitHub username
    __FILE__,
    'wp-subscriber-manager'
);

// Optional: Set which branch to track (default is 'main' or 'master')
$myUpdateChecker->setBranch('main');

// Activation hook - creates database table
register_activation_hook(__FILE__, 'wsm_activate');
function wsm_activate() {
    global $wpdb;
    $table = $wpdb->prefix . 'custom_subscribers';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(200) NOT NULL,
        email varchar(200) NOT NULL,
        confirmed tinyint(1) DEFAULT 0,
        token varchar(100),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Handle confirmation links
add_action('wp', function() {
    if (isset($_GET['wsm_confirm'])) {
        global $wpdb;
        $table = $wpdb->prefix.'custom_subscribers';
        $token = sanitize_text_field($_GET['wsm_confirm']);
        
        $result = $wpdb->update($table, ['confirmed'=>1], ['token'=>$token]);
        
        if ($result) {
            // Set cookie on confirmation
            $subscriber = $wpdb->get_row($wpdb->prepare("SELECT email FROM $table WHERE token = %s", $token));
            if ($subscriber) {
                setcookie('wsm_subscribed', md5($subscriber->email), time() + (10 * 365 * 24 * 60 * 60), '/');
            }
            echo "<div style='padding:20px;font-size:20px;text-align:center;background:#e0ffe0;color:#060;'>✓ Subscription confirmed. Thank you!</div>";
        } else {
            echo "<div style='padding:20px;font-size:20px;text-align:center;background:#ffe0e0;color:#600;'>Invalid or expired confirmation link.</div>";
        }
        exit;
    }
});

// Admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Subscribers',
        'Subscribers',
        'manage_options',
        'wsm-subs',
        'wsm_admin_page',
        'dashicons-groups',
        30
    );
});

// Admin page content
function wsm_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'custom_subscribers';
    
    // Handle bulk actions
    if (isset($_POST['wsm_bulk_action']) && check_admin_referer('wsm_bulk_action', 'wsm_nonce')) {
        $action = sanitize_text_field($_POST['wsm_bulk_action']);
        $selected = isset($_POST['wsm_selected']) ? array_map('intval', $_POST['wsm_selected']) : [];
        
        if ($action === 'delete' && !empty($selected)) {
            $ids = implode(',', $selected);
            $wpdb->query("DELETE FROM $table WHERE id IN ($ids)");
            echo "<div class='notice notice-success'><p>Deleted " . count($selected) . " subscriber(s).</p></div>";
        }
    }
    
    // Get all subscribers
    $subscribers = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    $total = count($subscribers);
    $confirmed = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE confirmed = 1");
    $pending = $total - $confirmed;
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Subscribers</h1>
        
        <div style="margin: 20px 0;">
            <div class="wsm-stats" style="display: flex; gap: 20px;">
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0;">Total Subscribers</h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold;"><?php echo $total; ?></p>
                </div>
                <div style="background: #e0ffe0; padding: 20px; border: 1px solid #0a0; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0;">Confirmed</h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold; color: #060;"><?php echo $confirmed; ?></p>
                </div>
                <div style="background: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 4px;">
                    <h3 style="margin: 0 0 10px 0;">Pending</h3>
                    <p style="font-size: 32px; margin: 0; font-weight: bold; color: #856404;"><?php echo $pending; ?></p>
                </div>
            </div>
        </div>
        
        <div style="margin: 20px 0; display: flex; gap: 10px;">
            <button id="wsm-export" class="button button-primary">Export CSV</button>
            <button id="wsm-import-btn" class="button">Import CSV</button>
            <input type="file" id="wsm-import-file" accept=".csv" style="display:none;">
        </div>
        
        <form method="post">
            <?php wp_nonce_field('wsm_bulk_action', 'wsm_nonce'); ?>
            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                <select name="wsm_bulk_action">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="button">Apply</button>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="wsm-select-all"></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date Subscribed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscribers)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                No subscribers yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td><input type="checkbox" name="wsm_selected[]" value="<?php echo $sub->id; ?>"></td>
                                <td><?php echo esc_html($sub->name); ?></td>
                                <td><?php echo esc_html($sub->email); ?></td>
                                <td>
                                    <?php if ($sub->confirmed): ?>
                                        <span style="color: #0a0; font-weight: bold;">✓ Confirmed</span>
                                    <?php else: ?>
                                        <span style="color: #f90;">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($sub->created_at)); ?></td>
                                <td>
                                    <button type="button" class="button button-small wsm-delete-single" data-id="<?php echo $sub->id; ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Select all checkbox
        $('#wsm-select-all').on('change', function() {
            $('input[name="wsm_selected[]"]').prop('checked', this.checked);
        });
        
        // Export CSV
        $('#wsm-export').on('click', function() {
            window.location.href = ajaxurl + '?action=wsm_export&_wpnonce=<?php echo wp_create_nonce('wsm_export'); ?>';
        });
        
        // Import CSV
        $('#wsm-import-btn').on('click', function() {
            $('#wsm-import-file').click();
        });
        
        $('#wsm-import-file').on('change', function() {
            if (this.files.length === 0) return;
            
            var formData = new FormData();
            formData.append('action', 'wsm_import');
            formData.append('file', this.files[0]);
            formData.append('_wpnonce', '<?php echo wp_create_nonce('wsm_import'); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Successfully imported ' + response.data.imported + ' subscriber(s). ' + 
                              (response.data.skipped > 0 ? response.data.skipped + ' duplicate(s) skipped.' : ''));
                        location.reload();
                    } else {
                        alert('Import failed: ' + response.data);
                    }
                },
                error: function() {
                    alert('Import failed. Please try again.');
                }
            });
        });
        
        // Delete single subscriber
        $('.wsm-delete-single').on('click', function() {
            if (!confirm('Are you sure you want to delete this subscriber?')) return;
            
            var id = $(this).data('id');
            var $row = $(this).closest('tr');
            
            $.post(ajaxurl, {
                action: 'wsm_delete',
                id: id,
                _wpnonce: '<?php echo wp_create_nonce('wsm_delete'); ?>'
            }, function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert('Delete failed. Please try again.');
                }
            });
        });
    });
    </script>
    <?php
}

// AJAX: Export CSV
add_action('wp_ajax_wsm_export', 'wsm_export');
function wsm_export() {
    check_ajax_referer('wsm_export');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'custom_subscribers';
    $subscribers = $wpdb->get_results("SELECT name, email, confirmed, created_at FROM $table ORDER BY created_at DESC", ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Email', 'Status', 'Date']);
    
    foreach ($subscribers as $sub) {
        fputcsv($output, [
            $sub['name'],
            $sub['email'],
            $sub['confirmed'] ? 'Confirmed' : 'Pending',
            $sub['created_at']
        ]);
    }
    
    fclose($output);
    exit;
}

// AJAX: Import CSV
add_action('wp_ajax_wsm_import', 'wsm_import');
function wsm_import() {
    check_ajax_referer('wsm_import');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('No file uploaded');
    }
    
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');
    
    if (!$handle) {
        wp_send_json_error('Could not read file');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'custom_subscribers';
    $imported = 0;
    $skipped = 0;
    
    // Skip header row
    fgetcsv($handle);
    
    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) < 2) continue;
        
        $name = sanitize_text_field($data[0]);
        $email = sanitize_email($data[1]);
        
        if (!is_email($email)) continue;
        
        // Check if email already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s", $email));
        
        if ($exists) {
            $skipped++;
            continue;
        }
        
        $token = wp_generate_uuid4();
        $wpdb->insert($table, [
            'name' => $name,
            'email' => $email,
            'token' => $token,
            'confirmed' => 1 // Auto-confirm imports
        ]);
        
        $imported++;
    }
    
    fclose($handle);
    
    wp_send_json_success([
        'imported' => $imported,
        'skipped' => $skipped
    ]);
}

// AJAX: Delete single subscriber
add_action('wp_ajax_wsm_delete', 'wsm_delete');
function wsm_delete() {
    check_ajax_referer('wsm_delete');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $id = intval($_POST['id']);
    
    global $wpdb;
    $table = $wpdb->prefix . 'custom_subscribers';
    $result = $wpdb->delete($table, ['id' => $id]);
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Delete failed');
    }
}

// Handle subscription form submission
add_action('init', function() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wsm_email'])) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_subscribers';
        
        // HONEYPOT CHECK - if filled, it's a bot
        if (!empty($_POST['wsm_website'])) {
            // Silent fail - don't tell bots they were caught
            set_transient('wsm_message', ['type' => 'success', 'text' => 'Please check your email to confirm your subscription.'], 30);
            return;
        }
        
        $name = sanitize_text_field($_POST['wsm_name']);
        $email = sanitize_email($_POST['wsm_email']);
        
        if (!is_email($email)) {
            set_transient('wsm_message', ['type' => 'error', 'text' => 'Please enter a valid email address.'], 30);
            return;
        }
        
        // Check if already subscribed
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email));
        
        if ($existing) {
            if ($existing->confirmed) {
                set_transient('wsm_message', ['type' => 'info', 'text' => 'This email is already subscribed.'], 30);
                // Set cookie for already subscribed users
                setcookie('wsm_subscribed', md5($email), time() + (10 * 365 * 24 * 60 * 60), '/');
            } else {
                set_transient('wsm_message', ['type' => 'info', 'text' => 'A confirmation email was already sent. Please check your inbox.'], 30);
            }
            return;
        }
        
        // Insert new subscriber
        $token = wp_generate_uuid4();
        $result = $wpdb->insert($table, [
            'name' => $name,
            'email' => $email,
            'token' => $token
        ]);
        
        if ($result) {
            // Send confirmation email
            $confirm_link = home_url('?wsm_confirm=' . $token);
            $subject = 'Please confirm your subscription';
            $message = "Hi " . esc_html($name) . ",\n\n";
            $message .= "Please click the link below to confirm your subscription:\n\n";
            $message .= $confirm_link . "\n\n";
            $message .= "If you didn't request this, please ignore this email.";
            
            wp_mail($email, $subject, $message);
            
            set_transient('wsm_message', ['type' => 'success', 'text' => 'Please check your email to confirm your subscription.'], 30);
        } else {
            set_transient('wsm_message', ['type' => 'error', 'text' => 'Something went wrong. Please try again.'], 30);
        }
    }
});

// Add styles for messages and honeypot
add_action('wp_head', function() {
    ?>
    <style>
    .wsm-message {
        padding: 15px 20px;
        margin: 20px 0;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1.5;
    }
    .wsm-message.success {
        background: #e0ffe0;
        border: 1px solid #0a0;
        color: #060;
    }
    .wsm-message.error {
        background: #ffe0e0;
        border: 1px solid #a00;
        color: #600;
    }
    .wsm-message.info {
        background: #e0f0ff;
        border: 1px solid #07c;
        color: #036;
    }
    .wsm-form {
        max-width: 500px;
        margin: 20px 0;
    }
    .wsm-form input[type="text"],
    .wsm-form input[type="email"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 16px;
    }
    .wsm-form button {
        background: #0073aa;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }
    .wsm-form button:hover {
        background: #005a87;
    }
    /* Honeypot field - hidden from humans, visible to bots */
    .wsm-honeypot {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }
    </style>
    <?php
});

// Shortcode for subscription form
add_shortcode('wsm_subscribe_form', 'wsm_subscribe_form_shortcode');
function wsm_subscribe_form_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Subscribe to Our Newsletter',
        'hide_for_subscribers' => 'yes'
    ], $atts);
    
    // Check if user already subscribed via cookie
    if ($atts['hide_for_subscribers'] === 'yes' && isset($_COOKIE['wsm_subscribed'])) {
        return '<div class="wsm-message info">You are already subscribed. Thank you!</div>';
    }
    
    ob_start();
    
    // Display message if exists
    $message = get_transient('wsm_message');
    if ($message) {
        delete_transient('wsm_message');
        echo '<div class="wsm-message ' . esc_attr($message['type']) . '">' . esc_html($message['text']) . '</div>';
        
        // If successful subscription, hide form
        if ($message['type'] === 'success') {
            return ob_get_clean();
        }
    }
    
    ?>
    <div class="wsm-form">
        <?php if (!empty($atts['title'])): ?>
            <h3><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>
        
        <form method="post">
            <input type="text" name="wsm_name" placeholder="Your Name" required>
            <input type="email" name="wsm_email" placeholder="Your Email" required>
            
            <!-- Honeypot field - invisible to humans, bots will fill it -->
            <div class="wsm-honeypot">
                <label for="wsm_website">Website (leave blank)</label>
                <input type="text" name="wsm_website" id="wsm_website" tabindex="-1" autocomplete="off">
            </div>
            
            <button type="submit">Subscribe</button>
        </form>
    </div>
    <?php
    
    return ob_get_clean();
}

?>