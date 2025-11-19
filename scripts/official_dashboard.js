/* ====================================================================
   USER MANAGEMENT
   ==================================================================== */

// Open "Add User" modal and reset the form
function addResident() {
  document.getElementById("residentForm").reset();
  document.getElementById("user_id").value = "";
  document.getElementById("residentModalTitle").textContent = "Add New User";
  document.getElementById('submitBtn').textContent = 'Submit';

  // Enable email field and reset styles
  const emailField = document.getElementById("email");
  emailField.disabled = false;
  emailField.style.backgroundColor = "";

  // Show and require password field
  document.getElementById("passwordField").style.display = "block";
  document.getElementById("password").required = true;

  // Hide email change note
  document.getElementById("emailNote").style.display = "none";

  openModal("residentModal");
}

// Open "Edit User" modal and populate with user data
function editUser(id) {
  document.getElementById("residentForm").reset();
  document.getElementById("residentModalTitle").textContent = "Edit User";
  document.getElementById('submitBtn').textContent = 'Update User';

  // Disable email field and show note
  const emailField = document.getElementById("email");
  emailField.disabled = true;
  emailField.style.backgroundColor = "#f5f5f5";
  document.getElementById("emailNote").style.display = "block";

  // Hide and un-require password field
  document.getElementById("passwordField").style.display = "none";
  document.getElementById("password").required = false;

  // Fetch user data
  fetch("get_user.php?id=" + encodeURIComponent(id))
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        showMessage('Error', "Failed to load user: " + data.message);
        return;
      }
      const user = data.user;
      document.getElementById("user_id").value = user.id;
      document.getElementById("first_name").value = user.first_name || "";
      document.getElementById("last_name").value = user.last_name || "";
      document.getElementById("gender").value = user.gender || "";
      document.getElementById("birth_date").value = user.birth_date || "";
      document.getElementById("email").value = user.email || "";
      document.getElementById("role").value = user.role || "";
      document.getElementById("account_status").value = user.account_status || "";
      openModal("residentModal");
    })
    .catch(err => showMessage('Error', "Error loading user data: " + err));
}

// Handle submission for both adding and editing users
document.addEventListener('DOMContentLoaded', () => {
  const residentForm = document.getElementById("residentForm");
  if (residentForm) {
    residentForm.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      // The action is the same for add and update, the PHP script differentiates by user_id
      const action = 'add_resident.php'; 

      fetch(action, {
        method: "POST",
        body: formData
      })
      .then(res => res.json()) // Expect a JSON response
      .then(data => {
        if (data.status === 'success') {
          showMessage('Success', data.message);
          setTimeout(() => location.reload(), 1500);
        } else {
          showMessage('Error', data.message || 'An unknown error occurred.');
        }
      })
      .catch(err => showMessage('Error', "A network or server error occurred: " + err));
    });
  }
});

// Update a user's account status (e.g., approve)
function updateStatus(id, status) {
  if (confirm(`Are you sure you want to ${status} this user?`)) {
    fetch('update_user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showMessage('Success', data.message);
        setTimeout(() => location.reload(), 1000);
      } else {
        showMessage('Error', data.message || 'Failed to update user.');
      }
    })
    .catch(err => showMessage('Error', 'Failed to update user.'));
  }
}

// Delete a single user
function deleteUser(id) {
  if (confirm("Are you sure you want to delete this user?")) {
    fetch('delete_user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        showMessage('Success', data.message);
        setTimeout(() => location.reload(), 1000);
      } else {
        showMessage('Error', data.message || 'Failed to delete user.');
      }
    })
    .catch(err => showMessage('Error', 'Failed to delete user.'));
  }
}


/* ====================================================================
   MODAL AND MESSAGING
   ==================================================================== */

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = 'flex';
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = 'none';
}

function showMessage(status, text) {
  const modal = document.getElementById('messageModal');
  const title = document.getElementById('messageTitle');
  const messageText = document.getElementById('messageText');

  modal.classList.remove('message-success', 'message-error');
  if (status.toLowerCase() === 'success') {
    modal.classList.add('message-success');
    title.innerText = "✅ Success";
  } else {
    modal.classList.add('message-error');
    title.innerText = "❌ Error";
  }

  messageText.innerText = text;
  openModal('messageModal');
}

window.onclick = function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
};


/* ====================================================================
   REQUEST MANAGEMENT
   ==================================================================== */
let currentRequestPage = 1;
const itemsPerPageRequests = 10;
let allRequests = [];

function loadRequests() {
  const rows = Array.from(document.querySelectorAll('#requestsTable tbody tr'));
  allRequests = rows.map(row => ({
    element: row,
    status: row.dataset.status,
    type: row.dataset.type
  }));
  renderRequestPage(1);
}

function filterRequests() {
  const statusFilter = document.getElementById('requestStatusFilter').value;
  const typeFilter = document.getElementById('requestTypeFilter').value;

  allRequests.forEach(request => {
    const statusMatch = !statusFilter || request.status === statusFilter;
    const typeMatch = !typeFilter || request.type === typeFilter;
    request.element.style.display = (statusMatch && typeMatch) ? '' : 'none';
  });

  currentRequestPage = 1;
  renderRequestPage(currentRequestPage);
}

function renderRequestPage(page) {
  const start = (page - 1) * itemsPerPageRequests;
  const end = start + itemsPerPageRequests;
  let visibleCount = 0;

  allRequests.forEach((request) => {
    if (request.element.style.display !== 'none') {
      request.element.style.display = (visibleCount >= start && visibleCount < end) ? '' : 'none';
      visibleCount++;
    }
  });

  const totalPages = Math.ceil(visibleCount / itemsPerPageRequests);
  document.getElementById('requestPageInfo').textContent = `Page ${page} of ${totalPages}`;
  document.getElementById('requestPrevPage').disabled = page === 1;
  document.getElementById('requestNextPage').disabled = page === totalPages;
}

function changeRequestPage(direction) {
  currentRequestPage += direction;
  renderRequestPage(currentRequestPage);
}

function openScheduleModal(r_id, status) {
  document.getElementById('schedule_r_id').value = r_id;
  document.getElementById('scheduleForm').reset();
  openModal('scheduleModal');
}

function updateRequestStatus(r_id, status) {
  if (confirm("Are you sure you want to " + status + " this request?")) {
    fetch('update_request_status.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `r_id=${encodeURIComponent(r_id)}&status=${encodeURIComponent(status)}`
    })
    .then(res => res.text())
    .then(data => {
      showMessage('Success', 'Request status updated successfully.');
      setTimeout(() => location.reload(), 1000);
    })
    .catch(err => showMessage('Error', 'Failed to update request.'));
  }
}

// Handle schedule form submission
document.addEventListener('DOMContentLoaded', function() {
  const scheduleForm = document.getElementById('scheduleForm');
  if (scheduleForm) {
    scheduleForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('status', 'approved');

      fetch('update_request_status.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.text())
      .then(data => {
        if (data.trim() === 'success') {
          showMessage('Success', 'Request approved and scheduled successfully.');
          closeModal('scheduleModal');
          setTimeout(() => location.reload(), 1000);
        } else {
          showMessage('Error', data);
        }
      })
      .catch(err => showMessage('Error', 'Failed to schedule request.'));
    });
  }
});


/* ====================================================================
   COMPLAINT MANAGEMENT
   ==================================================================== */
let currentComplaintPage = 1;
const itemsPerPageComplaints = 10;
let allComplaints = [];

function loadComplaints() {
  const rows = Array.from(document.querySelectorAll('#complaintsTable tbody tr'));
  allComplaints = rows.map(row => ({
    element: row,
    status: row.dataset.status,
    type: row.dataset.type
  }));
  renderComplaintPage(1);
}

function filterComplaints() {
  const statusFilter = document.getElementById('complaintStatusFilter').value;
  const typeFilter = document.getElementById('complaintTypeFilter').value;

  allComplaints.forEach(complaint => {
    const statusMatch = !statusFilter || complaint.status === statusFilter;
    const typeMatch = !typeFilter || complaint.type === typeFilter;
    complaint.element.style.display = (statusMatch && typeMatch) ? '' : 'none';
  });

  currentComplaintPage = 1;
  renderComplaintPage(currentComplaintPage);
}

function renderComplaintPage(page) {
  const start = (page - 1) * itemsPerPageComplaints;
  const end = start + itemsPerPageComplaints;
  let visibleCount = 0;

  allComplaints.forEach((complaint) => {
    if (complaint.element.style.display !== 'none') {
      complaint.element.style.display = (visibleCount >= start && visibleCount < end) ? '' : 'none';
      visibleCount++;
    }
  });

  const totalPages = Math.ceil(visibleCount / itemsPerPageComplaints);
  document.getElementById('complaintPageInfo').textContent = `Page ${page} of ${totalPages}`;
  document.getElementById('complaintPrevPage').disabled = page === 1;
  document.getElementById('complaintNextPage').disabled = page === totalPages;
}

function changeComplaintPage(direction) {
  currentComplaintPage += direction;
  renderComplaintPage(currentComplaintPage);
}

function updateComplaintStatus(c_id, newStatus) {
  if (!confirm(`Are you sure you want to mark this complaint as "${newStatus}"?`)) return;

  fetch('update_complaint_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `c_id=${encodeURIComponent(c_id)}&status=${encodeURIComponent(newStatus)}`
  })
  .then(res => res.text())
  .then(response => {
    showMessage('Success', response);
    setTimeout(() => location.reload(), 1000);
  })
  .catch(err => {
    showMessage('Error', 'Error updating complaint: ' + err);
  });
}


/* ====================================================================
   ANNOUNCEMENT MANAGEMENT
   ==================================================================== */
let currentAnnouncementPage = 1;
const itemsPerPageAnnouncements = 10;
let allAnnouncements = [];

function loadAnnouncements() {
  const rows = Array.from(document.querySelectorAll('#announcementsTable tbody tr'));
  allAnnouncements = rows.map(row => ({
    element: row,
    status: row.dataset.status
  }));
  renderAnnouncementPage(1);
}

function filterAnnouncements() {
  const statusFilter = document.getElementById('announcementStatusFilter').value;

  allAnnouncements.forEach(announcement => {
    const statusMatch = !statusFilter || announcement.status === statusFilter;
    announcement.element.style.display = statusMatch ? '' : 'none';
  });

  currentAnnouncementPage = 1;
  renderAnnouncementPage(currentAnnouncementPage);
}

function renderAnnouncementPage(page) {
  const start = (page - 1) * itemsPerPageAnnouncements;
  const end = start + itemsPerPageAnnouncements;
  let visibleCount = 0;

  allAnnouncements.forEach((announcement) => {
    if (announcement.element.style.display !== 'none') {
      announcement.element.style.display = (visibleCount >= start && visibleCount < end) ? '' : 'none';
      visibleCount++;
    }
  });

  const totalPages = Math.ceil(visibleCount / itemsPerPageAnnouncements);
  document.getElementById('announcementPageInfo').textContent = `Page ${page} of ${totalPages}`;
  document.getElementById('announcementPrevPage').disabled = page === 1;
  document.getElementById('announcementNextPage').disabled = page === totalPages;
}

function changeAnnouncementPage(direction) {
  currentAnnouncementPage += direction;
  renderAnnouncementPage(currentAnnouncementPage);
}

function openAddAnnouncementModal() {
  document.getElementById('announcementForm').reset();
  document.getElementById('ann_id').value = '';
  document.getElementById('announcementModalTitle').innerHTML = '<i class="fas fa-plus"></i> Create Announcement';
  document.getElementById('submitBtnText').textContent = 'Create Announcement';
  document.getElementById('imagePreview').style.display = 'none';
  document.getElementById('imageInfo').innerHTML = '<i class="fas fa-lightbulb"></i> Optional: Upload an image...';
  openModal('addAnnouncementModal');
}

function editAnnouncement(ann_id) {
  document.getElementById('announcementForm').reset();
  document.getElementById('ann_id').value = ann_id;
  document.getElementById('announcementModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Announcement';
  document.getElementById('submitBtnText').textContent = 'Update Announcement';

  fetch('get_announcement.php?ann_id=' + encodeURIComponent(ann_id))
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('ann_title').value = data.title || '';
        document.getElementById('ann_details').value = data.details || '';
        document.getElementById('ann_status').value = data.status || 'draft';
        if (data.image_path && data.image_path !== 'null') {
          document.getElementById('imagePreview').style.display = 'block';
          document.getElementById('previewImg').src = '../../' + data.image_path;
          document.getElementById('imageInfo').innerHTML = '<i class="fas fa-check-circle"></i> Current image will be kept.';
        } else {
          document.getElementById('imagePreview').style.display = 'none';
          document.getElementById('imageInfo').innerHTML = '<i class="fas fa-lightbulb"></i> Optional: Upload an image...';
        }
      } else {
        showMessage('Error', 'Error loading announcement: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => showMessage('Error', 'Error loading announcement data: ' + err.message));

  openModal('addAnnouncementModal');
}

function deleteAnnouncement(ann_id) {
  if (!confirm('Delete this announcement?')) return;
  fetch('delete_announcement.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'ann_id=' + encodeURIComponent(ann_id)
  })
  .then(res => res.text())
  .then(data => {
    if (data.trim() === 'success') {
      showMessage('Success', 'Announcement deleted.');
      setTimeout(() => location.reload(), 1000);
    } else {
      showMessage('Error', 'Failed to delete announcement: ' + data);
    }
  })
  .catch(err => showMessage('Error', 'Error deleting announcement: ' + err));
}

function previewImage(src) {
  document.getElementById('previewImage').src = src;
  openModal('imagePreviewModal');
}


/* ====================================================================
   INITIALIZATION
   ==================================================================== */
document.addEventListener('DOMContentLoaded', function() {
  // Initialize all content sections
  loadRequests();
  loadComplaints();
  loadAnnouncements();

  // Setup navigation
  const sections = {
    dashboard: document.getElementById('dashboardSection'),
    users: document.getElementById('usersSection'),
    requests: document.getElementById('requestsSection'),
    complaints: document.getElementById('complaintsSection'),
    announcements: document.getElementById('announcementsSection'),
    settings: document.getElementById('settingsSection')
  };

  function hideAllSections() {
    Object.values(sections).forEach(s => { if (s) s.style.display = 'none'; });
  }

  function showSection(targetId) {
    hideAllSections();
    const section = document.getElementById(targetId);
    if (section) section.style.display = 'block';
  }

  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const target = this.dataset.target;
      if (!target) return;
      showSection(target);
      document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
      this.classList.add('active');
    });
  });

  // Show dashboard by default
  showSection('dashboardSection');
  document.querySelector('.nav-dashboard').classList.add('active');

  // Image preview functionality for announcements
  const imageInput = document.getElementById('ann_image');
  if (imageInput) {
    imageInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('imagePreview').style.display = 'block';
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('imageInfo').innerHTML = '<i class="fas fa-check-circle"></i> Image selected for upload';
        };
        reader.readAsDataURL(file);
      }
    });
  }
});
