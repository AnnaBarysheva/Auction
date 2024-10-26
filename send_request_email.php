<?php


require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $paintingId = $_POST['paintingId'];
    $isChecked = filter_var($_POST['isChecked'], FILTER_VALIDATE_BOOLEAN); 

    
    $to = 'levdanskaya2017@gmail.com';
    //$to = 'annabarsh12@gmail.com';

   
    $subject = $isChecked ? 'Новая заявка на картину' : 'Заявка на картину отменена';
    $message = $isChecked 
        ? "Пользователь отправил заявку на картину с ID: $paintingId."
        : "Пользователь отменил заявку на картину с ID: $paintingId.";

    
    $mail = new PHPMailer(true);

    try {
        
       
        $mail->isSMTP();                                      
        $mail->Host = 'smtp.mail.ru';                       
        $mail->SMTPAuth = true;                               
        $mail->Username = 'alya.levdanskaya.04@mail.ru';        
        $mail->Password = 'Gcay5szVqdeBiU3UERM6';                        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   
        $mail->Port = 587;                                    

         
         $mail->CharSet = 'UTF-8';  

        // Настройки отправителя и получателя
        $mail->setFrom('alya.levdanskaya.04@mail.ru', 'Alina');  
        $mail->addAddress($to);                              

        // Содержимое письма
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;                            
        $mail->Body    = $message;                           
        $mail->AltBody = strip_tags($message);               

        // Отправка письма
        if ($mail->send()) {
            echo 'Письмо успешно отправлено';
        } else {
            echo 'Ошибка при отправке письма';
        }
    } catch (Exception $e) {
        echo "Ошибка при отправке письма: {$mail->ErrorInfo}";
    }
}
?>
