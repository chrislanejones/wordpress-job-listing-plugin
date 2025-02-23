(function () {
  tinymce.create("tinymce.plugins.JobListingShortcode", {
    init: function (ed, url) {
      ed.addButton("job_listing_shortcode", {
        title: "Insert Job Listing",
        cmd: "job_listing_shortcode",
        image: url + "/../images/job-icon.png",
      });

      ed.addCommand("job_listing_shortcode", function () {
        ed.insertContent(
          '[job_listing jobs_per_page="10" show_department="yes" show_location="yes"]'
        );
      });
    },
  });

  tinymce.PluginManager.add(
    "job_listing_shortcode",
    tinymce.plugins.JobListingShortcode
  );
})();
