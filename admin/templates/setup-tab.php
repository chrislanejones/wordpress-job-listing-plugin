<?php defined('ABSPATH') || exit; ?>

<div class="setup-wizard-container">
    <h2><?php echo $setup_complete ? esc_html__('Modify Setup', 'job-listing-plugin') : esc_html__('Initial Setup Wizard', 'job-listing-plugin'); ?></h2>
    
    <p><?php esc_html_e('Please provide your Ashby organization ID and select when you would like the plugin to fetch job data.', 'job-listing-plugin'); ?></p>
    
    <div class="job-listing-setup-form">
        <div class="form-group">
            <label for="organization_id"><?php esc_html_e('Organization ID', 'job-listing-plugin'); ?></label>
            <input type="text" id="organization_id" name="organization_id" class="regular-text" 
                   value="<?php echo esc_attr($organization_id); ?>" 
                   placeholder="your-company" required>
            <p class="description">
                <?php esc_html_e('Enter your Ashby organization ID. You can find this in your Ashby job board URL.', 'job-listing-plugin'); ?>
            </p>
        </div>
        
        <div class="form-group schedule-times-group">
            <label><?php esc_html_e('Schedule Times', 'job-listing-plugin'); ?></label>
            <p class="description"><?php esc_html_e('Choose up to three times when the plugin should fetch job data daily. All times are in 24-hour format.', 'job-listing-plugin'); ?></p>
            
            <div class="schedule-times-container">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="schedule-time-wrapper">
                        <select name="schedule_time_<?php echo $i; ?>" id="schedule_time_<?php echo $i; ?>" <?php echo $i === 1 ? 'required' : ''; ?>>
                            <option value=""><?php echo $i === 1 ? esc_html__('Select Time', 'job-listing-plugin') : esc_html__('Select Time (Optional)', 'job-listing-plugin'); ?></option>
                            <?php echo $this->get_time_options($scheduled_times[$i-1] ?? ''); ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="submit-container">
            <div id="setup-response" class="setup-response"></div>
            <button type="button" id="save-setup-button" class="button button-primary">
                <?php echo $setup_complete ? esc_html__('Update Setup', 'job-listing-plugin') : esc_html__('Complete Setup', 'job-listing-plugin'); ?>
            </button>
        </div>
    </div>
</div>
