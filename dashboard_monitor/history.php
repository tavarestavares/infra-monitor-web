<?php
require_once 'includes/config.php';

$conn = getConnection();

// Filtros
$service_filter = $_GET['service'] ?? '';
$type_filter = $_GET['type'] ?? '';
$limit = 100;

// Construir query
$where = [];
$params = [];
$types = '';

if ($service_filter) {
    $where[] = "h.service_id = ?";
    $params[] = $service_filter;
    $types .= 'i';
}

if ($type_filter) {
    $where[] = "h.service_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar histórico
$sql = "SELECT h.*, 
        CASE 
            WHEN h.service_type = 'internal' THEN i.name 
            ELSE e.name 
        END as service_name
        FROM check_history h
        LEFT JOIN internal_services i ON h.service_type = 'internal' AND h.service_id = i.id
        LEFT JOIN external_services e ON h.service_type = 'external' AND h.service_id = e.id
        $whereClause
        ORDER BY h.checked_at DESC
        LIMIT $limit";

$history = [];
if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

// Buscar lista de serviços para filtro
$services = [];
$result = $conn->query("SELECT id, name, 'internal' as type FROM internal_services 
                        UNION ALL 
                        SELECT id, name, 'external' as type FROM external_services 
                        ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100">
    <!-- Header -->
    <header class="bg-gray-800 shadow-lg border-b border-gray-700">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-history text-purple-400 text-2xl"></i>
                    <h1 class="text-2xl font-bold text-white">Histórico de Verificações</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-6">
        <!-- Filtros -->
        <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium mb-2">Serviço</label>
                    <select name="service" class="w-full bg-gray-700 rounded px-3 py-2">
                        <option value="">Todos os serviços</option>
                        <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service['id']; ?>" <?php echo $service_filter == $service['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['name']); ?> (<?php echo ucfirst($service['type']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Tipo</label>
                    <select name="type" class="bg-gray-700 rounded px-3 py-2">
                        <option value="">Todos</option>
                        <option value="internal" <?php echo $type_filter == 'internal' ? 'selected' : ''; ?>>Interno</option>
                        <option value="external" <?php echo $type_filter == 'external' ? 'selected' : ''; ?>>Externo</option>
                    </select>
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded transition">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="history.php" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded transition">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </form>
        </div>

        <!-- Gráfico de Tempo de Resposta -->
        <?php if ($service_filter): ?>
        <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
            <h2 class="text-lg font-bold mb-4">Gráfico de Tempo de Resposta</h2>
            <canvas id="responseChart" height="100"></canvas>
        </div>
        <?php endif; ?>

        <!-- Tabela de Histórico -->
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left">Data/Hora</th>
                        <th class="px-4 py-2 text-left">Serviço</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Tempo de Resposta</th>
                        <th class="px-4 py-2 text-left">Erro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                    <tr class="border-t border-gray-700 hover:bg-gray-750">
                        <td class="px-4 py-2 text-sm"><?php echo $item['checked_at']; ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($item['service_name']); ?></td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs bg-gray-700">
                                <?php echo ucfirst($item['service_type']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs <?php echo $item['status'] == 'online' ? 'bg-green-600' : 'bg-red-600'; ?>">
                                <?php echo strtoupper($item['status']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-2"><?php echo formatResponseTime($item['response_time']); ?></td>
                        <td class="px-4 py-2 text-sm text-gray-400"><?php echo $item['error_message'] ?: '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($history)): ?>
        <div class="bg-gray-800 rounded-lg p-8 text-center border border-gray-700 mt-6">
            <i class="fas fa-inbox text-gray-600 text-4xl mb-4"></i>
            <p class="text-gray-400">Nenhum registro encontrado</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($service_filter && !empty($history)): ?>
    <script>
        // Preparar dados para o gráfico
        const chartData = <?php 
            $chartLabels = [];
            $responseData = [];
            $statusData = [];

            foreach (array_reverse($history) as $item) {
                $chartLabels[] = date('H:i', strtotime($item['checked_at']));
                $responseData[] = $item['response_time'];
                $statusData[] = $item['status'] == 'online' ? 1 : 0;
            }

            echo json_encode([
                'labels' => $chartLabels,
                'response' => $responseData,
                'status' => $statusData
            ]);
        ?>;

        // Criar gráfico
        const ctx = document.getElementById('responseChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Tempo de Resposta (ms)',
                    data: chartData.response,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#9CA3AF'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    },
                    x: {
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>