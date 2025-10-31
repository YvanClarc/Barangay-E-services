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
