<?php
class RoleMiddleware {

    public static function allow($roles = []) {
        $user = AuthMiddleware::verify();

        if (!in_array($user->rol, $roles)) {
            http_response_code(403);
            echo json_encode(["error" => "Acceso denegado"]);
            exit;
        }

        return $user;
    }
}

?>