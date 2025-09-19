<?php
require_once 'includes/config.php';

// Obter estatísticas
$conn = getConnection();

// Contar serviços
$stats = [];
$result = $conn->query("SELECT 
    (SELECT COUNT(*) FROM internal_services WHERE status = 'online') as online_internal,
    (SELECT COUNT(*) FROM internal_services WHERE status = 'offline') as offline_internal,
    (SELECT COUNT(*) FROM external_services WHERE status = 'online') as online_external,
    (SELECT COUNT(*) FROM external_services WHERE status = 'offline') as offline_external");
$stats = $result->fetch_assoc();

// Obter serviços internos
$internal_services = [];
$result = $conn->query("SELECT * FROM internal_services ORDER BY status DESC, name");
while ($row = $result->fetch_assoc()) {
    $internal_services[] = $row;
}

// Obter serviços externos
$external_services = [];
$result = $conn->query("SELECT * FROM external_services ORDER BY status DESC, name");
while ($row = $result->fetch_assoc()) {
    $external_services[] = $row;
}

// Combinar todos os serviços para exibição unificada
$all_services = [];
foreach ($internal_services as $service) {
    $service['type_category'] = 'internal';
    $all_services[] = $service;
}
foreach ($external_services as $service) {
    $service['type_category'] = 'external';
    $all_services[] = $service;
}

// Ordenar por status (offline primeiro) e depois por nome
usort($all_services, function($a, $b) {
    if ($a['status'] == $b['status']) {
        return strcmp($a['name'], $b['name']);
    }
    return $a['status'] == 'offline' ? -1 : 1;
});

// Função para calcular tempo offline
function getOfflineTime($lastOnline) {
    if (empty($lastOnline)) return 'Desconhecido';

    $diff = time() - strtotime($lastOnline);

    if ($diff < 60) {
        return $diff . 's';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'min';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
    } else {
        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        return $days . 'd' . ($hours > 0 ? ' ' . $hours . 'h' : '');
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Monitor de Serviços</title>

    <!-- DaisyUI + Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.24/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Container auto-ajustável */
        .services-grid {
            display: grid;
            gap: 0.75rem;
            padding: 0.75rem;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }

        /* Grid responsivo baseado em viewport */
        @media (min-width: 640px) and (max-width: 767px) {
            .services-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .services-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (min-width: 1024px) and (max-width: 1279px) {
            .services-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (min-width: 1280px) and (max-width: 1535px) {
            .services-grid { grid-template-columns: repeat(5, 1fr); }
        }

        @media (min-width: 1536px) and (max-width: 1919px) {
            .services-grid { grid-template-columns: repeat(6, 1fr); }
        }

        @media (min-width: 1920px) {
            .services-grid { grid-template-columns: repeat(8, 1fr); }
        }

        /* Para telas muito pequenas */
        @media (max-width: 639px) {
            .services-grid { 
                grid-template-columns: repeat(1, 1fr);
                padding: 0.5rem;
            }
        }

        /* Cards auto-ajustáveis */
        .service-card {
            display: flex;
            flex-direction: column;
            height: fit-content;
            min-height: 160px;
            transition: all 0.2s;
        }

        .service-card:hover {
            transform: scale(1.02);
            z-index: 10;
        }

        /* Cards offline com destaque */
        .service-card.offline {
            animation: pulse-red 2s infinite;
            border: 2px solid rgba(239, 68, 68, 0.5);
        }

        @keyframes pulse-red {
            0%, 100% { 
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            70% { 
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
        }

        /* Status dots */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.online {
            background: #22c55e;
        }

        .status-dot.offline {
            background: #ef4444;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.3; }
        }

        /* Navbar compacta */
        .navbar {
            min-height: 3.5rem;
            height: 3.5rem;
        }

        /* Alert sonoro visual */
        .sound-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        .sound-indicator.active {
            display: flex;
            animation: soundPulse 1s ease-out;
        }

        @keyframes soundPulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        /* Scrollbar customizada */
        .services-grid::-webkit-scrollbar {
            width: 6px;
        }

        .services-grid::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .services-grid::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 3px;
        }

        .services-grid::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.5);
        }

        /* Timer de tempo offline */
        .offline-timer {
            font-size: 0.7rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #ef4444;
            margin-top: 0.25rem;
        }

        /* Botões de ação menores */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            justify-content: flex-end;
            margin-top: auto;
            padding-top: 0.5rem;
        }
    </style>
</head>
<body class="min-h-screen bg-base-200 overflow-hidden">
    <!-- Navbar Compacta -->
    <div class="navbar bg-base-100 shadow-lg fixed top-0 z-50">
        <div class="flex-1">
            <a class="btn btn-ghost btn-sm normal-case text-lg">
                <i class="fas fa-server text-primary"></i>
                <span class="ml-2"><?php echo SITE_NAME; ?></span>
            </a>
        </div>
        <div class="flex-none gap-2">
            <!-- Contador de status com alerta -->
            <div class="flex items-center gap-3 mr-3 text-sm">
                <div class="flex items-center gap-1">
                    <span class="status-dot online"></span>
                    <span class="text-success font-bold">
                        <?php echo $stats['online_internal'] + $stats['online_external']; ?>
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="status-dot offline"></span>
                    <span class="text-error font-bold" id="offlineCount">
                        <?php echo $stats['offline_internal'] + $stats['offline_external']; ?>
                    </span>
                    <?php if (($stats['offline_internal'] + $stats['offline_external']) > 0): ?>
                    <i class="fas fa-exclamation-triangle text-warning animate-pulse"></i>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Som toggle -->
            <div class="tooltip tooltip-bottom" data-tip="Som de Alerta">
                <button id="soundToggle" class="btn btn-ghost btn-circle btn-sm">
                    <i class="fas fa-volume-up" id="soundIcon"></i>
                </button>
            </div>

            <!-- Auto-refresh -->
            <div class="badge badge-outline badge-sm">
                <i class="far fa-clock mr-1 text-xs"></i>
                <span id="countdown" class="text-xs"><?php echo AUTO_REFRESH; ?></span>s
            </div>

            <!-- Botões -->
            <button onclick="location.reload()" class="btn btn-sm btn-circle btn-ghost">
                <i class="fas fa-sync-alt text-sm"></i>
            </button>

            <button onclick="openModal()" class="btn btn-sm btn-primary">
                <i class="fas fa-plus text-sm"></i>
            </button>

            <!-- Botão Sobre -->
            <a href="about.php" class="btn btn-sm btn-ghost">
                <i class="fas fa-info-circle text-sm"></i>
                <span class="hidden sm:inline ml-1">Sobre</span>
            </a>

            <!-- Theme toggle -->
            <label class="swap swap-rotate">
                <input type="checkbox" id="theme-toggle" />
                <svg class="swap-on fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/>
                </svg>
                <svg class="swap-off fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/>
                </svg>
            </label>
        </div>
    </div>

    <!-- Grid de Serviços -->
    <div class="services-grid mt-14">
        <?php foreach ($all_services as $service): ?>
        <div class="card bg-base-100 shadow-md service-card <?php echo $service['status'] == 'offline' ? 'offline' : ''; ?>" 
             data-service-id="<?php echo $service['id']; ?>" 
             data-service-type="<?php echo $service['type_category']; ?>"
             data-status="<?php echo $service['status']; ?>"
             data-last-online="<?php echo $service['last_online'] ?? ''; ?>">
            <div class="card-body p-3">
                <!-- Nome do Serviço -->
                <h3 class="font-semibold text-sm mb-2 truncate" title="<?php echo htmlspecialchars($service['name']); ?>">
                    <?php echo htmlspecialchars($service['name']); ?>
                </h3>

                <!-- Informações em lista compacta -->
                <div class="space-y-1 text-xs">
                    <!-- IP/Domínio e Porta -->
                    <div class="flex items-center gap-1">
                        <i class="fas fa-<?php echo $service['type_category'] == 'internal' ? 'server' : 'globe'; ?> text-primary w-3"></i>
                        <span class="font-mono truncate" title="<?php echo $service['type_category'] == 'internal' ? $service['ip_address'] : ($service['domain'] ?? $service['ip_address']); ?>">
                            <?php 
                            if ($service['type_category'] == 'internal') {
                                echo $service['ip_address'] . ':' . $service['port'];
                            } else {
                                $domain = $service['domain'] ?? $service['ip_address'];
                                echo (strlen($domain) > 15 ? substr($domain, 0, 15) . '...' : $domain) . ':' . $service['port'];
                            }
                            ?>
                        </span>
                    </div>

                    <!-- Protocolo -->
                    <div class="flex items-center gap-1">
                        <i class="fas fa-tag text-secondary w-3"></i>
                        <span><?php echo $service['service_type']; ?></span>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-1">
                        <div class="badge <?php echo $service['status'] == 'online' ? 'badge-success' : 'badge-error'; ?> badge-xs gap-1">
                            <span class="status-dot <?php echo $service['status']; ?>"></span>
                            <?php echo strtoupper($service['status']); ?>
                        </div>
                    </div>

                    <!-- Tempo Offline (apenas se estiver offline) -->
                    <?php if ($service['status'] == 'offline'): ?>
                    <div class="offline-timer" data-offline-since="<?php echo $service['last_online'] ?? ''; ?>">
                        <i class="fas fa-clock"></i>
                        <span class="offline-time-text">
                            Offline há: <?php echo getOfflineTime($service['last_online'] ?? ''); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Ações -->
                <div class="action-buttons">
                    <button onclick="editService(<?php echo $service['id']; ?>, '<?php echo $service['type_category']; ?>')" 
                            class="btn btn-xs btn-ghost tooltip" data-tip="Editar">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="checkService(<?php echo $service['id']; ?>, '<?php echo $service['type_category']; ?>')" 
                            class="btn btn-xs btn-primary tooltip" data-tip="Verificar">
                        <i class="fas fa-sync text-xs"></i>
                    </button>
                    <button onclick="deleteService(<?php echo $service['id']; ?>, '<?php echo $service['type_category']; ?>')" 
                            class="btn btn-xs btn-error tooltip" data-tip="Excluir">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Indicador Visual de Som -->
    <div class="sound-indicator badge badge-warning gap-2" id="soundIndicator">
        <i class="fas fa-volume-up"></i>
        <span>ALERTA SONORO</span>
    </div>

    <!-- Modal Adicionar/Editar Serviço -->
    <dialog id="serviceModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">
                <i class="fas fa-plus-circle text-success" id="modalIcon"></i> 
                <span id="modalTitle">Adicionar Novo Serviço</span>
            </h3>

            <form id="serviceForm">
                <input type="hidden" id="editServiceId" value="">
                <input type="hidden" id="isEdit" value="false">

                <!-- Tipo de Serviço -->
                <div class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Tipo de Serviço</span>
                    </label>
                    <select id="serviceType" class="select select-bordered select-sm" onchange="toggleServiceFields()">
                        <option value="internal">Interno (IP Local)</option>
                        <option value="external">Externo (Domínio/IP)</option>
                    </select>
                </div>

                <!-- Nome -->
                <div class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Nome do Serviço</span>
                    </label>
                    <input type="text" id="serviceName" placeholder="Ex: Servidor Web" 
                           class="input input-bordered input-sm" required>
                </div>

                <!-- IP (Interno) -->
                <div id="ipField" class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Endereço IP</span>
                    </label>
                    <input type="text" id="serviceIP" placeholder="Ex: 10.10.110.64" 
                           class="input input-bordered input-sm">
                </div>

                <!-- Domínio (Externo) -->
                <div id="domainField" class="form-control w-full mb-3 hidden">
                    <label class="label py-1">
                        <span class="label-text">Domínio ou IP Externo</span>
                    </label>
                    <input type="text" id="serviceDomain" placeholder="Ex: google.com" 
                           class="input input-bordered input-sm">
                </div>

                <!-- Porta -->
                <div class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Porta</span>
                    </label>
                    <input type="number" id="servicePort" placeholder="80" value="80" 
                           class="input input-bordered input-sm" min="1" max="65535">
                </div>

                <!-- Protocolo -->
                <div class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Protocolo</span>
                    </label>
                    <select id="protocolType" class="select select-bordered select-sm">
                        <option value="HTTP">HTTP</option>
                        <option value="HTTPS">HTTPS</option>
                        <option value="MySQL">MySQL</option>
                        <option value="SSH">SSH</option>
                        <option value="FTP">FTP</option>
                        <option value="DNS">DNS</option>
                        <option value="SMTP">SMTP</option>
                        <option value="Other">Outro</option>
                    </select>
                </div>

                <!-- Intervalo -->
                <div class="form-control w-full mb-3">
                    <label class="label py-1">
                        <span class="label-text">Intervalo de Verificação</span>
                    </label>
                    <select id="checkInterval" class="select select-bordered select-sm">
                        <option value="30">30 segundos</option>
                        <option value="60" selected>1 minuto</option>
                        <option value="300">5 minutos</option>
                        <option value="600">10 minutos</option>
                        <option value="1800">30 minutos</option>
                        <option value="3600">1 hora</option>
                    </select>
                </div>

                <!-- Botões -->
                <div class="modal-action">
                    <button type="button" onclick="closeModal()" class="btn btn-sm">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-save"></i> <span id="submitButtonText">Adicionar</span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

    <!-- Toast Container -->
    <div class="toast toast-end">
        <div id="toast-success" class="alert alert-success hidden">
            <i class="fas fa-check-circle"></i>
            <span id="toast-success-message">Sucesso!</span>
        </div>
        <div id="toast-error" class="alert alert-error hidden">
            <i class="fas fa-exclamation-circle"></i>
            <span id="toast-error-message">Erro!</span>
        </div>
    </div>

    <!-- Audio para alertas -->
    <audio id="alertSound" preload="auto">
        <source src="alert.mp3" type="audio/mpeg">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE" type="audio/wav">
    </audio>

    <!-- Scripts -->
    <script>
        // Configurações de som
        let soundEnabled = localStorage.getItem('soundEnabled') !== 'false';
        let lastOfflineServices = new Set();
        let lastAlertTime = 0;
        let periodicAlertInterval = null;

        // Estado inicial dos serviços offline
        document.querySelectorAll('.service-card[data-status="offline"]').forEach(card => {
            lastOfflineServices.add(card.dataset.serviceId + '-' + card.dataset.serviceType);
        });

        // Função para atualizar tempo offline
        function updateOfflineTimes() {
            document.querySelectorAll('.offline-timer').forEach(timer => {
                const offlineSince = timer.dataset.offlineSince;
                if (!offlineSince || offlineSince === '') {
                    timer.querySelector('.offline-time-text').textContent = 'Offline há: Desconhecido';
                    return;
                }

                const diff = Math.floor((Date.now() - new Date(offlineSince).getTime()) / 1000);
                let timeText = '';

                if (diff < 60) {
                    timeText = diff + 's';
                } else if (diff < 3600) {
                    timeText = Math.floor(diff / 60) + 'min';
                } else if (diff < 86400) {
                    const hours = Math.floor(diff / 3600);
                    const minutes = Math.floor((diff % 3600) / 60);
                    timeText = hours + 'h' + (minutes > 0 ? ' ' + minutes + 'min' : '');
                } else {
                    const days = Math.floor(diff / 86400);
                    const hours = Math.floor((diff % 86400) / 3600);
                    timeText = days + 'd' + (hours > 0 ? ' ' + hours + 'h' : '');
                }

                timer.querySelector('.offline-time-text').textContent = 'Offline há: ' + timeText;
            });
        }

        // Atualizar tempos offline a cada segundo
        setInterval(updateOfflineTimes, 1000);

        // Função para tocar som de alerta
        function playAlertSound() {
            if (!soundEnabled) return;

            const audio = document.getElementById('alertSound');
            const indicator = document.getElementById('soundIndicator');

            // Tentar diferentes métodos para tocar o som
            try {
                // Método 1: Audio element
                audio.currentTime = 0;
                audio.play().catch(e => {
                    // Método 2: Web Audio API
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    oscillator.frequency.value = 800; // Frequência do beep
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);
                });

                // Mostrar indicador visual
                indicator.classList.add('active');
                setTimeout(() => {
                    indicator.classList.remove('active');
                }, 1000);

            } catch (e) {
                console.log('Erro ao tocar som:', e);
            }
        }

        // Toggle do som
        document.getElementById('soundToggle').addEventListener('click', function() {
            soundEnabled = !soundEnabled;
            localStorage.setItem('soundEnabled', soundEnabled);
            const icon = document.getElementById('soundIcon');

            if (soundEnabled) {
                icon.className = 'fas fa-volume-up';
                showToast('success', 'Som ativado');
                // Tocar som de teste
                playAlertSound();
            } else {
                icon.className = 'fas fa-volume-mute';
                showToast('success', 'Som desativado');
            }
        });

        // Atualizar ícone do som baseado no estado salvo
        if (!soundEnabled) {
            document.getElementById('soundIcon').className = 'fas fa-volume-mute';
        }

        // Função para verificar novos serviços offline
        function checkForNewOfflineServices() {
            const currentOfflineServices = new Set();
            let hasNewOffline = false;

            document.querySelectorAll('.service-card[data-status="offline"]').forEach(card => {
                const serviceKey = card.dataset.serviceId + '-' + card.dataset.serviceType;
                currentOfflineServices.add(serviceKey);

                // Verificar se é um novo serviço offline
                if (!lastOfflineServices.has(serviceKey)) {
                    hasNewOffline = true;
                    console.log('Novo serviço offline detectado:', card.querySelector('h3').textContent);
                }
            });

            // Tocar alerta se houver novo serviço offline
            if (hasNewOffline) {
                playAlertSound();
                lastAlertTime = Date.now();
            }

            // Atualizar lista de serviços offline
            lastOfflineServices = currentOfflineServices;

            // Configurar alerta periódico se houver serviços offline
            if (currentOfflineServices.size > 0) {
                startPeriodicAlert();
            } else {
                stopPeriodicAlert();
            }
        }

        // Função para iniciar alerta periódico (30 minutos)
        function startPeriodicAlert() {
            if (periodicAlertInterval) return; // Já está rodando

            periodicAlertInterval = setInterval(() => {
                const offlineCount = document.querySelectorAll('.service-card[data-status="offline"]').length;
                if (offlineCount > 0) {
                    console.log('Alerta periódico: ' + offlineCount + ' serviços offline');
                    playAlertSound();
                }
            }, 30 * 60 * 1000); // 30 minutos
        }

        // Função para parar alerta periódico
        function stopPeriodicAlert() {
            if (periodicAlertInterval) {
                clearInterval(periodicAlertInterval);
                periodicAlertInterval = null;
            }
        }

        // Verificar status inicial
        checkForNewOfflineServices();

        // Countdown para auto-refresh com verificação de mudanças
        let countdown = <?php echo AUTO_REFRESH; ?>;
        const countdownInterval = setInterval(function() {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            if (countdown <= 0) {
                // Fazer verificação antes de recarregar
                checkAllServices().then(() => {
                    location.reload();
                });
            }
        }, 1000);

        // Função para verificar todos os serviços
        async function checkAllServices() {
            const services = document.querySelectorAll('.service-card');
            const promises = [];

            services.forEach(card => {
                const id = card.dataset.serviceId;
                const type = card.dataset.serviceType;

                const promise = fetch(`api/check_service.php?id=${id}&type=${type}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            const newStatus = result.status;
                            const oldStatus = card.dataset.status;

                            if (oldStatus !== newStatus) {
                                card.dataset.status = newStatus;

                                // Atualizar visual do card
                                if (newStatus === 'offline') {
                                    card.classList.add('offline');
                                } else {
                                    card.classList.remove('offline');
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Erro ao verificar serviço:', error));

                promises.push(promise);
            });

            await Promise.all(promises);
            checkForNewOfflineServices();
        }

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });

        // Carregar tema salvo
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        themeToggle.checked = savedTheme === 'light';

        // Funções do Modal
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Adicionar Novo Serviço';
            document.getElementById('modalIcon').className = 'fas fa-plus-circle text-success';
            document.getElementById('submitButtonText').textContent = 'Adicionar';
            document.getElementById('isEdit').value = 'false';
            document.getElementById('editServiceId').value = '';
            document.getElementById('serviceForm').reset();
            toggleServiceFields();
            document.getElementById('serviceModal').showModal();
        }

        function closeModal() {
            document.getElementById('serviceModal').close();
        }

        // Função para editar serviço
        async function editService(id, type) {
            try {
                // Buscar dados do serviço
                const response = await fetch(`api/get_service.php?id=${id}&type=${type}`);
                const service = await response.json();

                if (service.success) {
                    // Configurar modal para edição
                    document.getElementById('modalTitle').textContent = 'Editar Serviço';
                    document.getElementById('modalIcon').className = 'fas fa-edit text-warning';
                    document.getElementById('submitButtonText').textContent = 'Salvar Alterações';
                    document.getElementById('isEdit').value = 'true';
                    document.getElementById('editServiceId').value = id;

                    // Preencher campos
                    document.getElementById('serviceType').value = type;
                    document.getElementById('serviceName').value = service.data.name;
                    document.getElementById('servicePort').value = service.data.port;
                    document.getElementById('protocolType').value = service.data.service_type;
                    document.getElementById('checkInterval').value = service.data.check_interval || 60;

                    if (type === 'internal') {
                        document.getElementById('serviceIP').value = service.data.ip_address;
                    } else {
                        document.getElementById('serviceDomain').value = service.data.domain || service.data.ip_address;
                    }

                    toggleServiceFields();
                    document.getElementById('serviceModal').showModal();
                } else {
                    showToast('error', 'Erro ao carregar dados do serviço');
                }
            } catch (error) {
                showToast('error', 'Erro de conexão');
            }
        }

        // Alternar campos do formulário
        function toggleServiceFields() {
            const type = document.getElementById('serviceType').value;
            const ipField = document.getElementById('ipField');
            const domainField = document.getElementById('domainField');

            if (type === 'internal') {
                ipField.classList.remove('hidden');
                domainField.classList.add('hidden');
                document.getElementById('serviceIP').required = true;
                document.getElementById('serviceDomain').required = false;
            } else {
                ipField.classList.add('hidden');
                domainField.classList.remove('hidden');
                document.getElementById('serviceIP').required = false;
                document.getElementById('serviceDomain').required = true;
            }
        }

        // Toast notifications
        function showToast(type, message) {
            const toastElement = document.getElementById(`toast-${type}`);
            const messageElement = document.getElementById(`toast-${type}-message`);

            messageElement.textContent = message;
            toastElement.classList.remove('hidden');

            setTimeout(() => {
                toastElement.classList.add('hidden');
            }, 3000);
        }

        // Submeter formulário
        document.getElementById('serviceForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = e.target.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Salvando...';

            const formData = new FormData();
            const type = document.getElementById('serviceType').value;
            const isEdit = document.getElementById('isEdit').value === 'true';

            formData.append('type', type);
            formData.append('name', document.getElementById('serviceName').value);
            formData.append('port', document.getElementById('servicePort').value);
            formData.append('service_type', document.getElementById('protocolType').value);
            formData.append('interval', document.getElementById('checkInterval').value);

            if (isEdit) {
                formData.append('id', document.getElementById('editServiceId').value);
            }

            if (type === 'internal') {
                formData.append('ip', document.getElementById('serviceIP').value);
            } else {
                formData.append('domain', document.getElementById('serviceDomain').value);
            }

            try {
                const url = isEdit ? 'api/update_service.php' : 'api/add_service.php';
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast('success', isEdit ? 'Serviço atualizado!' : 'Serviço adicionado!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', result.message || 'Erro ao salvar');
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save"></i> ' + (isEdit ? 'Salvar Alterações' : 'Adicionar');
                }
            } catch (error) {
                showToast('error', 'Erro de conexão');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save"></i> ' + (isEdit ? 'Salvar Alterações' : 'Adicionar');
            }
        });

        // Verificar serviço individual
        async function checkService(id, type) {
            const button = event.target.closest('button');
            const card = button.closest('.service-card');
            const originalHTML = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

            try {
                const response = await fetch(`api/check_service.php?id=${id}&type=${type}`);
                const result = await response.json();

                if (result.success) {
                    const oldStatus = card.dataset.status;
                    const newStatus = result.status;

                    // Atualizar status do card
                    card.dataset.status = newStatus;

                    // Atualizar visual
                    if (newStatus === 'offline' && oldStatus === 'online') {
                        card.classList.add('offline');
                        playAlertSound(); // Tocar som imediatamente se ficou offline
                    } else if (newStatus === 'online' && oldStatus === 'offline') {
                        card.classList.remove('offline');
                    }

                    showToast('success', `${newStatus.toUpperCase()} - ${result.response_time}ms`);

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast('error', result.message);
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                }
            } catch (error) {
                showToast('error', 'Erro ao verificar');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        }

        // Excluir serviço
        async function deleteService(id, type) {
            if (!confirm('Confirma a exclusão deste serviço?')) {
                return;
            }

            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

            try {
                const response = await fetch(`api/delete_service.php?id=${id}&type=${type}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showToast('success', 'Serviço excluído!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', result.message);
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                }
            } catch (error) {
                showToast('error', 'Erro ao excluir');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        }

        // Ajustar grid dinamicamente baseado no número de cards
        function adjustGrid() {
            const container = document.querySelector('.services-grid');
            const cards = container.querySelectorAll('.service-card');
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight - 80; // Menos altura do navbar

            // Calcular número ideal de colunas baseado na viewport
            let idealColumns = Math.floor(viewportWidth / 200); // ~200px por card
            idealColumns = Math.max(1, Math.min(idealColumns, 10)); // Entre 1 e 10 colunas

            // Aplicar o grid calculado
            if (cards.length > 0) {
                container.style.gridTemplateColumns = `repeat(${idealColumns}, 1fr)`;
            }
        }

        // Ajustar grid ao carregar e ao redimensionar
        window.addEventListener('load', adjustGrid);
        window.addEventListener('resize', adjustGrid);

        // Verificar mudanças a cada 30 segundos (além do auto-refresh)
        setInterval(() => {
            checkAllServices();
        }, 30000);

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N para adicionar novo serviço
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                openModal();
            }

            // Ctrl/Cmd + R para recarregar
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                location.reload();
            }

            // Ctrl/Cmd + S para toggle de som
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('soundToggle').click();
            }
        });

        // Permitir clique para ativar som em dispositivos móveis
        document.addEventListener('click', function() {
            const audio = document.getElementById('alertSound');
            if (audio.paused) {
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                }).catch(() => {});
            }
        }, { once: true });
    </script>
</body>
</html>