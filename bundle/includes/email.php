<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $body, $altBody = '', $attachments = [], $embeddedImages = []) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        // $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'Soorajsingh1911@gmail.com';
        $mail->Password   = 'okqv pyha xshu dlgz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('Soorajsingh1911@gmail.com', 'Culture of Internet');
        $mail->addAddress($to);

        // Attachments
        foreach ($attachments as $filePath) {
            $mail->addAttachment($filePath);
        }

        // Embedded images
        foreach ($embeddedImages as $cid => $path) {
            $fullPath = __DIR__ . '/../' . $path;
            if (file_exists($fullPath)) {
                $mail->addEmbeddedImage($fullPath, $cid);
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        if ($altBody) {
            $mail->AltBody = $altBody;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
