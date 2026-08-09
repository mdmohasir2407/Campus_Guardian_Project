<?php
/**
 * CampusGuardian - Mail Dispatcher & Logger Engine
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/email_templates.php';

class CampusMailer {

    /**
     * Send email notification and log result into email_logs table
     */
    public static function send($to_email, $to_name, $subject, $body_html) {
        $status = 'Logged';
        
        // Attempt sending via PHP mail()
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: CampusGuardian Alerts <no-reply@campusguardian.edu>';
        $headers[] = 'Reply-To: support@campusguardian.edu';
        $headers[] = 'X-Mailer: CampusGuardian PHP/' . phpversion();

        // In local XAMPP without configured sendmail, mail() returns false, which is gracefully handled
        $mail_sent = @mail($to_email, $subject, $body_html, implode("\r\n", $headers));
        
        if ($mail_sent) {
            $status = 'Sent';
        } else {
            // Keep as Logged for demo/offline XAMPP environments
            $status = 'Logged';
        }

        // Log to database
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, body, status, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$to_email, $to_name, $subject, $body_html, $status]);
        } catch (Exception $e) {
            // Log error silently
        }

        return true;
    }
}
