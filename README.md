# Job Listing Plugin - Shortcode Documentation

This is the Shortcode addition, I also made an [Elementor Edition](https://github.com/chrislanejones/job-listing-plugin)

## Overview

The Job Listing Plugin now includes a shortcode implementation that allows you to display job listings anywhere on your WordPress site without requiring Elementor.

## Basic Usage

Simply add the following shortcode to any page or post:

```
[job_listing]
```

This will display job listings with default settings.

## Customization Options

You can customize the job listing display with the following attributes:

### Available Attributes

| Attribute             | Description                         | Default | Options             |
| --------------------- | ----------------------------------- | ------- | ------------------- |
| `jobs_per_page`       | Number of jobs to display per page  | `10`    | Any positive number |
| `group_by_department` | Whether to group jobs by department | `yes`   | `yes` or `no`       |
| `show_location`       | Whether to display job locations    | `yes`   | `yes` or `no`       |

### Example with Custom Attributes

```
[job_listing jobs_per_page="20" group_by_department="no" show_location="yes"]
```

This example will:

- Display 20 jobs per page
- List all jobs without grouping them by department
- Show the location for each job

## Styling

The job listings use the same styling as before. If you want to customize the appearance, you can add custom CSS to your theme or use a custom CSS plugin.

## Installation & Setup

1. Replace the Elementor widget implementation with the shortcode implementation in `class-job-listing-plugin.php`
2. Make sure you've completed the initial plugin setup in the admin panel (Organization ID and scheduling)
3. Add the shortcode to any page or post
4. The plugin will automatically fetch and display the latest job listings

## Troubleshooting

- If jobs don't appear, check the admin dashboard to ensure job data has been fetched successfully
- Verify your Ashby organization ID is correct
- Check that scheduled fetches are running properly
- You can manually trigger a job data refresh from the admin panel

## Support

For additional support or questions, please contact the plugin developer.
