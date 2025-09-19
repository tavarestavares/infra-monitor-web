<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$type = $_POST['type'] ?? '';
$name = $_POST['name'] ?? '';
$port = intval($_POST['port'] ?? 80);
$service_type = $_POST['service_type'] ?? 'HTTP';
$interval = intval($_POST['interval'] ?? 60);

$conn = getConnection();

try {
    if ($type === 'internal') {
        $ip = $_POST['ip'] ?? '';

        if (empty($name) || empty($ip)) {
            throw new Exception('Nome e IP são obrigatórios');
        }

        // Validar IP
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new Exception('IP inválido');
        }

        $stmt = $conn->prepare("INSERT INTO internal_services (name, ip_address, port, service_type, check_interval) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $name, $ip, $port, $service_type, $interval);

    } else {
        $domain = $_POST['domain'] ?? '';

        if (empty($name) || empty($domain)) {
            throw new Exception('Nome e domínio são obrigatórios');
        }

        // Se for IP, validar
        if (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip_address = $domain;
            $domain = null;
        } else {
            $ip_address = null;
        }

        $stmt = $conn->prepare("INSERT INTO external_services (name, domain, ip_address, port, service_type, check_interval) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisi", $name, $domain, $ip_address, $port, $service_type, $interval);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Serviço adicionado com sucesso']);
    } else {
        throw new Exception('Erro ao adicionar serviço');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>