<?php

// Importar dependencias
use Firebase\JWT\JWT;

// Controlador de autenticación
class AuthController {

    // Referencia a la conexión de base de datos
    private $db;

    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }

    // Método de login
    public function login() {

        // Obtener datos JSON del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar datos
        if (!isset($data['username'], $data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        // Buscar usuario en la base de datos
        $stmt = $this->db->prepare(
            "SELECT u.id_usuario, u.username, u.password_hash, r.tipo_rol
             FROM usuarios u
             JOIN roles r ON u.id_rol = r.id_rol
             WHERE u.username = ?"
        );
     
        $stmt->execute([$data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar credenciales
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(["error" => "Credenciales inválidas"]);
            return;
        }

        // Generar token JWT
        $payload = [
            "iat" => time(),
            "exp" => time() + 3600,
            "uid" => $user['id_usuario'],
            "rol" => $user['tipo_rol']
        ];
        
        // Codificar el token
        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        
        echo json_encode(["token" => $token]);
    }
}
