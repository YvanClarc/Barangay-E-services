function openModal(id) {
  console.log("Opening modal:", id);
  const modal = document.getElementById(id);
  modal.style.display = 'flex';
  console.log("Modal found:", modal);
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

// Show success/error message modal
function showMessage(status, text) {
  const modal = document.getElementById('messageModal');
  const title = document.getElementById('messageTitle');
  const messageText = document.getElementById('messageText');

  // Reset old classes
  modal.classList.remove('message-success', 'message-error');

  // Apply styling based on status
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

// ✅ Close message modal only (keeps the form modal open)
function closeMessageModal() {
  closeModal('messageModal');
}

// Close modal when clicking outside (for all except the inner message)
window.onclick = function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target === modal && modal.id !== 'messageModal') {
      modal.style.display = 'none';
    }
  });
};

function updateStatus(id, status) {
  if (confirm("Are you sure you want to approve this user?")) {
    fetch('update_user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}&status=${status}`
    })
    .then(res => res.text())
    .then(data => {
      showMessage('Success', 'User status updated successfully.');
      setTimeout(() => location.reload(), 1000);
    })
    .catch(err => showMessage('Error', 'Failed to update user.'));
  }
}

function deleteUser(id) {
  if (confirm("Are you sure you want to delete this user?")) {
    fetch('delete_user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    })
    .then(res => res.text())
    .then(data => {
      showMessage('Success', 'User deleted successfully.');
      setTimeout(() => location.reload(), 1000);
    })
    .catch(err => showMessage('Error', 'Failed to delete user.'));
  }
}

function searchUsers() {
  const input = document.getElementById('userSearch').value.toLowerCase();
  const rows = document.querySelectorAll('#userTable tbody tr');

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(input) ? '' : 'none';
  });
}

function updateRequestStatus(r_id, status) {
  if (confirm("Are you sure you want to " + status + " this request?")) {
    fetch('update_request_status.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'r_id=' + encodeURIComponent(r_id) + '&status=' + encodeURIComponent(status)
    })
    .then(res => res.text())
    .then(data => {
      alert('Request status updated.');
      location.reload();
    })
    .catch(err => alert('Failed to update request.'));
  }
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
    alert(response);
    location.reload();
  })
  .catch(err => {
    alert('Error updating complaint: ' + err);
  });
}


// Navigation toggle for Announcements
    document.addEventListener('DOMContentLoaded', function() {
  const sections = {
    dashboard: document.getElementById('dashboardSection'),
    announcements: document.getElementById('announcementsSection'),
    request: document.getElementById('requestCertificateSection'),
    complaint: document.getElementById('fileComplaintSection'),
    settings: document.getElementById('settingsSection') // optional
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

      // Reset forms when switching to forms
      if (target === 'requestCertificateSection') resetRequestForm();
      if (target === 'fileComplaintSection') resetComplaintForm();
    });
  });

  // Show dashboard by default
  showSection('dashboardSection');
});


    // Edit Announcement
    function editAnnouncement(ann_id) {
      fetch('get_announcement.php?ann_id=' + encodeURIComponent(ann_id))
        .then(res => res.json())
        .then(data => {
          if (!data) { alert('Failed to load announcement'); return; }
          document.getElementById('ann_id').value = data.ann_id;
          document.getElementById('ann_title').value = data.title;
          document.getElementById('ann_details').value = data.details;
          document.getElementById('ann_status').value = data.status;
          document.getElementById('imageInfo').innerText = data.image_path ? 'Current: ' + data.image_path : '';
          document.getElementById('announcementModalTitle').innerText = 'Edit Announcement';
          document.getElementById('submitAnnBtn').innerText = 'Update Announcement';
          openModal('addAnnouncementModal');
        })
        .catch(err => alert('Error loading announcement: ' + err));
    }

    // Delete Announcement
    function deleteAnnouncement(ann_id) {
      if (!confirm('Delete this announcement?')) return;
      fetch('delete_announcement.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ann_id=' + encodeURIComponent(ann_id)
      })
      .then(res => res.text())
      .then(data => {
        console.log('Response:', data);
        if (data.trim() === 'success') {
          alert('Announcement deleted.');
          location.reload();
        } else {
          alert('Failed to delete announcement: ' + data);
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error deleting announcement: ' + err);
      });
    }

    // Preview image
    function previewImage(src) {
      document.getElementById('previewImage').src = src;
      openModal('previewModal');
    }

    // Reset form when opening modal
    function openAddAnnouncementModal() {
      document.getElementById('announcementForm').reset();
      document.getElementById('ann_id').value = '';
      document.getElementById('announcementModalTitle').innerText = 'Post Announcement';
      document.getElementById('submitAnnBtn').innerText = 'Post Announcement';
      openModal('addAnnouncementModal');
    }