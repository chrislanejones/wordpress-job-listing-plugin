<?php
namespace JobListingPlugin;

class Job_Listing_Admin {
    private static $instance = null;
    private $plugin;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin = Job_Listing_Plugin::get_instance();
    }

    public function init() {
        // Admin menu and page setup
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Admin AJAX actions
        add_action('wp_ajax_job_listing_save_setup', [$this, 'save_setup_ajax']);
        add_action('wp_ajax_job_listing_refresh_jobs', [$this, 'refresh_jobs_ajax']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Job Listings', 
            'Job Listings', 
            'manage_options', 
            'job-listing-plugin', 
            [$this, 'render_admin_page'], 
            'dashicons-list-view', 
            30
        );
    }

    public function render_admin_page() {
        // Get last fetch info
        $last_fetch_info = $this->plugin->get_last_fetch_info();
        $schedule_times = [];
        
        // Extract hours from scheduled times
        if (!empty($last_fetch_info['scheduled_times'])) {
            foreach ($last_fetch_info['scheduled_times'] as $time) {
                $hour = date('H', strtotime($time));
                $schedule_times[] = $hour;
            }
        }
        
        // If we don't have 5 times yet, add defaults
        if (count($schedule_times) < 5) {
            $defaults = ['02', '08', '14', '20', '23'];
            foreach ($defaults as $default) {
                if (!in_array($default, $schedule_times) && count($schedule_times) < 5) {
                    $schedule_times[] = $default;
                }
            }
        }
        
        // Ensure we have exactly 5 times
        $schedule_times = array_slice($schedule_times, 0, 5);
        
        // Get WordPress timezone
        $timezone = wp_timezone();
        $timezone_string = $timezone->getName();
        
        ?>
        <div class="wrap">
            <h1>Job Listing Plugin</h1>

            <!-- Status Messages Container -->
            <div id="job-listing-admin-messages"></div>

            <div id="job-listing-setup">
                <form id="job-listing-setup-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="organization-id">Organization ID</label>
                            </th>
                            <td>
                                <input 
                                    type="text" 
                                    id="organization-id" 
                                    name="organization_id" 
                                    value="<?php echo esc_attr($last_fetch_info['organization_id'] ?? ''); ?>" 
                                    class="regular-text"
                                    placeholder="Enter Ashby Organization ID"
                                >
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label>Fetch Schedule Times</label>
                            </th>
                            <td>
                                <div class="schedule-hours-selector">
                                    <p class="description">Select exactly five hours when jobs should be fetched daily (24-hour format).</p>
                                    <br>
                                    <div class="hour-buttons">
                                        <?php for ($i = 0; $i < 24; $i++): 
                                            $hour = sprintf('%02d', $i);
                                            $isSelected = in_array($hour, $schedule_times);
                                        ?>
                                            <button 
                                                type="button" 
                                                class="hour-button <?php echo $isSelected ? 'selected' : ''; ?>"
                                                data-hour="<?php echo $hour; ?>"
                                            >
                                                <?php echo $hour; ?>:00
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="selected-hours-container">
                                    <strong>Selected hours:</strong>
                                        <span id="selected-hours-display">
                                            <?php echo implode(':00, ', $schedule_times) . ':00'; ?>
                                        </span>
                                        <div id="schedule-error" class="schedule-error"></div>
                                    </div>
                                    <?php foreach ($schedule_times as $time): ?>
                                        <input type="hidden" name="schedule_times[]" value="<?php echo $time; ?>:00" class="selected-hour-input">
                                    <?php endforeach; ?>
                                    </div>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
                        <button type="button" id="refresh-jobs" class="button button-secondary">Refresh Jobs</button>
                    </p>
                </form>

                <div id="last-fetch-info">
                    <h2>Last Fetch Information</h2>
                    <p>
                        <strong>Last Fetch:</strong> 
                        <?php 
                        echo $last_fetch_info['last_fetch'] 
                            ? esc_html($last_fetch_info['last_fetch']) 
                            : 'No fetch performed yet'; 
                        ?>
                    </p>
                    <p>
                        <strong>Scheduled Fetch Times:</strong>
                        <?php 
                        echo !empty($last_fetch_info['scheduled_times']) 
                            ? esc_html(implode(', ', array_map(function($time) {
                                return date('H:i', strtotime($time));
                              }, $last_fetch_info['scheduled_times'])))
                            : 'No fetch scheduled'; 
                        ?>
					</p>
					<p>
						                    <h2>Timezone Fetch Information</h2>
                    <strong>Current Server Time Zone:</strong> <?php echo esc_html($timezone_string); ?><br>
                    <strong>Current Server Time:</strong> <?php echo date('g:i A', current_time('timestamp')); ?>
                </p>
                </div>
            </div>

            <div class="database-info-wrapper">
                <h2>Database Information</h2>
                <?php $this->render_database_info(); ?>
            </div>
        </div>
        <?php
    }

    // Add this new method to the class
    private function render_database_info() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'job_listings';
        $job_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $last_updated = $wpdb->get_var("SELECT MAX(date_updated) FROM $table_name");
        
        ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Total Jobs in Database</th>
                    <th>Database Table Name</th>
                    <th>Last Updated</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo intval($job_count); ?></td>
                    <td><?php echo esc_html($table_name); ?></td>
                    <td>
                        <?php 
                        if ($last_updated) {
                            $date = new \DateTime($last_updated, wp_timezone());
                            echo $date->format('Y-m-d g:i A');
                        } else {
                            echo 'Never';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name) {
                            echo '<span class="status-success">Active</span>';
                        } else {
                            echo '<span class="status-error">Table Missing</span>';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public function enqueue_admin_scripts($hook) {
        // Only enqueue on our plugin page
        if ($hook !== 'toplevel_page_job-listing-plugin') {
            return;
        }

        wp_enqueue_script(
            'job-listing-admin',
            JLP_PLUGIN_URL . 'assets/js/admin.js',
            [],
            JLP_VERSION,
            true
        );

        wp_localize_script('job-listing-admin', 'jobListingAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('job_listing_admin_nonce')
        ]);

        wp_enqueue_style(
            'job-listing-admin',
            JLP_PLUGIN_URL . 'assets/css/admin.css',
            [],
            JLP_VERSION
        );
    }

    public function save_setup_ajax() {
        // Verify nonce
        check_ajax_referer('job_listing_admin_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions', 403);
        }

        // Sanitize and validate inputs
        $organization_id = sanitize_text_field($_POST['organization_id'] ?? '');
        $schedule_times = array_map('sanitize_text_field', $_POST['schedule_times'] ?? []);

        // Remove empty schedule times
        $schedule_times = array_filter($schedule_times);

        // Save setup
        $result = $this->plugin->save_setup($organization_id, $schedule_times);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 400);
        }

        wp_send_json_success($result);
    }

    public function refresh_jobs_ajax() {
        // Verify nonce
        check_ajax_referer('job_listing_admin_nonce', 'nonce');

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions', 403);
        }

        // Trigger job refresh
        $result = $this->plugin->refresh_jobs_data();

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 400);
        }

        wp_send_json_success($result);
    }
}

