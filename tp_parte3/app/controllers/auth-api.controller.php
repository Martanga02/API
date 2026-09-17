<?php
require_once './app/models/user.model.php';

require_once './libs/jwt/jwt.php';

class AuthApiController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login($request, $response) {
        $authorization = $request->authorization;
        $auth = explode(' ', $authorization);
    
        if (count($auth) != 2 || $auth[0] !== 'Basic') {
            header("WWW-Authenticate: Basic realm='Get a token'");
            return $response->json("Autenticación no válida", 401);
        }
    
        $auth = base64_decode($auth[1]);
        $user_pass = explode(":", $auth);
        if (count($user_pass) != 2) {
            return $response->json("Autenticación no válida", 401);
        }
    
        $user = $user_pass[0];
        $password = $user_pass[1];
    
        $userFromDB = $this->userModel->getByUser($user);
    
        if (!$userFromDB || !password_verify($password, $userFromDB->contrasena)) {
            return $response->json("Usuario o contraseña incorrecta", 401);
        }
    
        $payload = [
            'sub' => $userFromDB->id,
            'usuario' => $userFromDB->usuario,
            'roles' => [$userFromDB->rol ?? 'USER'],
            'exp' => time() + 3600
        ];
    
        $token = createJWT($payload);
        return $response->json(['token' => $token], 200);
    }
    
}