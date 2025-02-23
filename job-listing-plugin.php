<?php
/**
 * Plugin Name: Job Listing Plugin
 * Plugin URI: 
 * Description: A comprehensive job listing plugin with Elementor integration
 * Version: 1.9.0
 * Author: Chris Lane Jones
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

namespace JobListingPlugin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('JobListingPlugin\JLP_VERSION', '1.9.0');
define('JobListingPlugin\JLP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JobListingPlugin\JLP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JobListingPlugin\JLP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('JobListingPlugin\JLP_MINIMUM_PHP_VERSION', '7.4');

// Explicitly require necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-job-listing-plugin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-job-listing-admin.php';

// Autoloader
spl_autoload_register(function ($class) {
    // Only autoload classes in our namespace
    $prefix = 'JobListingPlugin\\';
    $base_dir = plugin_dir_path(__FILE__) . 'includes/';
    
    // Check if the class uses our namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Remove namespace prefix and convert namespace to file path
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . str_replace(['_', '\\'], ['-', '/'], strtolower($relative_class)) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

// Main plugin initialization class
class Job_Listing_Plugin_Core {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_plugin']);
        register_activation_hook(JLP_PLUGIN_BASENAME, [$this, 'activate']);
        register_deactivation_hook(JLP_PLUGIN_BASENAME, [$this, 'deactivate']);
    }

    public function load_plugin() {
        // Initialize plugin
        try {
            $plugin = Job_Listing_Plugin::get_instance();
            $plugin->init();

            // Initialize admin if in admin area
            if (is_admin()) {
                $admin = Job_Listing_Admin::get_instance();
                $admin->init();
            }
        } catch (\Exception $e) {
            error_log('Job Listing Plugin Initialization Error: ' . $e->getMessage());
        }
    }

    public function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, JLP_MINIMUM_PHP_VERSION, '<')) {
            deactivate_plugins(JLP_PLUGIN_BASENAME);
            wp_die(
                sprintf(
                    __('Job Listing Plugin requires PHP version %s or higher.', 'job-listing-plugin'),
                    JLP_MINIMUM_PHP_VERSION
                )
            );
        }

        // Get plugin instance
        $plugin = Job_Listing_Plugin::get_instance();
        
        // Create database table
        $plugin->create_db_table();

        // Default schedule times if not set
        $settings = get_option('job_listing_settings', []);
        $schedule_times = isset($settings['schedule_times']) ? $settings['schedule_times'] : ['08:00', '16:00'];

        // Activate scheduler
        $plugin->activate_scheduler($schedule_times);

        // Initial data fetch
        $plugin->fetch_and_store_jobs();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Get plugin instance
        $plugin = Job_Listing_Plugin::get_instance();
        
        // Deactivate scheduler
        $plugin->deactivate_scheduler();

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function jlp_init() {
    return Job_Listing_Plugin_Core::instance();
}

// Start the plugin
jlp_init();
