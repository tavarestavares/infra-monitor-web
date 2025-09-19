<?php
// Script para ser executado via cron a cada minuto
require_once 'includes/config.php';

$conn = getConnection();

// Verificar serviços internos que precisam ser checados
$sql = "SELECT * FROM internal_services 
        WHERE last_check IS NULL 
        OR TIMESTAMPDIFF(SECOND, last_check, NOW()) >= check_interval";

$result = $conn->query($sql);
while ($service = $result->fetch_assoc()) {
    // Verificar serviço
    $check = checkService($service['ip_address'], $service['port']);

    // Atualizar status
    $stmt = $conn->prepare("UPDATE internal_services SET status = ?, last_check = NOW(), response_time = ? WHERE id = ?");
    $stmt->bind_param("sii", $check['status'], $check['response_time'], $service['id']);
    $stmt->execute();

    // Registrar no histórico
    $stmt = $conn->prepare("INSERT INTO check_history (service_id, service_type, status, response_time, error_message) VALUES (?, 'internal', ?, ?, ?)");
    $error = $check['error'] ?? null;
    $stmt->bind_param("isis", $service['id'], $check['status'], $check['response_time'], $error);
    $stmt->execute();

    // Gerenciar alertas
    if ($check['status'] === 'offline') {
        // Verificar se já existe alerta ativo
        $stmt = $conn->prepare("SELECT id FROM alerts WHERE service_id = ? AND service_type = 'internal' AND resolved = 0");
        $stmt->bind_param("i", $service['id']);
        $stmt->execute();
        $alertResult = $stmt->get_result();

        if ($alertResult->num_rows == 0) {
            // Criar novo alerta
            $message = "Serviço {$service['name']} ({$service['ip_address']}:{$service['port']}) está offline";
            $stmt = $conn->prepare("INSERT INTO alerts (service_id, service_type, alert_type, message) VALUES (?, 'internal', 'offline', ?)");
            $stmt->bind_param("is", $service['id'], $message);
            $stmt->execute();
        }
    } else {
        // Resolver alertas se serviço voltou
        $stmt = $conn->prepare("UPDATE alerts SET resolved = 1, resolved_at = NOW() WHERE service_id = ? AND service_type = 'internal' AND resolved = 0");
        $stmt->bind_param("i", $service['id']);
        $stmt->execute();
    }
}

// Verificar serviços externos que precisam ser checados
$sql = "SELECT * FROM external_services 
        WHERE last_check IS NULL 
        OR TIMESTAMPDIFF(SECOND, last_check, NOW()) >= check_interval";

$result = $conn->query($sql);
while ($service = $result->fetch_assoc()) {
    // Determinar host e porta
    $host = $service['domain'] ?? $service['ip_address'];
    $port = $service['port'];

    if ($service['service_type'] === 'HTTPS') {
        $port = 443;
    }

    // Verificar serviço
    $check = checkDomain($host, $port);

    // Atualizar status
    $ssl_expiry = isset($check['ssl_expiry']) ? $check['ssl_expiry'] : null;
    $stmt = $conn->prepare("UPDATE external_services SET status = ?, last_check = NOW(), response_time = ?, ssl_expiry = ? WHERE id = ?");
    $stmt->bind_param("sisi", $check['status'], $check['response_time'], $ssl_expiry, $service['id']);
    $stmt->execute();

    // Registrar no histórico
    $stmt = $conn->prepare("INSERT INTO check_history (service_id, service_type, status, response_time, error_message) VALUES (?, 'external', ?, ?, ?)");
    $error = $check['error'] ?? null;
    $stmt->bind_param("isis", $service['id'], $check['status'], $check['response_time'], $error);
    $stmt->execute();

    // Gerenciar alertas
    if ($check['status'] === 'offline') {
        // Verificar se já existe alerta ativo
        $stmt = $conn->prepare("SELECT id FROM alerts WHERE service_id = ? AND service_type = 'external' AND resolved = 0");
        $stmt->bind_param("i", $service['id']);
        $stmt->execute();
        $alertResult = $stmt->get_result();

        if ($alertResult->num_rows == 0) {
            // Criar novo alerta
            $message = "Serviço {$service['name']} ({$host}) está offline";
            $stmt = $conn->prepare("INSERT INTO alerts (service_id, service_type, alert_type, message) VALUES (?, 'external', 'offline', ?)");
            $stmt->bind_param("is", $service['id'], $message);
            $stmt->execute();
        }
    } else {
        // Resolver alertas se serviço voltou
        $stmt = $conn->prepare("UPDATE alerts SET resolved = 1, resolved_at = NOW() WHERE service_id = ? AND service_type = 'external' AND resolved = 0");
        $stmt->bind_param("i", $service['id']);
        $stmt->execute();
    }

    // Alerta de SSL expirando (30 dias)
    if ($ssl_expiry) {
        $days_until_expiry = (strtotime($ssl_expiry) - time()) / (60 * 60 * 24);

        if ($days_until_expiry <= 30 && $days_until_expiry > 0) {
            // Verificar se já existe alerta de SSL
            $stmt = $conn->prepare("SELECT id FROM alerts WHERE service_id = ? AND service_type = 'external' AND alert_type = 'ssl_expiry' AND resolved = 0");
            $stmt->bind_param("i", $service['id']);
            $stmt->execute();
            $alertResult = $stmt->get_result();

            if ($alertResult->num_rows == 0) {
                $message = "Certificado SSL de {$service['name']} expira em " . round($days_until_expiry) . " dias";
                $stmt = $conn->prepare("INSERT INTO alerts (service_id, service_type, alert_type, message) VALUES (?, 'external', 'ssl_expiry', ?)");
                $stmt->bind_param("is", $service['id'], $message);
                $stmt->execute();
            }
        }
    }
}

// Limpar histórico antigo (manter últimos 7 dias)
$conn->query("DELETE FROM check_history WHERE checked_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

// Limpar alertas resolvidos há mais de 30 dias
$conn->query("DELETE FROM alerts WHERE resolved = 1 AND resolved_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

$conn->close();

echo "Verificação concluída em " . date('Y-m-d H:i:s') . "
";
?>