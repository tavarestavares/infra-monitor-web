<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$id = intval($_GET['id']);
$type = $_GET['type'];

if (!in_array($type, ['internal', 'external'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de serviço inválido']);
    exit;
}

$conn = getConnection();

$table = $type === 'internal' ? 'internal_services' : 'external_services';
$sql = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $service = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $service]);
} else {
    echo json_encode(['success' => false, 'message' => 'Serviço não encontrado']);
}

$stmt->close();
$conn->close();
?>