<?php
/**
 * CampusGuardian - Responsive HTML Email Templates
 */

class EmailTemplates {
    public static function getHeader($title = 'CampusGuardian Alert') {
        $institution = get_db_setting('system_institution_name', 'CampusGuardian Institute');
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; color: #333; }
                .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
                .email-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 25px; text-align: center; color: #ffffff; }
                .email-header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px; }
                .email-header p { margin: 5px 0 0; opacity: 0.8; font-size: 13px; }
                .email-body { padding: 30px; line-height: 1.6; }
                .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 13px; text-transform: uppercase; }
                .status-approved { background: #d1fae5; color: #065f46; }
                .status-rejected { background: #fee2e2; color: #991b1b; }
                .status-pending { background: #fef3c7; color: #92400e; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .info-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
                .info-table tr:last-child td { border-bottom: none; }
                .email-footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>CampusGuardian</h1>
                    <p>{$institution}</p>
                </div>
                <div class='email-body'>";
    }

    public static function getFooter() {
        return "
                </div>
                <div class='email-footer'>
                    <p>This is an automated notification from CampusGuardian Student Monitoring System.</p>
                    <p>&copy; " . date('Y') . " CampusGuardian. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public static function getLateEntryNotification($student_name, $date, $arrival_time, $late_minutes, $reason, $status, $remarks = '') {
        $badgeClass = strtolower($status) === 'approved' ? 'status-approved' : (strtolower($status) === 'rejected' ? 'status-rejected' : 'status-pending');
        
        $html = self::getHeader('Late Entry Status Notification');
        $html .= "
            <h2 style='color:#1e293b; margin-top:0;'>Late Arrival Notification</h2>
            <p>Dear Parent / Student,</p>
            <p>This notification is to inform you regarding a <strong>Late Entry Record</strong> logged on the CampusGuardian system.</p>
            
            <table class='info-table'>
                <tr><td><strong>Student Name:</strong></td><td>{$student_name}</td></tr>
                <tr><td><strong>Date:</strong></td><td>" . format_date($date) . "</td></tr>
                <tr><td><strong>Arrival Time:</strong></td><td>" . format_time($arrival_time) . "</td></tr>
                <tr><td><strong>Delay Duration:</strong></td><td>{$late_minutes} Minutes</td></tr>
                <tr><td><strong>Reason:</strong></td><td>{$reason}</td></tr>
                <tr><td><strong>Current Status:</strong></td><td><span class='status-badge {$badgeClass}'>{$status}</span></td></tr>";
        
        if (!empty($remarks)) {
            $html .= "<tr><td><strong>Faculty Remarks:</strong></td><td>{$remarks}</td></tr>";
        }

        $html .= "</table>
            <p>If you have any queries, please contact the respective Department HOD office.</p>";
        $html .= self::getFooter();

        return $html;
    }

    public static function getLeaveNotification($student_name, $leave_type, $start_date, $end_date, $reason, $status, $remarks = '') {
        $badgeClass = strtolower($status) === 'approved' ? 'status-approved' : (strtolower($status) === 'rejected' ? 'status-rejected' : 'status-pending');

        $html = self::getHeader('Leave Application Update');
        $html .= "
            <h2 style='color:#1e293b; margin-top:0;'>Leave Application Status Update</h2>
            <p>Dear Parent / Student,</p>
            <p>The leave application submitted for <strong>{$student_name}</strong> has been updated.</p>
            
            <table class='info-table'>
                <tr><td><strong>Leave Type:</strong></td><td>{$leave_type}</td></tr>
                <tr><td><strong>From Date:</strong></td><td>" . format_date($start_date) . "</td></tr>
                <tr><td><strong>To Date:</strong></td><td>" . format_date($end_date) . "</td></tr>
                <tr><td><strong>Reason:</strong></td><td>{$reason}</td></tr>
                <tr><td><strong>Approval Status:</strong></td><td><span class='status-badge {$badgeClass}'>{$status}</span></td></tr>";
        
        if (!empty($remarks)) {
            $html .= "<tr><td><strong>Remarks:</strong></td><td>{$remarks}</td></tr>";
        }

        $html .= "</table>";
        $html .= self::getFooter();

        return $html;
    }

    public static function getAbsenceAlertNotification($student_name, $reg_no, $date, $time = '09:00 AM') {
        $html = self::getHeader('URGENT: Student Absence Alert');
        $html .= "
            <h2 style='color:#dc2626; margin-top:0;'>⚠️ Urgent Absence Notification</h2>
            <p>Dear Parent / Guardian,</p>
            <p>Our automated campus monitoring system has flagged an un-notified absence for your ward:</p>
            
            <table class='info-table'>
                <tr><td><strong>Student Name:</strong></td><td><strong>{$student_name}</strong></td></tr>
                <tr><td><strong>Register Number:</strong></td><td>{$reg_no}</td></tr>
                <tr><td><strong>Date:</strong></td><td>" . format_date($date) . "</td></tr>
                <tr><td><strong>Check-In Cutoff Time:</strong></td><td>{$time}</td></tr>
                <tr><td><strong>Status:</strong></td><td><span class='status-badge status-rejected'>NOT INFORMED / ABSENT</span></td></tr>
            </table>
            
            <p style='background:#fef2f2; border-left:4px solid #ef4444; padding:12px; font-size:13px; color:#991b1b;'>
                <strong>Action Required:</strong> Your ward has not checked into the campus by 09:00 AM today and no prior leave application was received. Please contact the department immediately.
            </p>";
        $html .= self::getFooter();

        return $html;
    }
}
