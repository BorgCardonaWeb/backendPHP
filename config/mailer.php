<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true); // Instanciar PHPMailer

        // Configuración del servidor SMTP
        $this->mail->isSMTP(); // Configurar para usar SMTP
        $this->mail->Host = 'smtp.openxchange.eu'; // Cambia esto si es necesario
        $this->mail->SMTPAuth = true; // Habilitar autenticación SMTP
        $this->mail->Username = getenv('SMTP2GO_USER'); // Usuario SMTP
        $this->mail->Password = getenv('SMTP2GO_PASSWORD'); // Contraseña SMTP
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar el cifrado TLS
        $this->mail->Port = 587; // Puerto TCP para conectarse

        // Activar el debug para ver más detalles si hay errores
        $this->mail->SMTPDebug = 0; // Cambia el valor a 2 para mostrar más detalles en los logs
    }

    public function send($from, $to, $subject, $body) {
        try {
            // Configurar destinatario
            $this->mail->setFrom($from, 'Gardening Malta'); // Remitente
            $this->mail->addAddress($to); // Añadir destinatario

            // Contenido del correo
            $this->mail->isHTML(true); // Configurar el formato del correo como HTML
            $this->mail->Subject = $subject; // Asunto del correo
            $this->mail->Body = $body; // Cuerpo del correo

            if ($this->mail->send()) {
                return true; // Indicar que el correo se envió correctamente
            } else {
                throw new Exception('No se pudo enviar el correo');
            }
        } catch (Exception $e) {
            // Capturar los errores y devolver el mensaje de error
            echo 'Error en el envío del correo: ' . $e->getMessage();
            return false; // Indicar que hubo un error al enviar el correo
        }
    }
}
?>
