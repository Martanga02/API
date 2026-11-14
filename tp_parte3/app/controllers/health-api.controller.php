<?php
// Endpoint liviano para los health checks (Kubernetes y Render): responde sin tocar la base de datos.
class HealthApiController {
    public function check($req, $res) {
        return $res->json(['status' => 'ok'], 200);
    }
}
