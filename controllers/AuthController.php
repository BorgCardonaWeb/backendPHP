<?php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../config/mailer.php';

use Firebase\JWT\JWT;
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
        $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
        $this->userModel->create($userData);
        return ['message' => 'User registered successfully'];
    }

    public function login($email, $password)
    {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $token = JWT::encode(['id' => $user['id'], 'email' => $user['email']], 'your_jwt_secret', 'HS256');
            return ['token' => $token];
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

        $user = $this->userModel->findByEmail($email);
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
}
