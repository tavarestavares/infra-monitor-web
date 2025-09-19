<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';

if ($id <= 0 || !in_array($type, ['internal', 'external'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$conn = getConnection();

try {
    // Buscar serviço
    if ($type === 'internal') {
        $stmt = $conn->prepare("SELECT * FROM internal_services WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();

        if (!$service) {
            throw new Exception('Serviço não encontrado');
        }

        // Verificar serviço
        $check = checkService($service['ip_address'], $service['port']);

        // Atualizar status
        $stmt = $conn->prepare("UPDATE internal_services SET status = ?, last_check = NOW(), response_time = ? WHERE id = ?");
        $stmt->bind_param("sii", $check['status'], $check['response_time'], $id);
        $stmt->execute();

    } else {
        $stmt = $conn->prepare("SELECT * FROM external_services WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();

        if (!$service) {
            throw new Exception('Serviço não encontrado');
        }

        // Verificar serviço
        $host = $service['domain'] ?? $service['ip_address'];
        $port = $service['port'];

        if ($service['service_type'] === 'HTTPS') {
            $port = 443;
        }

        $check = checkDomain($host, $port);

        // Atualizar status
        $ssl_expiry = isset($check['ssl_expiry']) ? $check['ssl_expiry'] : null;
        $stmt = $conn->prepare("UPDATE external_services SET status = ?, last_check = NOW(), response_time = ?, ssl_expiry = ? WHERE id = ?");
        $stmt->bind_param("sisi", $check['status'], $check['response_time'], $ssl_expiry, $id);
        $stmt->execute();
    }

    // Registrar no histórico
    $stmt = $conn->prepare("INSERT INTO check_history (service_id, service_type, status, response_time, error_message) VALUES (?, ?, ?, ?, ?)");
    $error = $check['error'] ?? null;
    $stmt->bind_param("issis", $id, $type, $check['status'], $check['response_time'], $error);
    $stmt->execute();

    // Criar alerta se offline
    if ($check['status'] === 'offline') {
        $message = "Serviço {$service['name']} está offline";
        $stmt = $conn->prepare("INSERT INTO alerts (service_id, service_type, alert_type, message) VALUES (?, ?, 'offline', ?)");
        $stmt->bind_param("iss", $id, $type, $message);
        $stmt->execute();
    }

    echo json_encode([
        'success' => true, 
        'status' => $check['status'],
        'response_time' => $check['response_time']
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>