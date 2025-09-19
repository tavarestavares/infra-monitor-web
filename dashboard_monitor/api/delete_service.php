<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $_GET = array_merge($_GET, $_DELETE);
}

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';

if ($id <= 0 || !in_array($type, ['internal', 'external'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$conn = getConnection();

try {
    // Deletar histórico
    $stmt = $conn->prepare("DELETE FROM check_history WHERE service_id = ? AND service_type = ?");
    $stmt->bind_param("is", $id, $type);
    $stmt->execute();

    // Deletar alertas
    $stmt = $conn->prepare("DELETE FROM alerts WHERE service_id = ? AND service_type = ?");
    $stmt->bind_param("is", $id, $type);
    $stmt->execute();

    // Deletar serviço
    if ($type === 'internal') {
        $stmt = $conn->prepare("DELETE FROM internal_services WHERE id = ?");
    } else {
        $stmt = $conn->prepare("DELETE FROM external_services WHERE id = ?");
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Serviço excluído com sucesso']);
    } else {
        throw new Exception('Erro ao excluir serviço');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>