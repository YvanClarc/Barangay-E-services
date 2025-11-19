# TODO: Improve Request-Handling Feature with Scheduling

## Database Updates
- [ ] Add columns to `tbl_requests`: `pickup_datetime` (DATETIME), `fees` (DECIMAL(10,2)), `instructions` (TEXT)

## Backend Changes
- [ ] Modify `users/admin/update_request_status.php` to accept scheduling details and send email notification

## Frontend Changes
- [ ] Add scheduling modal in `users/admin/official_dashboard.php`
- [ ] Update `scripts/official_dashboard.js` to handle scheduling modal and submission
- [ ] Update `users/user/user_dashboard.php` to display scheduling details for approved requests

## Testing
- [ ] Test approving requests with scheduling
- [ ] Verify email notifications are sent
- [ ] Ensure user dashboard shows pickup details
