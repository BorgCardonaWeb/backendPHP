<?php

require_once __DIR__ . '/../models/UserAdminModel.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../config/mailer.php';

use Firebase\JWT\JWT;
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
            'adminUser' => $user  // Aquí se incluye la información del usuario
        ];
    }
    return ['message' => 'Invalid credentials'];
}


    public function forgotPassword()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = $data['email'] ?? null;
        if (empty($email)) {
            return json_encode(['message' => 'Email is required']);
        }

        $user = $this->userAdminModel->findByEmail($email);
        if (!$user) {
            return json_encode(['message' => 'User not found']);
        }

        $token = bin2hex(random_bytes(50));
        $resetLink = "http://localhost:4200/reset-password?token=$token";

        $mailOptions = [
            'from' => 'infogardeningmalta@gardeningmalta.com.mt',
            'to' => $user['email'],
            'subject' => 'Password Reset',
            'text' => "Click the following link to reset your password: $resetLink"
        ];
		
		$mailer = new Mailer();

		if ($mailer->send($mailOptions['from'], $mailOptions['to'], $mailOptions['subject'], $mailOptions['text'])) {
			return json_encode(['message' => 'Password reset email sent']);
		} else {
			return json_encode(['message' => 'Error sending email']);
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
