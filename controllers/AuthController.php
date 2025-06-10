<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../config/mailer.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController
{
    private $userModel;

    public function __construct($db)
    {
        $this->userModel = new UserModel($db);
    }

    public function register($userData)
    {
        try {
            // Validar si el email ya existe
            if ($this->userModel->emailExists($userData['email'])) {
                http_response_code(400);
                return ['error' => 'Email already registered'];
            }

            // Continúa con el registro
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
            $this->userModel->create($userData);
            return ['message' => 'User registered successfully'];
        } catch (Exception $ex) {
            http_response_code(500);
            return ['error' => $ex->getMessage()];
        }
    }

    public function login($email, $password)
    {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $token = JWT::encode(['id' => $user['id'], 'email' => $user['email']], 'your_jwt_secret', 'HS256');
            return [
                'token' => $token,
                'user' => $user
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

        $user = $this->userModel->findByEmail($email);
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
        $resetLink = "https://gardeningmalta.com.mt/reset-password?token=$token";

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
            $this->userModel->updatePassword($decoded['sub'], $hashedPassword);

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

    public function updateUser($userId)
    {
        $updatedData = json_decode(file_get_contents("php://input"), true);

        error_log(print_r($updatedData, true));

        if ($updatedData === null) {
            return ['success' => false, 'message' => 'Invalid or empty data received'];
        }

        try {
            $result = $this->userModel->updateUser($userId, $updatedData);
            return $result;
        } catch (Exception $e) {
            http_response_code(500);
            return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
        }
    }

}
