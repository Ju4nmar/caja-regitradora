<?php

class ClientController {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // LISTAR
    public function index() {
        $stmt = $this->db->prepare("SELECT * FROM clientes");
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // CREAR
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            !isset($data['nombre_cliente']) ||
            !isset($data['tipo_cliente'])
        ) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO clientes (nombre_cliente, documento, tipo_cliente)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $data['nombre_cliente'],
            $data['documento'] ?? null,
            $data['tipo_cliente']
        ]);

        echo json_encode(["message" => "Cliente creado"]);
    }

    // VER POR ID
    public function show($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM clientes WHERE id_cliente = ?"
        );
        $stmt->execute([$id]);

        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            http_response_code(404);
            echo json_encode(["error" => "Cliente no encontrado"]);
            return;
        }

        echo json_encode($cliente);
    }

    // ACTUALIZAR
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        if (
            !isset($data['nombre_cliente']) ||
            !isset($data['tipo_cliente'])
        ) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        $stmt = $this->db->prepare(
            "UPDATE clientes
             SET nombre_cliente = ?, documento = ?, tipo_cliente = ?
             WHERE id_cliente = ?"
        );

        $stmt->execute([
            $data['nombre_cliente'],
            $data['documento'] ?? null,
            $data['tipo_cliente'],
            $id
        ]);

        echo json_encode(["message" => "Cliente actualizado"]);
    }

    // ELIMINAR
    public function destroy($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM clientes WHERE id_cliente = ?"
        );
        $stmt->execute([$id]);

        echo json_encode(["message" => "Cliente eliminado"]);
    }
}
