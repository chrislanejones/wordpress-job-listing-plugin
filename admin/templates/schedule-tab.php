<?php defined('ABSPATH') || exit; ?>

<div class="schedule-info-wrapper">
    <h2><?php esc_html_e('Scheduled Job Data Fetches', 'job-listing-plugin'); ?></h2>
    
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Last Fetch', 'job-listing-plugin'); ?></th>
                <th><?php esc_html_e('Schedule Times (Daily)', 'job-listing-plugin'); ?></th>
                <th><?php esc_html_e('Actions', 'job-listing-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td id="last-fetch-time">
                    <?php 
                    echo isset($fetch_info['last_fetch']) 
                        ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($fetch_info['last_fetch']))) 
                        : esc_html__('Never', 'job-listing-plugin');
                    ?>
                </td>
                <td>
                    <?php if (!empty($fetch_info['scheduled_times'])): ?>
                        <ul class="schedule-times-list">
                            <?php foreach ($fetch_info['scheduled_times'] as $time): ?>
                                <li><?php echo esc_html(date_i18n('g:i A', strtotime($time))); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php esc_html_e('No scheduled times. Please complete setup.', 'job-listing-plugin'); ?></p>
                    <?php endif; ?>
                </td>
                <td>
                    <button type="button" id="refresh-jobs-button" class="button">
                        <?php esc_html_e('Fetch Jobs Now', 'job-listing-plugin'); ?>
                    </button>
                    <span id="refresh-status"></span>
                </td>
            </tr>
        </tbody>
    </table>
</div>