<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Validar campos obrigatórios
if (!isset($_POST['id']) || !isset($_POST['type']) || !isset($_POST['name']) || !isset($_POST['port']) || !isset($_POST['service_type'])) {
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
    exit;
}

$id = intval($_POST['id']);
$type = $_POST['type'];
$name = trim($_POST['name']);
$port = intval($_POST['port']);
$service_type = $_POST['service_type'];
$interval = intval($_POST['interval'] ?? 60);

if (!in_array($type, ['internal', 'external'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de serviço inválido']);
    exit;
}

$conn = getConnection();

if ($type === 'internal') {
    if (!isset($_POST['ip'])) {
        echo json_encode(['success' => false, 'message' => 'IP é obrigatório para serviços internos']);
        exit;
    }

    $ip = $_POST['ip'];

    // Validar IP
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo json_encode(['success' => false, 'message' => 'IP inválido']);
        exit;
    }

    $sql = "UPDATE internal_services SET name = ?, ip_address = ?, port = ?, service_type = ?, check_interval = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissi", $name, $ip, $port, $service_type, $interval, $id);
} else {
    if (!isset($_POST['domain'])) {
        echo json_encode(['success' => false, 'message' => 'Domínio é obrigatório para serviços externos']);
        exit;
    }

    $domain = $_POST['domain'];
    $ip_address = null;

    // Verificar se é um IP ou domínio
    if (filter_var($domain, FILTER_VALIDATE_IP)) {
        $ip_address = $domain;
        $domain = null;
    }

    $sql = "UPDATE external_services SET name = ?, domain = ?, ip_address = ?, port = ?, service_type = ?, check_interval = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisii", $name, $domain, $ip_address, $port, $service_type, $interval, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Serviço atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar serviço: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>