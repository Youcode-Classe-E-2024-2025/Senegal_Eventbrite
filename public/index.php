<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

\Config\Config::init();
// var_dump($_ENV['MAIL_HOST']);
// var_dump($_ENV['MAIL_USERNAME']);

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;


// $mail = new PHPMailer(true);

// try {
//     // Configuration SMTP
//     $mail->isSMTP();
//     $mail->Host       = 'sandbox.smtp.mailtrap.io';
//     $mail->SMTPAuth   = true;
//     $mail->Username   = 'bbb4159c810d82';
//     $mail->Password   = 'e55e87e152e0de';
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//     $mail->Port       = 2525;

//     // Expéditeur et destinataire
//     $mail->setFrom('no-reply@test.com', 'Test Mailtrap');
//     $mail->addAddress('amdymoustapha011@gmail.com'); // Remplace par ton email de test

//     // Contenu du mail
//     $mail->isHTML(true);
//     $mail->Subject = 'Test Mailtrap avec PHPMailer';
//     $mail->Body    = '<h1>Salut ! Ceci est un test.</h1>';

//     // Envoi
//     if ($mail->send()) {
//         echo 'Email envoyé avec succès via Mailtrap';
//     }
// } catch (Exception $e) {
//     echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
// }


// var_dump($_ENV['MAIL_HOST']);
// var_dump($_ENV['MAIL_USERNAME']);
// var_dump($_ENV['MAIL_PASSWORD']);
// var_dump($_ENV['MAIL_FROM']);
// var_dump($_ENV['MAIL_FROM_NAME']);

// var_dump($_ENV['MAIL_HOST']);
// var_dump($_ENV['MAIL_USERNAME']);
// var_dump($_ENV['MAIL_PASSWORD']);
// var_dump($_ENV['MAIL_PORT']);
// var_dump($_ENV['MAIL_FROM']);
// var_dump($_ENV['MAIL_FROM_NAME']);


session_start();

require_once __DIR__ . '/../app/models/Reservations.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/controllers/front/ReservationsController.php';
require_once __DIR__ . '/../app/config/routes.php';

