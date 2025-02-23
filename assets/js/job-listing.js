class JobListing {
  constructor(container) {
    this.container = container;
    this.jobsList = container.querySelector(".jobs-list");
    this.loading = container.querySelector(".loading");
    this.settings = {
      showDepartment: container.dataset.showDepartment === "yes",
      showLocation: container.dataset.showLocation === "yes",
      jobsPerPage: container.dataset.jobsPerPage || 10,
    };

    this.init();
  }

  init() {
    this.loadJobs();
    this.bindEvents();
  }

  bindEvents() {
    window.addEventListener("resize", this.handleResize.bind(this));
  }

  handleResize() {
    // Handle responsive behavior if needed
  }

  async loadJobs() {
    try {
      const response = await fetch(jobListingData.ajaxUrl, {
        method: "GET",
        headers: {
          "X-WP-Nonce": jobListingData.nonce,
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      this.loading.style.display = "none";

      if (data && data.jobs && Array.isArray(data.jobs)) {
        this.renderJobsByDepartment(data.jobs);
      } else {
        this.showError("No jobs found");
      }
    } catch (error) {
      console.error("Error loading jobs:", error);
      this.showError("Error loading jobs");
    }
  }

  renderJobsByDepartment(jobs) {
    this.jobsList.innerHTML = "";

    if (jobs.length === 0) {
      this.showMessage("No open positions at this time.");
      return;
    }

    // Group jobs by department
    const jobsByDepartment = {};
    jobs.forEach((job) => {
      const department = job.department || "Other";
      if (!jobsByDepartment[department]) {
        jobsByDepartment[department] = [];
      }
      jobsByDepartment[department].push(job);
    });

    // Sort departments alphabetically
    const sortedDepartments = Object.keys(jobsByDepartment).sort();

    // Render each department section
    sortedDepartments.forEach((department) => {
      const departmentJobs = jobsByDepartment[department];

      // Create department header
      const departmentSection = document.createElement("div");
      departmentSection.className = "department-section";
      departmentSection.innerHTML = `
        <h2 class="department-heading">${this.escapeHtml(department)}</h2>
        <div class="department-jobs"></div>
      `;

      const jobsContainer = departmentSection.querySelector(".department-jobs");

      // Render jobs in this department
      departmentJobs.forEach((job, index) => {
        const jobElement = this.createJobElement(job);
        jobsContainer.insertAdjacentHTML("beforeend", jobElement);
      });

      this.jobsList.appendChild(departmentSection);
    });

    // Add fade-in animation
    const jobItems = this.jobsList.querySelectorAll(".job-item");
    jobItems.forEach((element, index) => {
      setTimeout(() => {
        element.classList.add("fade-in");
      }, index * 100);
    });
  }

  createJobElement(job) {
    let jobHtml = `
      <div class="job-item">
        <div class="job-content">
          <div class="job-header">
            <h3>${this.escapeHtml(job.title)}</h3>
          </div>
          <div class="job-details">`;

    // if (job.team) {
    //   jobHtml += `
    //     <div class="job-detail">
    //       <i class="fas fa-users"></i>
    //       ${this.escapeHtml(job.team)}
    //     </div>`;
    // }

    if (job.location) {
      jobHtml += `
        <div class="job-detail">
          <i class="fas fa-map-marker-alt"></i>
          ${this.escapeHtml(job.location)}
        </div>`;
    }

    if (job.employmentType) {
      jobHtml += `
        <div class="job-detail">
          <i class="fas fa-clock"></i>
          ${this.escapeHtml(job.employmentType)}
        </div>`;
    }

    // Add Remote status with icon
    jobHtml += `
      <div class="job-detail">
        <i class="fas fa-laptop-house"></i>
        <span class="remote-status ${
          job.isRemote ? "is-remote" : "not-remote"
        }">
          ${job.isRemote ? "Yes" : "On Site"}
        </span>
      </div>`;

    jobHtml += `
          </div>
        </div>
        <div class="job-action">
          <a href="${this.escapeHtml(job.applicationUrl)}" 
             class="job-apply-button" 
             target="_blank"
             rel="noopener noreferrer">
            Apply Now
          </a>
        </div>
      </div>`;

    return jobHtml;
  }

  showError(message) {
    this.loading.style.display = "none";
    this.jobsList.innerHTML = `
      <div class="error-message">
        ${this.escapeHtml(message)}
      </div>
    `;
  }

  showMessage(message) {
    this.jobsList.innerHTML = `
      <div class="info-message">
        ${this.escapeHtml(message)}
      </div>
    `;
  }

  escapeHtml(unsafe) {
    if (!unsafe) return "";
    const div = document.createElement("div");
    div.textContent = unsafe;
    return div.innerHTML;
  }
}

// Initialize on document ready
document.addEventListener("DOMContentLoaded", () => {
  const containers = document.querySelectorAll(".job-listing-container");
  containers.forEach((container) => new JobListing(container));
});
