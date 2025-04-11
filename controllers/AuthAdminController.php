<?php

require_once __DIR__ . '/../models/UserAdminModel.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../config/mailer.php';


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthAdminController
{
    private $userAdminModel;

    public function __construct($db)
    {
        $this->userAdminModel = new UserAdminModel($db);
    }

    public function register($userData)
    {
        $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        $this->userAdminModel->create($userData);
        return ['message' => 'User registered successfully'];
    }

    public function login($email, $password)
{
    $user = $this->userAdminModel->findByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $token = JWT::encode(['id' => $user['id'], 'email' => $user['email']], 'your_jwt_secret', 'HS256');
        return [
            'token' => $token,
            'adminUser' => $user  
        ];
    }
    http_response_code(401);
    return ['error' => 'Invalid credentials'];
}


    public function forgotPassword($email)
    {
        if (empty($email)) {
            return json_encode(['message' => 'Email is required']);
        }

        $user = $this->userAdminModel->findByEmail($email);
        if (!$user) {
            return json_encode(['message' => 'User not found']);
        }

        // Generar el token JWT
        $secretKey = getenv('JWT_SECRET'); 
        $issuedAt = time();  
        $expirationTime = $issuedAt + 3600;  
        $payload = [
            'iss' => 'infogardeningmalta@gardeningmalta.com.mt',  
            'sub' => $user['id'],  
            'iat' => $issuedAt,    
            'exp' => $expirationTime  
        ];

        // Generar el JWT con el algoritmo 'HS256'
        $token = JWT::encode($payload, $secretKey, 'HS256');

        // Construir el enlace de restablecimiento de contraseña con el JWT 
        $resetLink = "https://gardeningmalta.com.mt/management/resetAdmin-password?token=$token";

        // Configurar los detalles del correo
        $mailOptions = [
            'from' => 'infogardeningmalta@gardeningmalta.com.mt',
            'to' => $user['email'],
            'subject' => 'Password Reset',
            'text' => "Click the following link to reset your password: $resetLink"
        ];
        
        // Enviar el correo
        $mailer = new Mailer();

        if ($mailer->send($mailOptions['from'], $mailOptions['to'], $mailOptions['subject'], $mailOptions['text'])) {
            return json_encode(['message' => 'Password reset email sent']);
        } else {
            return json_encode(['message' => 'Error sending email']);
        }
    }

    public function resetPassword($token, $newPassword)
    {
        try {
            // Verificar y decodificar el token JWT
            $decoded = $this->decodeJWT($token);

            // Hashear la nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos utilizando el ID del usuario decodificado
            $this->userAdminModel->updatePassword($decoded['sub'], $hashedPassword);

            return ['message' => 'Password reset successfully'];
        } catch (Exception $e) {
            return ['message' => 'Error resetting password', 'error' => $e->getMessage()];
        }
    }
    
     public function decodeJWT($token)
    {
        $secretKey = getenv('JWT_SECRET');

        try {
            // Usar JWT::decode para decodificar y verificar el token
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            // Convertir el objeto resultante a un arreglo
            return (array) $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    public function getAdminUserById($id) {
        try {
            $adminUser = $this->userAdminModel->findById($id);

            if ($adminUser) {
                return $adminUser;
            } else {
                http_response_code(404);
                return ['message' => 'Admin user not found'];
            }
        } catch (Exception $e) {
            http_response_code(500);
            return ['message' => 'Failed to retrieve admin user: ' . $e->getMessage()];
        }
    }
}
