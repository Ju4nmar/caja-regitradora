<?php

class VentaController {

    private $db;
    private $IVA_PORCENTAJE = 0.19; // 19%

    public function __construct($db) {
        $this->db = $db;
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validación básica
        if (
            !isset($data['id_usuario']) ||
            !isset($data['id_cliente']) ||
            !isset($data['productos']) ||
            !is_array($data['productos']) ||
            count($data['productos']) === 0
        ) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos"]);
            return;
        }

        try {
            $this->db->beginTransaction();

            $subTotal = 0;
            $productosCalculados = [];

            // Validar stock y calcular subtotales
            foreach ($data['productos'] as $item) {

                $stmt = $this->db->prepare(
                    "SELECT precio, stock FROM productos WHERE id_producto = ?"
                );
                $stmt->execute([$item['id_producto']]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$producto) {
                    throw new Exception("Producto no encontrado: {$item['id_producto']}");
                }

                $cantidad = (int) $item['cantidad'];
                $precio   = (float) $producto['precio'];
                $stock    = (int) $producto['stock'];

                if ($stock < $cantidad) {
                    throw new Exception("Stock insuficiente para el producto {$item['id_producto']}");
                }

                $subTotalItem = $precio * $cantidad;
                $subTotal += $subTotalItem;

                $productosCalculados[] = [
                    "id_producto" => $item['id_producto'],
                    "cantidad"    => $cantidad,
                    "precio"      => $precio,
                    "sub_total"   => $subTotalItem
                ];
            }

            $iva   = $subTotal * $this->IVA_PORCENTAJE;
            $total = $subTotal + $iva;

            // Insertar venta
            $stmtVenta = $this->db->prepare(
                "INSERT INTO venta (id_usuario, id_cliente, sub_total, iva, total)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmtVenta->execute([
                $data['id_usuario'],
                $data['id_cliente'],
                $subTotal,
                $iva,
                $total
            ]);

            $idVenta = $this->db->lastInsertId();

            // Insertar detalle y actualizar stock
            $stmtDetalle = $this->db->prepare(
                "INSERT INTO detalle_venta
                 (id_venta, id_producto, cantidad, precio_unidad, sub_total)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmtStock = $this->db->prepare(
                "UPDATE productos
                 SET stock = stock - ?
                 WHERE id_producto = ?"
            );

            foreach ($productosCalculados as $p) {
                $stmtDetalle->execute([
                    $idVenta,
                    $p['id_producto'],
                    $p['cantidad'],
                    $p['precio'],
                    $p['sub_total']
                ]);

                $stmtStock->execute([
                    $p['cantidad'],
                    $p['id_producto']
                ]);
            }

            $this->db->commit();

            echo json_encode([
                "message"   => "Venta registrada correctamente",
                "id_venta"  => $idVenta,
                "sub_total" => $subTotal,
                "iva"       => $iva,
                "total"     => $total
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode([
                "error"   => "Error al registrar la venta",
                "detalle" => $e->getMessage()
            ]);
        }
    }

    public function index() {
        $stmt = $this->db->prepare(
            "SELECT v.id_venta, v.fecha, v.total,
                    c.nombre_cliente,
                    u.username
             FROM venta v
             JOIN clientes c ON v.id_cliente = c.id_cliente
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             ORDER BY v.fecha DESC"
        );

        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function show($id) {
        $stmtVenta = $this->db->prepare(
            "SELECT v.id_venta, v.fecha, v.sub_total, v.iva, v.total,
                    c.nombre_cliente,
                    u.username
             FROM venta v
             JOIN clientes c ON v.id_cliente = c.id_cliente
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             WHERE v.id_venta = ?"
        );

        $stmtVenta->execute([$id]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            http_response_code(404);
            echo json_encode(["error" => "Venta no encontrada"]);
            return;
        }

        $stmtDetalle = $this->db->prepare(
            "SELECT p.nombre_producto,
                    d.cantidad,
                    d.precio_unidad,
                    d.sub_total
             FROM detalle_venta d
             JOIN productos p ON d.id_producto = p.id_producto
             WHERE d.id_venta = ?"
        );

        $stmtDetalle->execute([$id]);
        $venta['detalle'] = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($venta);
    }
}
