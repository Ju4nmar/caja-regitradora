<?php

class UserController {

    private $db;
    private $ROLES_VALIDOS = ['Administrador', 'Vendedor'];

    public function __construct($db) {
        $this->db = $db;
    }

    // LISTAR USUARIOS
    public function index() {
        $stmt = $this->db->prepare(
            "SELECT u.id_usuario, u.username, u.nombre_completo, u.created_at, r.tipo_rol
             FROM usuarios u
             JOIN roles r ON u.id_rol = r.id_rol"
        );
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // CREAR USUARIO
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['username'], $data['password'], $data['rol'], $data['nombre_completo'])) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        if (!in_array($data['rol'], $this->ROLES_VALIDOS)) {
            http_response_code(400);
            echo json_encode(["error" => "Rol inválido"]);
            return;
        }

        $stmtRol = $this->db->prepare(
            "SELECT id_rol FROM roles WHERE tipo_rol = ?"
        );
        $stmtRol->execute([$data['rol']]);
        $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            http_response_code(400);
            echo json_encode(["error" => "Rol no existe"]);
            return;
        }

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (username, password_hash, nombre_completo, id_rol)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['username'],
            $passwordHash,
            $data['nombre_completo'],
            $rol['id_rol']
        ]);

        echo json_encode(["message" => "Usuario creado"]);
    }

    // ACTUALIZAR USUARIO
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['username'], $data['rol'], $data['nombre_completo'])) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        if (!in_array($data['rol'], $this->ROLES_VALIDOS)) {
            http_response_code(400);
            echo json_encode(["error" => "Rol inválido"]);
            return;
        }

        $stmtRol = $this->db->prepare(
            "SELECT id_rol FROM roles WHERE tipo_rol = ?"
        );
        $stmtRol->execute([$data['rol']]);
        $rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

        if (!$rol) {
            http_response_code(400);
            echo json_encode(["error" => "Rol no existe"]);
            return;
        }

        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db->prepare(
                "UPDATE usuarios
                 SET username = ?, password_hash = ?, nombre_completo = ?, id_rol = ?
                 WHERE id_usuario = ?"
            );
            $stmt->execute([
                $data['username'],
                $passwordHash,
                $data['nombre_completo'],
                $rol['id_rol'],
                $id
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE usuarios
                 SET username = ?, nombre_completo = ?, id_rol = ?
                 WHERE id_usuario = ?"
            );
            $stmt->execute([
                $data['username'],
                $data['nombre_completo'],
                $rol['id_rol'],
                $id
            ]);
        }

        echo json_encode(["message" => "Usuario actualizado"]);
    }

    // ELIMINAR USUARIO
    public function destroy($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM usuarios WHERE id_usuario = ?"
        );
        $stmt->execute([$id]);

        echo json_encode(["message" => "Usuario eliminado"]);
    }
}
