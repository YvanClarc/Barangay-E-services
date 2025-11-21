function showRequestCertificateSection() {
  showSectionById('requestCertificateSection');
}

function showFileComplaintSection() {
  showSectionById('fileComplaintSection');
  resetComplaintForm();
}

function showDashboardSection() {
  showSectionById('dashboardSection');
  resetRequestForm();
}

// Show only the section with the given id; hide all others to prevent overlap
function showSectionById(id){
  document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
  const target = document.getElementById(id);
  if (target) target.style.display = 'block';
}

function resetRequestForm() {
  const form = document.querySelector('#certificateRequestForm');
  if (form) {
    form.reset();
    // Clear any validation states
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.style.borderColor = '#e9ecef';
      input.style.boxShadow = 'none';
      input.style.transform = 'none';
    });
  }
}

function resetComplaintForm() {
  const form = document.querySelector('#complaintForm');
  if (form) {
    form.reset();
    // Clear any validation states
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.style.borderColor = '#e9ecef';
      input.style.boxShadow = 'none';
      input.style.transform = 'none';
    });
  }
}

// User Requests Management Functions
let currentRequestPage = 1;
const itemsPerPageRequests = 10;
let allUserRequests = [];

function loadUserRequests() {
  const rows = Array.from(document.querySelectorAll('#userTable tbody tr'));
  allUserRequests = rows.map(row => ({
    element: row,
    status: row.dataset.status
  }));
  renderRequestPage(1);
}

function filterUserRequests() {
  const statusFilter = document.getElementById('requestStatusFilter').value;

  allUserRequests.forEach(request => {
    const statusMatch = !statusFilter || request.status === statusFilter;
    request.element.style.display = statusMatch ? '' : 'none';
  });

  currentRequestPage = 1;
  renderRequestPage(currentRequestPage);
}

function renderRequestPage(page) {
  const start = (page - 1) * itemsPerPageRequests;
  const end = start + itemsPerPageRequests;
  let visibleCount = 0;

  allUserRequests.forEach((request, index) => {
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

// User Complaints Management Functions
let currentComplaintPage = 1;
const itemsPerPageComplaints = 10;
let allUserComplaints = [];

function loadUserComplaints() {
  const rows = Array.from(document.querySelectorAll('#complaintsTable tbody tr'));
  allUserComplaints = rows.map(row => ({
    element: row,
    status: row.dataset.status
  }));
  renderComplaintPage(1);
}

function filterUserComplaints() {
  const statusFilter = document.getElementById('complaintStatusFilter').value;

  allUserComplaints.forEach(complaint => {
    const statusMatch = !statusFilter || complaint.status === statusFilter;
    complaint.element.style.display = statusMatch ? '' : 'none';
  });

  currentComplaintPage = 1;
  renderComplaintPage(currentComplaintPage);
}

function renderComplaintPage(page) {
  const start = (page - 1) * itemsPerPageComplaints;
  const end = start + itemsPerPageComplaints;
  let visibleCount = 0;

  allUserComplaints.forEach((complaint, index) => {
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

document.addEventListener('DOMContentLoaded', function() {
  var navLinks = document.querySelectorAll('.nav a');
  if (navLinks.length) {
    navLinks.forEach((link, idx) => {
      link.addEventListener('click', function(e){
        e.preventDefault();
        // set active class
        navLinks.forEach(l=>l.classList.remove('active'));
        this.classList.add('active');

        // map indexes to section ids (keeps existing order)
        if (idx === 0) showSectionById('dashboardSection');
        else if (idx === 1) showSectionById('announcementsSection');
        else if (idx === 2) showSectionById('requestCertificateSection');
        else if (idx === 3) showSectionById('fileComplaintSection');
        else if (idx === 4) showSectionById('settingsSection');
      });
    });
  }



// Initialize user dashboard features
loadUserRequests();
loadUserComplaints();
});



// Delete request
function deleteRequest(r_id) {
  if (confirm("Are you sure you want to delete this request?")) {
    fetch('delete_request.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'r_id=' + encodeURIComponent(r_id)
    })
    .then(res => res.text())
    .then(data => {
      alert('Request deleted successfully.');
      location.reload();
    })
    .catch(err => alert('Failed to delete request.'));
  }
}

// Edit request
function editRequest(r_id) {
  fetch('get_request.php?r_id=' + encodeURIComponent(r_id))
    .then(res => res.json())
    .then(data => {
      document.querySelector('.dashboard').style.display = 'none';
      document.getElementById('requestCertificateSection').style.display = 'block';
      document.getElementById('fileComplaintSection').style.display = 'none';

      document.getElementById('first_name').value = data.first_name;
      document.getElementById('second_name').value = data.second_name;
      document.getElementById('last_name').value = data.last_name;
      document.getElementById('gender').value = data.gender;
      document.getElementById('age').value = data.age;
      document.getElementById('address').value = data.address;
      document.getElementById('doc_type').value = data.document_type;
      document.getElementById('purpose').value = data.purpose;

      let hidden = document.getElementById('edit_r_id');
      if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'edit_r_id';
        hidden.id = 'edit_r_id';
        document.querySelector('.request-form').appendChild(hidden);
      }
      hidden.value = data.r_id;

      document.querySelector('.request-form button[type="submit"]').textContent = "Update Request";
    });
}

function viewPickupDetails(r_id) {
  // Fetch pickup details
  fetch('get_request.php?r_id=' + encodeURIComponent(r_id))
    .then(res => res.json())
    .then(data => {
      document.getElementById('pickupDetails').innerHTML = `
        <p><strong>Document Type:</strong> ${data.document_type}</p>
        <p><strong>Purpose:</strong> ${data.purpose}</p>
        <p><strong>Pickup Date & Time:</strong> ${new Date(data.pickup_datetime).toLocaleString()}</p>
        <p><strong>Fees:</strong> PHP ${parseFloat(data.fees || 0).toFixed(2)}</p>
        <p><strong>Instructions:</strong> ${data.instructions || 'None'}</p>
      `;
      openModal('viewPickupModal');
    })
    .catch(err => alert('Failed to load pickup details.'));
}

function viewHearingDetails(c_id) {
  // Fetch hearing details
  fetch('../admin/get_hearing.php?c_id=' + encodeURIComponent(c_id))
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        let html = '<div style="max-height:400px;overflow-y:auto;">';
        if (data.hearings.length > 0) {
          data.hearings.forEach(hearing => {
            const hearingDate = new Date(hearing.date + ' ' + hearing.time).toLocaleString();
            const statusClass = hearing.status === 'Scheduled' ? 'status-pending' :
                               hearing.status === 'Completed' ? 'status-active' : 'status-denied';
            html += `
              <div style="border:1px solid #ddd;padding:15px;margin-bottom:10px;border-radius:8px;">
                <h4 style="margin:0 0 10px 0;color:#1e3d8f;">${hearing.hearing_no}${hearing.hearing_no === 1 ? 'st' : hearing.hearing_no === 2 ? 'nd' : 'rd'} Hearing</h4>
                <p style="margin:5px 0;"><strong>Date & Time:</strong> ${hearingDate}</p>
                <p style="margin:5px 0;"><strong>Status:</strong> <span class="${statusClass}">${hearing.status}</span></p>
                <p style="margin:5px 0;font-size:12px;color:#666;"><strong>Scheduled:</strong> ${new Date(hearing.created_at).toLocaleString()}</p>
              </div>
            `;
          });
        } else {
          html += '<p style="text-align:center;color:#666;padding:40px;">No hearings scheduled yet.</p>';
        }
        html += '</div>';
        document.getElementById('hearingDetails').innerHTML = html;
        openModal('viewHearingModal');
      } else {
        alert('Failed to load hearing details: ' + data.message);
      }
    })
    .catch(err => alert('Failed to load hearing details.'));
}

function logout(confirmMsg = 'Are you sure you want to log out?') {
  if (!confirm(confirmMsg)) return;
  // if page is inside /users/... go up two levels to project root, otherwise use root path
  const path = window.location.pathname.toLowerCase();
  const logoutPath = path.includes('/users/') ? '../../logout.php' : 'logout.php';
  window.location.href = logoutPath;
}

// Modal functions
function openModal(modalId) {
  document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
}
