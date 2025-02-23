<?php
// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// We need to access non-namespaced WordPress functions
use function \wp_clear_scheduled_hook;
use function \delete_option;
use function \delete_site_option;
use function \delete_transient;
use function \current_user_can;

// Check user capabilities
if (!current_user_can('activate_plugins')) {
    return;
}

// Global WordPress database object
global $wpdb;

// Plugin-specific constants to match main plugin file
$post_type = 'job_listing';
$table_name = $wpdb->prefix . 'job_listings';
$hook_name = 'job_listing_api_fetch';

// Delete all plugin options
delete_option('job_listing_settings');
delete_site_option('job_listing_settings'); // For multisite support

// Delete transients
delete_transient('job_listing_last_fetch');
delete_transient('job_listing_jobs');

// Remove all related options
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE %s",
        '%job_listing_%'
    )
);

// Remove all scheduled events for this plugin
$crons = _get_cron_array();
if (!empty($crons)) {
    foreach ($crons as $timestamp => $cron) {
        if (isset($cron[$hook_name])) {
            unset($crons[$timestamp][$hook_name]);
            if (empty($crons[$timestamp])) {
                unset($crons[$timestamp]);
            }
        }
    }
    _set_cron_array($crons);
}

// Clear any remaining cron jobs
wp_clear_scheduled_hook($hook_name);

// Drop custom database tables if they exist
if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name)) === $table_name) {
    $wpdb->query("DROP TABLE {$table_name}");
}
