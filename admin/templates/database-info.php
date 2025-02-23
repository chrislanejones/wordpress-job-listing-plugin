<?php defined('ABSPATH') || exit; ?>

<table class="widefat">
    <thead>
        <tr>
            <th><?php esc_html_e('Total Jobs in Database', 'job-listing-plugin'); ?></th>
            <th><?php esc_html_e('Table Name', 'job-listing-plugin'); ?></th>
            <th><?php esc_html_e('Last Updated', 'job-listing-plugin'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo intval($job_count); ?></td>
            <td><?php echo esc_html($table_name); ?></td>
            <td>
                <?php 
                $last_updated = $wpdb->get_var("SELECT MAX(date_updated) FROM $table_name");
                echo $last_updated 
                    ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_updated))) 
                    : esc_html__('Never', 'job-listing-plugin');
                ?>
            </td>
        </tr>
    </tbody>
</table>