<?php
class ProductController {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    
    public function index() {
        $stmt = $this->db->query("SELECT * FROM productos");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $this->db->prepare(
            "INSERT INTO productos (nombre_producto, stock, precio)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre_producto'],
            $data['stock'],
            $data['precio']
        ]);

        echo json_encode(["message" => "Producto creado"]);
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $this->db->prepare(
            "UPDATE productos
             SET nombre_producto=?, stock=?, precio=?
             WHERE id_producto=?"
        );
        $stmt->execute([
            $data['nombre_producto'],
            $data['stock'],
            $data['precio'],
            $id
        ]);

        echo json_encode(["message" => "Producto actualizado"]);
    }

    public function destroy($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM productos WHERE id_producto=?"
        );
        $stmt->execute([$id]);

        echo json_encode(["message" => "Producto eliminado"]);
    }
}
