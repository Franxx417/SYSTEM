// Admin Users Index Page JavaScript
// Handles opening and populating the Edit User modal

window.editUser = function editUser(id, name, email, position, department, role) {
    var form = document.getElementById('editUserForm');
    if (form) form.action = '/admin/users/' + id + '/edit';
    var el;
    el = document.getElementById('edit_user_name'); if (el) el.value = name || '';
    el = document.getElementById('edit_user_email'); if (el) el.value = email || '';
    el = document.getElementById('edit_user_position'); if (el) el.value = position || '';
    el = document.getElementById('edit_user_department'); if (el) el.value = department || '';
    el = document.getElementById('edit_user_role'); if (el) el.value = role || '';
    if (window.bootstrap && document.getElementById('editUserModal')) {
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
};


