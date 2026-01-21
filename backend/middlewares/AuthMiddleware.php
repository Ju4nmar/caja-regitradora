<?php
// Importar dependencias
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Middleware de autenticación
class AuthMiddleware {
    // Verificar token JWT
    public static function verify() {
        $headers = getallheaders();
        // Verificar si el token está presente
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["error" => "Token requerido"]);
            exit;
        }
        // Obtener el token
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        // Decodificar el token
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return $decoded; // datos del usuario
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => "Token inválido"]);
            exit;
        }
    }
}

?>