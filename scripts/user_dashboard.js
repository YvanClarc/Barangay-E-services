function showRequestCertificateSection() {
      document.querySelector('.dashboard').style.display = 'none';
      document.getElementById('requestCertificateSection').style.display = 'block';
    }

    function showDashboardSection() {
      document.querySelector('.dashboard').style.display = 'block';
      document.getElementById('requestCertificateSection').style.display = 'none';
      resetRequestForm();
    }

    function resetRequestForm() {
      const form = document.querySelector('#requestCertificateSection .request-form');
      if (form) form.reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
      var navLinks = document.querySelectorAll('.nav a');
      if (navLinks.length > 2) {
        navLinks[2].addEventListener('click', function(e) {
          e.preventDefault();
          showRequestCertificateSection();
        });
        navLinks[0].addEventListener('click', function(e) {
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

    // Edit request (fetch data and fill the form)
    function editRequest(r_id) {
      fetch('get_request.php?r_id=' + encodeURIComponent(r_id))
        .then(res => res.json())
        .then(data => {
          // Show the request form section
          document.querySelector('.dashboard').style.display = 'none';
          document.getElementById('requestCertificateSection').style.display = 'block';

          // Fill the form fields
          document.getElementById('first_name').value = data.first_name;
          document.getElementById('second_name').value = data.second_name;
          document.getElementById('last_name').value = data.last_name;
          document.getElementById('gender').value = data.gender;
          document.getElementById('age').value = data.age;
          document.getElementById('address').value = data.address;
          document.getElementById('doc_type').value = data.document_type;
          document.getElementById('purpose').value = data.purpose;

          // Add or update a hidden field for r_id
          let hidden = document.getElementById('edit_r_id');
          if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'edit_r_id';
            hidden.id = 'edit_r_id';
            document.querySelector('.request-form').appendChild(hidden);
          }
          hidden.value = data.r_id;

          // Change submit button text to "Update Request"
          document.querySelector('.request-form button[type="submit"]').textContent = "Update Request";
        });
    }