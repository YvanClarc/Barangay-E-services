function showRequestCertificateSection() {
  document.querySelector('.dashboard').style.display = 'none';
  document.getElementById('requestCertificateSection').style.display = 'block';
  document.getElementById('fileComplaintSection').style.display = 'none';
}

function showFileComplaintSection() {
  document.querySelector('.dashboard').style.display = 'none';
  document.getElementById('requestCertificateSection').style.display = 'none';
  document.getElementById('fileComplaintSection').style.display = 'block';
  resetComplaintForm();
}

function showDashboardSection() {
  document.querySelector('.dashboard').style.display = 'block';
  document.getElementById('requestCertificateSection').style.display = 'none';
  document.getElementById('fileComplaintSection').style.display = 'none';
  resetRequestForm();
}

function resetRequestForm() {
  const form = document.querySelector('#requestCertificateSection .request-form');
  if (form) form.reset();
}

function resetComplaintForm() {
  const form = document.querySelector('#fileComplaintSection .complaint-form');
  if (form) form.reset();
}

document.addEventListener('DOMContentLoaded', function() {
  var navLinks = document.querySelectorAll('.nav a');
  if (navLinks.length >= 4) {
    navLinks[2].addEventListener('click', function(e) { // Request Certificate
      e.preventDefault();
      showRequestCertificateSection();
    });

    navLinks[3].addEventListener('click', function(e) { // File Complaint
      e.preventDefault();
      showFileComplaintSection();
    });

    navLinks[0].addEventListener('click', function(e) { // Dashboard
      e.preventDefault();
      showDashboardSection();
    });
  }
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

function logout(confirmMsg = 'Are you sure you want to log out?') {
  if (!confirm(confirmMsg)) return;
  // if page is inside /users/... go up two levels to project root, otherwise use root path
  const path = window.location.pathname.toLowerCase();
  const logoutPath = path.includes('/users/') ? '../../logout.php' : 'logout.php';
  window.location.href = logoutPath;
}