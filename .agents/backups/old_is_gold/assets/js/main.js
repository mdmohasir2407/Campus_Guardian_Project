/**
 * CampusGuardian - Client JavaScript Engine
 */

$(document).ready(function () {
    // 1. Sidebar Toggle & Sliding Drawer Animation
    function toggleSidebar() {
        $('#sidebar').toggleClass('active');
        $('#sidebarOverlay').toggleClass('active');
    }

    function closeSidebar() {
        $('#sidebar').removeClass('active');
        $('#sidebarOverlay').removeClass('active');
    }

    $('#sidebarCollapse').on('click', function (e) {
        e.preventDefault();
        toggleSidebar();
    });

    $('#sidebarOverlay').on('click', function () {
        closeSidebar();
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            closeSidebar();
        }
    });

    // 2. Dark Mode Toggle & Persistence
    const currentTheme = localStorage.getItem('cg_theme') || 'light';
    if (currentTheme === 'dark') {
        $('html').attr('data-theme', 'dark');
        $('#themeIcon').removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
    }

    $('#btnThemeToggle').on('click', function () {
        let activeTheme = $('html').attr('data-theme');
        if (activeTheme === 'dark') {
            $('html').removeAttr('data-theme');
            localStorage.setItem('cg_theme', 'light');
            $('#themeIcon').removeClass('bi-sun-fill').addClass('bi-moon-stars-fill');
        } else {
            $('html').attr('data-theme', 'dark');
            localStorage.setItem('cg_theme', 'dark');
            $('#themeIcon').removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
        }
    });

    // 3. Initialize Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 4. Run background auto-attendance checker (09:00 AM logic trigger)
    if ($('body').data('user-role')) {
        $.ajax({
            url: '../cron/auto_attendance_check.php',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.marked_count > 0) {
                    console.log('CampusGuardian Auto-Attendance: Marked ' + res.marked_count + ' absent/uninformed students.');
                }
            }
        });
    }

    // 5. AJAX Approval / Rejection Handler Modal Dynamic Filler
    $('.btn-action-modal').on('click', function () {
        const requestId = $(this).data('id');
        const requestType = $(this).data('type');
        const action = $(this).data('action');
        const studentName = $(this).data('student');

        $('#modal_request_id').val(requestId);
        $('#modal_request_type').val(requestType);
        $('#modal_action').val(action);
        $('#modal_student_name').text(studentName);
        $('#modal_action_label').text(action.toUpperCase());

        if (action === 'approved') {
            $('#modal_action_label').removeClass('bg-danger').addClass('bg-success');
        } else {
            $('#modal_action_label').removeClass('bg-success').addClass('bg-danger');
        }

        $('#approvalActionModal').modal('show');
    });

    // Handle approval modal submission via AJAX
    $('#formApprovalAction').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '../ajax/handler.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function () {
                $('#btnSubmitAction').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
            },
            success: function (response) {
                $('#approvalActionModal').modal('hide');
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('An unexpected server error occurred.');
            },
            complete: function () {
                $('#btnSubmitAction').prop('disabled', false).text('Confirm Action');
            }
        });
    });
});
