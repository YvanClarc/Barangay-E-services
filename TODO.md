# TODO: Improve Complaint-Handling Module for Conflicts

## Database Changes
- [x] Update `database-import/barangay_e-services (3).sql` to add new columns to `tbl_complaints`: `involved_parties` (TEXT), `relationship` (VARCHAR(255)), `evidence` (TEXT)
- [x] Create new table `tbl_hearings`: `h_id` (INT PRIMARY KEY AUTO_INCREMENT), `c_id` (INT FK to tbl_complaints), `hearing_no` (INT 1-3), `date` (DATE), `time` (TIME), `status` (ENUM: 'Scheduled', 'Completed', 'Rescheduled'), `created_at` (DATETIME DEFAULT CURRENT_TIMESTAMP)

## User-Side Changes
- [x] Update `users/user/add_complaint.php` to handle new conflict fields
- [x] Modify `users/user/user_dashboard.php` to show additional form fields for conflicts (involved parties, relationship, evidence) when complaint type is "Conflict with Neighbor" or "Conflict with Persons"
- [x] Add JavaScript to toggle conflict fields visibility based on complaint type

## Admin-Side Changes
- [x] Update `users/admin/official_dashboard.php` to display conflict details in complaint view modal
- [x] Add hearing scheduling functionality: modal to schedule hearings (up to 3), view/edit hearing details
- [x] Update complaint status options to include "In-Progress", "Resolved", "Unresolved"
- [x] Implement logic for tracking hearings and final outcome

## New PHP Files
- [x] Create `users/admin/schedule_hearing.php`
- [ ] Create `users/admin/update_hearing_status.php`
- [ ] Create `users/admin/get_hearing.php`

## Scripts and Logic
- [x] Update `scripts/official_dashboard.js` to handle hearing modals, scheduling, and status updates
- [x] Ensure up to 3 hearings; if all completed and unresolved, set complaint to "Unresolved"

## Testing
- [ ] Test user form for conflict complaints
- [ ] Test admin viewing and scheduling hearings
- [ ] Verify status updates and final resolution logic
