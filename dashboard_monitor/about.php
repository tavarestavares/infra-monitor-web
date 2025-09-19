<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Sobre o Sistema</title>

    <!-- DaisyUI + Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.24/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Animações suaves */
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cards com hover effect */
        .feature-card {
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        /* Gradientes */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Timeline */
        .timeline-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            position: absolute;
            left: -6px;
            top: 6px;
        }

        .timeline-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        }

        /* Scrollbar customizada */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }

        /* Badge animado */
        .badge-glow {
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(102, 126, 234, 0.5); }
            50% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.8); }
        }

        /* Código com syntax highlight */
        .code-block {
            background: #1e1e1e;
            border-radius: 8px;
            padding: 16px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .code-comment {
            color: #6A9955;
        }

        .code-keyword {
            color: #569CD6;
        }

        .code-string {
            color: #CE9178;
        }

        /* Tabs customizadas */
        .tab-custom {
            position: relative;
            padding-bottom: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-custom:hover {
            opacity: 0.8;
        }

        .tab-custom.tab-active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Esconder conteúdo das tabs */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body class="min-h-screen bg-base-200">
    <!-- Header/Navbar -->
    <div class="navbar bg-base-100 shadow-lg sticky top-0 z-50">
        <div class="flex-1">
            <a href="index.php" class="btn btn-ghost normal-case text-xl">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar ao Dashboard
            </a>
        </div>
        <div class="flex-none">
            <button onclick="toggleTheme()" class="btn btn-ghost btn-circle">
                <i class="fas fa-moon" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero gradient-bg text-white py-20">
        <div class="hero-content text-center">
            <div class="max-w-md fade-in">
                <h1 class="text-5xl font-bold mb-4">
                    <i class="fas fa-server mb-4"></i><br>
                    Monitor Dashboard
                </h1>
                <p class="text-xl mb-4">Sistema Profissional de Monitoramento de Serviços</p>
                <div class="badge badge-outline badge-lg badge-glow">Versão 2.0</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- Tabs de Navegação -->
        <div class="tabs tabs-boxed mb-8 justify-center">
            <button class="tab tab-custom tab-active" data-tab="overview">
                <i class="fas fa-home mr-2"></i>Visão Geral
            </button>
            <button class="tab tab-custom" data-tab="features">
                <i class="fas fa-star mr-2"></i>Recursos
            </button>
            <button class="tab tab-custom" data-tab="technical">
                <i class="fas fa-code mr-2"></i>Técnico
            </button>
            <button class="tab tab-custom" data-tab="installation">
                <i class="fas fa-download mr-2"></i>Instalação
            </button>
            <button class="tab tab-custom" data-tab="api">
                <i class="fas fa-plug mr-2"></i>APIs
            </button>
            <button class="tab tab-custom" data-tab="changelog">
                <i class="fas fa-history mr-2"></i>Changelog
            </button>
        </div>

        <!-- Tab: Visão Geral -->
        <div id="tab-overview" class="tab-content active fade-in">
            <div class="card bg-base-100 shadow-xl mb-8">
                <div class="card-body">
                    <h2 class="card-title text-2xl mb-4 gradient-text">
                        <i class="fas fa-info-circle"></i> Sobre o Sistema
                    </h2>
                    <div class="prose max-w-none">
                        <p class="text-lg">
                            O <strong>Monitor Dashboard</strong> é uma solução completa e profissional para monitoramento 
                            de serviços internos e externos em tempo real. Desenvolvido com as mais modernas tecnologias web, 
                            oferece uma interface intuitiva e responsiva para gerenciar a disponibilidade de seus serviços críticos.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="stat bg-primary text-primary-content rounded-lg">
                                <div class="stat-figure">
                                    <i class="fas fa-rocket text-3xl"></i>
                                </div>
                                <div class="stat-title text-primary-content opacity-80">Performance</div>
                                <div class="stat-value">Real-time</div>
                                <div class="stat-desc text-primary-content opacity-80">Atualização automática</div>
                            </div>

                            <div class="stat bg-secondary text-secondary-content rounded-lg">
                                <div class="stat-figure">
                                    <i class="fas fa-shield-alt text-3xl"></i>
                                </div>
                                <div class="stat-title text-secondary-content opacity-80">Confiabilidade</div>
                                <div class="stat-value">99.9%</div>
                                <div class="stat-desc text-secondary-content opacity-80">Uptime garantido</div>
                            </div>

                            <div class="stat bg-accent text-accent-content rounded-lg">
                                <div class="stat-figure">
                                    <i class="fas fa-mobile-alt text-3xl"></i>
                                </div>
                                <div class="stat-title text-accent-content opacity-80">Responsivo</div>
                                <div class="stat-value">100%</div>
                                <div class="stat-desc text-accent-content opacity-80">Mobile-first</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Principais Características -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-check-circle text-success"></i> Principais Características
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-bell text-warning mt-1"></i>
                            <div>
                                <h4 class="font-bold">Alertas Inteligentes</h4>
                                <p class="text-sm opacity-80">Notificações sonoras e visuais quando serviços ficam offline</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-clock text-info mt-1"></i>
                            <div>
                                <h4 class="font-bold">Contador de Tempo Offline</h4>
                                <p class="text-sm opacity-80">Mostra há quanto tempo cada serviço está indisponível</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-edit text-primary mt-1"></i>
                            <div>
                                <h4 class="font-bold">Edição Inline</h4>
                                <p class="text-sm opacity-80">Edite configurações de serviços sem sair da tela principal</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-sync text-success mt-1"></i>
                            <div>
                                <h4 class="font-bold">Auto-Refresh</h4>
                                <p class="text-sm opacity-80">Atualização automática configurável da interface</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Recursos -->
        <div id="tab-features" class="tab-content">
            <h2 class="text-3xl font-bold mb-6 gradient-text">
                <i class="fas fa-star"></i> Recursos Completos
            </h2>

            <!-- Grid de Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Monitoramento -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-primary">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="card-title">Monitoramento em Tempo Real</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>Verificação de portas TCP/UDP</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Teste de conectividade HTTP/HTTPS</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Suporte a serviços internos e externos</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Medição de tempo de resposta</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Histórico de disponibilidade</li>
                        </ul>
                    </div>
                </div>

                <!-- Interface -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-secondary">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3 class="card-title">Interface Moderna</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>Design responsivo mobile-first</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Tema claro/escuro</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Grid auto-ajustável</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Cards interativos com animações</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Tooltips informativos</li>
                        </ul>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="card-title">Sistema de Alertas</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>Alertas sonoros configuráveis</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Notificações visuais toast</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Indicador de serviços offline</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Alerta periódico a cada 30min</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Contador de tempo offline</li>
                        </ul>
                    </div>
                </div>

                <!-- Gerenciamento -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-info">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 class="card-title">Gerenciamento Fácil</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>Adicionar serviços rapidamente</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Edição inline de configurações</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Exclusão com confirmação</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Verificação manual individual</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Intervalos personalizados</li>
                        </ul>
                    </div>
                </div>

                <!-- Protocolos -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-success">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h3 class="card-title">Protocolos Suportados</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>HTTP/HTTPS</li>
                            <li><i class="fas fa-check text-success mr-2"></i>MySQL/MariaDB</li>
                            <li><i class="fas fa-check text-success mr-2"></i>SSH</li>
                            <li><i class="fas fa-check text-success mr-2"></i>FTP/SFTP</li>
                            <li><i class="fas fa-check text-success mr-2"></i>SMTP/DNS</li>
                        </ul>
                    </div>
                </div>

                <!-- Performance -->
                <div class="card bg-base-100 shadow-xl feature-card">
                    <div class="card-body">
                        <div class="text-4xl mb-4 text-error">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h3 class="card-title">Alta Performance</h3>
                        <ul class="text-sm space-y-2 mt-4">
                            <li><i class="fas fa-check text-success mr-2"></i>Verificações assíncronas</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Cache inteligente</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Queries otimizadas</li>
                            <li><i class="fas fa-check text-success mr-2"></i>JavaScript não-bloqueante</li>
                            <li><i class="fas fa-check text-success mr-2"></i>Lazy loading de recursos</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Funcionalidades Especiais -->
            <div class="card bg-gradient-to-r from-primary to-secondary text-primary-content shadow-xl mt-8">
                <div class="card-body">
                    <h3 class="card-title text-2xl">
                        <i class="fas fa-magic"></i> Funcionalidades Especiais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <h4 class="font-bold mb-2">🎵 Som de Alerta Inteligente</h4>
                            <p class="text-sm opacity-90">
                                Sistema de alertas sonoros com Web Audio API fallback, 
                                ativação por clique em mobile e persistência de preferências.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">⏱️ Timer Offline em Tempo Real</h4>
                            <p class="text-sm opacity-90">
                                Contador que atualiza a cada segundo mostrando há quanto 
                                tempo o serviço está offline com formato inteligente.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">📱 Grid Auto-Responsivo</h4>
                            <p class="text-sm opacity-90">
                                Layout que se ajusta automaticamente ao tamanho da tela, 
                                de 1 a 8 colunas baseado na viewport.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">⌨️ Atalhos de Teclado</h4>
                            <p class="text-sm opacity-90">
                                Ctrl+N para novo serviço, Ctrl+R para recarregar, 
                                Ctrl+S para toggle de som.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Técnico -->
        <div id="tab-technical" class="tab-content">
            <h2 class="text-3xl font-bold mb-6 gradient-text">
                <i class="fas fa-code"></i> Especificações Técnicas
            </h2>

            <!-- Stack Tecnológico -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Stack Tecnológico</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Backend -->
                        <div>
                            <h4 class="font-bold mb-3 text-primary">
                                <i class="fas fa-server"></i> Backend
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fab fa-php text-purple-500"></i> PHP
                                    </span>
                                    <span class="badge badge-outline">7.4+</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-database text-blue-500"></i> MySQL
                                    </span>
                                    <span class="badge badge-outline">5.7+</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-cube text-orange-500"></i> MySQLi
                                    </span>
                                    <span class="badge badge-outline">Extension</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-lock text-green-500"></i> Prepared Statements
                                    </span>
                                    <span class="badge badge-outline">Security</span>
                                </div>
                            </div>
                        </div>

                        <!-- Frontend -->
                        <div>
                            <h4 class="font-bold mb-3 text-secondary">
                                <i class="fas fa-palette"></i> Frontend
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fab fa-html5 text-orange-500"></i> HTML5
                                    </span>
                                    <span class="badge badge-outline">Semantic</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fab fa-css3-alt text-blue-500"></i> Tailwind CSS
                                    </span>
                                    <span class="badge badge-outline">3.x</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-flower text-purple-500"></i> DaisyUI
                                    </span>
                                    <span class="badge badge-outline">4.4.24</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <i class="fab fa-js text-yellow-500"></i> JavaScript
                                    </span>
                                    <span class="badge badge-outline">ES6+</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arquitetura -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">Arquitetura do Sistema</h3>

                    <div class="mockup-code">
                        <pre data-prefix="📁"><code>dashboard_monitor/
├── 📄 index.php           <span class="code-comment"># Dashboard principal</span>
├── 📄 about.php           <span class="code-comment"># Página sobre (você está aqui)</span>
├── 📄 history.php         <span class="code-comment"># Histórico de verificações</span>
├── 📄 cron_check.php      <span class="code-comment"># Script para execução via CRON</span>
├── 📄 database.sql        <span class="code-comment"># Schema do banco de dados</span>
├── 🔊 alert.mp3           <span class="code-comment"># Som de alerta</span>
│
├── 📁 includes/
│   └── 📄 config.php      <span class="code-comment"># Configurações e funções</span>
│
└── 📁 api/
    ├── 📄 add_service.php     <span class="code-comment"># Adicionar serviço</span>
    ├── 📄 check_service.php   <span class="code-comment"># Verificar status</span>
    ├── 📄 delete_service.php  <span class="code-comment"># Excluir serviço</span>
    ├── 📄 get_service.php     <span class="code-comment"># Obter dados do serviço</span>
    └── 📄 update_service.php  <span class="code-comment"># Atualizar configurações</span></code></pre>
                    </div>
                </div>
            </div>

            <!-- Banco de Dados -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title mb-4">Estrutura do Banco de Dados</h3>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Tabela</th>
                                    <th>Descrição</th>
                                    <th>Principais Campos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code class="text-primary">internal_services</code></td>
                                    <td>Serviços internos da rede</td>
                                    <td><span class="text-xs">id, name, ip_address, port, status, last_online</span></td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">external_services</code></td>
                                    <td>Serviços externos/internet</td>
                                    <td><span class="text-xs">id, name, domain, port, status, last_online</span></td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">check_history</code></td>
                                    <td>Histórico de verificações</td>
                                    <td><span class="text-xs">id, service_id, status, response_time, checked_at</span></td>
                                </tr>
                                <tr>
                                    <td><code class="text-primary">settings</code></td>
                                    <td>Configurações do sistema</td>
                                    <td><span class="text-xs">setting_key, setting_value</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle"></i>
                        <span>O banco utiliza triggers para atualizar automaticamente o campo <code>last_online</code> quando um serviço volta a ficar online.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Instalação -->
        <div id="tab-installation" class="tab-content">
            <h2 class="text-3xl font-bold mb-6 gradient-text">
                <i class="fas fa-download"></i> Guia de Instalação
            </h2>

            <!-- Requisitos -->
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        <i class="fas fa-check-square text-success"></i> Requisitos do Sistema
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-bold mb-2">Servidor Web</h4>
                            <ul class="space-y-1 text-sm">
                                <li>✅ Apache 2.4+ ou Nginx 1.18+</li>
                                <li>✅ PHP 7.4 ou superior</li>
                                <li>✅ Extensão MySQLi habilitada</li>
                                <li>✅ Extensão cURL (opcional)</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">Banco de Dados</h4>
                            <ul class="space-y-1 text-sm">
                                <li>✅ MySQL 5.7+ ou MariaDB 10.3+</li>
                                <li>✅ Suporte a triggers</li>
                                <li>✅ Charset UTF8MB4</li>
                                <li>✅ Mínimo 50MB de espaço</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passo a Passo -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title mb-4">
                        <i class="fas fa-list-ol text-primary"></i> Instalação Passo a Passo
                    </h3>

                    <div class="space-y-6">
                        <!-- Passo 1 -->
                        <div class="relative pl-8">
                            <div class="timeline-line"></div>
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">1. Download e Extração</h4>
                                <div class="code-block">
                                    <span class="code-comment"># Baixar o arquivo</span><br>
                                    wget dashboard_monitor_final.zip<br><br>
                                    <span class="code-comment"># Extrair para o diretório web</span><br>
                                    unzip dashboard_monitor_final.zip -d /var/www/html/<br>
                                    mv /var/www/html/dashboard_monitor_final /var/www/html/dashboard_monitor
                                </div>
                            </div>
                        </div>

                        <!-- Passo 2 -->
                        <div class="relative pl-8">
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">2. Configurar Permissões</h4>
                                <div class="code-block">
                                    <span class="code-comment"># Definir propriedade</span><br>
                                    chown -R www-data:www-data /var/www/html/dashboard_monitor<br><br>
                                    <span class="code-comment"># Definir permissões</span><br>
                                    chmod -R 755 /var/www/html/dashboard_monitor<br>
                                    chmod 644 /var/www/html/dashboard_monitor/includes/config.php
                                </div>
                            </div>
                        </div>

                        <!-- Passo 3 -->
                        <div class="relative pl-8">
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">3. Criar Banco de Dados</h4>
                                <div class="code-block">
                                    <span class="code-comment"># Acessar MySQL</span><br>
                                    mysql -u root -p<br><br>
                                    <span class="code-comment"># Importar estrutura</span><br>
                                    source /var/www/html/dashboard_monitor/database.sql;<br><br>
                                    <span class="code-comment"># Ou via linha de comando</span><br>
                                    mysql -u root -p &lt; /var/www/html/dashboard_monitor/database.sql
                                </div>
                            </div>
                        </div>

                        <!-- Passo 4 -->
                        <div class="relative pl-8">
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">4. Configurar Conexão</h4>
                                <div class="code-block">
                                    <span class="code-comment"># Editar arquivo de configuração</span><br>
                                    nano /var/www/html/dashboard_monitor/includes/config.php<br><br>
                                    <span class="code-comment"># Atualizar credenciais:</span><br>
                                    <span class="code-keyword">define</span>(<span class="code-string">'DB_HOST'</span>, <span class="code-string">'localhost'</span>);<br>
                                    <span class="code-keyword">define</span>(<span class="code-string">'DB_USER'</span>, <span class="code-string">'seu_usuario'</span>);<br>
                                    <span class="code-keyword">define</span>(<span class="code-string">'DB_PASS'</span>, <span class="code-string">'sua_senha'</span>);<br>
                                    <span class="code-keyword">define</span>(<span class="code-string">'DB_NAME'</span>, <span class="code-string">'monitor_dashboard'</span>);
                                </div>
                            </div>
                        </div>

                        <!-- Passo 5 -->
                        <div class="relative pl-8">
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">5. Configurar CRON (Opcional)</h4>
                                <div class="code-block">
                                    <span class="code-comment"># Editar crontab</span><br>
                                    crontab -e<br><br>
                                    <span class="code-comment"># Adicionar linha para verificação automática a cada minuto</span><br>
                                    * * * * * php /var/www/html/dashboard_monitor/cron_check.php > /dev/null 2>&1
                                </div>
                            </div>
                        </div>

                        <!-- Passo 6 -->
                        <div class="relative pl-8">
                            <div class="timeline-dot"></div>
                            <div>
                                <h4 class="font-bold mb-2">6. Acessar o Sistema</h4>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <div>
                                        <p>Acesse o dashboard através do navegador:</p>
                                        <code>http://seu-servidor/dashboard_monitor/</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Troubleshooting -->
            <div class="card bg-warning text-warning-content shadow-xl mt-6">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Solução de Problemas
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <strong>❌ Erro de conexão com banco:</strong>
                            <p>Verifique as credenciais em config.php e se o MySQL está rodando.</p>
                        </div>
                        <div>
                            <strong>❌ Página em branco:</strong>
                            <p>Ative display_errors no PHP e verifique os logs de erro.</p>
                        </div>
                        <div>
                            <strong>❌ Som não funciona:</strong>
                            <p>Alguns navegadores bloqueiam autoplay. Clique na página para ativar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: API -->
        <div id="tab-api" class="tab-content">
            <h2 class="text-3xl font-bold mb-6 gradient-text">
                <i class="fas fa-plug"></i> Documentação das APIs
            </h2>

            <!-- Lista de APIs -->
            <div class="space-y-6">

                <!-- API: Add Service -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-primary">
                            <i class="fas fa-plus-circle"></i> POST /api/add_service.php
                        </h3>
                        <p class="text-sm opacity-80 mb-4">Adiciona um novo serviço ao monitoramento</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-bold mb-2">Parâmetros</h4>
                                <div class="code-block text-xs">
                                    type: "internal" | "external"<br>
                                    name: string<br>
                                    ip: string (serviços internos)<br>
                                    domain: string (serviços externos)<br>
                                    port: number<br>
                                    service_type: string<br>
                                    interval: number (segundos)
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">Resposta</h4>
                                <div class="code-block text-xs">
                                    {<br>
                                    &nbsp;&nbsp;"success": true,<br>
                                    &nbsp;&nbsp;"message": "Serviço adicionado",<br>
                                    &nbsp;&nbsp;"id": 123<br>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API: Check Service -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-info">
                            <i class="fas fa-sync"></i> GET /api/check_service.php
                        </h3>
                        <p class="text-sm opacity-80 mb-4">Verifica o status atual de um serviço</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-bold mb-2">Parâmetros</h4>
                                <div class="code-block text-xs">
                                    id: number<br>
                                    type: "internal" | "external"
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2">Resposta</h4>
                                <div class="code-block text-xs">
                                    {<br>
                                    &nbsp;&nbsp;"success": true,<br>
                                    &nbsp;&nbsp;"status": "online" | "offline",<br>
                                    &nbsp;&nbsp;"response_time": 125<br>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mais APIs... -->
            </div>

            <!-- Rate Limiting -->
            <div class="alert alert-warning mt-6">
                <i class="fas fa-exclamation-triangle"></i>
                <span>
                    <strong>Nota:</strong> Recomenda-se implementar rate limiting em produção para evitar abuso das APIs.
                    Sugestão: máximo 60 requisições por minuto por IP.
                </span>
            </div>
        </div>

        <!-- Tab: Changelog -->
        <div id="tab-changelog" class="tab-content">
            <h2 class="text-3xl font-bold mb-6 gradient-text">
                <i class="fas fa-history"></i> Histórico de Versões
            </h2>

            <!-- Timeline de Versões -->
            <div class="space-y-6">

                <!-- Versão 2.0 -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="card-title">
                                <span class="badge badge-primary badge-lg">v2.0</span>
                                Versão Final - Feature Complete
                            </h3>
                            <span class="text-sm opacity-60">Janeiro 2024</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-bold mb-2 text-success">✨ Novos Recursos</h4>
                                <ul class="text-sm space-y-1">
                                    <li>• Página "Sobre" completa com documentação</li>
                                    <li>• Timer de tempo offline em tempo real</li>
                                    <li>• Botão de edição inline para serviços</li>
                                    <li>• APIs REST para gerenciamento</li>
                                    <li>• Sistema de alertas sonoros inteligente</li>
                                    <li>• Alerta periódico a cada 30 minutos</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-bold mb-2 text-info">🔧 Melhorias</h4>
                                <ul class="text-sm space-y-1">
                                    <li>• Campo last_online no banco de dados</li>
                                    <li>• Triggers SQL automáticos</li>
                                    <li>• Grid super responsivo (1-8 colunas)</li>
                                    <li>• Tooltips em todos os botões</li>
                                    <li>• Atalhos de teclado</li>
                                    <li>• Fallback para Web Audio API</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Versão 1.5 -->
                <div class="card bg-base-100 shadow-xl opacity-90">
                    <div class="card-body">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="card-title">
                                <span class="badge badge-secondary badge-lg">v1.5</span>
                                Interface Aprimorada
                            </h3>
                            <span class="text-sm opacity-60">Janeiro 2024</span>
                        </div>

                        <ul class="text-sm space-y-1">
                            <li>• Tema claro/escuro com persistência</li>
                            <li>• Cards com animações e hover effects</li>
                            <li>• Indicador visual de serviços offline</li>
                            <li>• Toast notifications</li>
                            <li>• Loading states em botões</li>
                        </ul>
                    </div>
                </div>

                <!-- Versão 1.0 -->
                <div class="card bg-base-100 shadow-xl opacity-80">
                    <div class="card-body">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="card-title">
                                <span class="badge badge-accent badge-lg">v1.0</span>
                                Versão Inicial
                            </h3>
                            <span class="text-sm opacity-60">Dezembro 2023</span>
                        </div>

                        <ul class="text-sm space-y-1">
                            <li>• Dashboard básico com cards</li>
                            <li>• Monitoramento de serviços internos/externos</li>
                            <li>• Verificação manual de status</li>
                            <li>• Auto-refresh configurável</li>
                            <li>• CRUD básico de serviços</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Roadmap Futuro -->
            <div class="card bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-xl mt-8">
                <div class="card-body">
                    <h3 class="card-title text-2xl">
                        <i class="fas fa-road"></i> Roadmap Futuro
                    </h3>
                    <p class="mb-4">Funcionalidades planejadas para próximas versões:</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-bold mb-2">📊 v2.5 - Analytics</h4>
                            <ul class="text-sm space-y-1">
                                <li>• Gráficos de disponibilidade</li>
                                <li>• Relatórios PDF/Excel</li>
                                <li>• Dashboard de métricas</li>
                                <li>• SLA calculator</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold mb-2">🔔 v3.0 - Notificações</h4>
                            <ul class="text-sm space-y-1">
                                <li>• Integração com Telegram</li>
                                <li>• Notificações por e-mail</li>
                                <li>• Webhooks personalizados</li>
                                <li>• Push notifications</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer da página -->
        <footer class="text-center py-8 mt-12 border-t">
            <div class="flex flex-col items-center gap-4">
                <div class="flex gap-4">
                    <a href="https://github.com" class="btn btn-ghost btn-circle">
                        <i class="fab fa-github text-xl"></i>
                    </a>
                    <a href="index.php" class="btn btn-ghost btn-circle">
                        <i class="fas fa-home text-xl"></i>
                    </a>
                    <button onclick="window.print()" class="btn btn-ghost btn-circle">
                        <i class="fas fa-print text-xl"></i>
                    </button>
                </div>
                <div class="text-sm opacity-60">
                    <p>Monitor Dashboard v2.0 - Sistema Profissional de Monitoramento</p>
                    <p>© 2024 - Desenvolvido com ❤️ usando PHP, MySQL, Tailwind CSS e DaisyUI</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        // Sistema de tabs melhorado
        function initTabs() {
            // Pegar todos os botões de tab
            const tabButtons = document.querySelectorAll('.tab[data-tab]');
            const tabContents = document.querySelectorAll('.tab-content');

            // Adicionar event listener para cada botão
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Pegar o nome da tab do atributo data-tab
                    const tabName = this.getAttribute('data-tab');

                    // Remover active de todos os botões e conteúdos
                    tabButtons.forEach(btn => btn.classList.remove('tab-active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Adicionar active ao botão clicado
                    this.classList.add('tab-active');

                    // Mostrar o conteúdo correspondente
                    const targetContent = document.getElementById('tab-' + tabName);
                    if (targetContent) {
                        targetContent.classList.add('active');
                        targetContent.classList.add('fade-in');
                    }

                    // Salvar preferência
                    localStorage.setItem('lastTab', tabName);
                });
            });
        }

        // Toggle tema
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');

            if (currentTheme === 'dark') {
                html.setAttribute('data-theme', 'light');
                icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'dark');
            }
        }

        // Inicialização quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar sistema de tabs
            initTabs();

            // Carregar tema salvo
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.getElementById('theme-icon').className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

            // Restaurar última tab visitada
            const lastTab = localStorage.getItem('lastTab');
            if (lastTab && document.getElementById('tab-' + lastTab)) {
                // Encontrar e clicar no botão correspondente
                const targetButton = document.querySelector(`.tab[data-tab="${lastTab}"]`);
                if (targetButton) {
                    targetButton.click();
                }
            }
        });

        // Copiar código ao clicar
        document.querySelectorAll('.code-block').forEach(block => {
            block.style.cursor = 'pointer';
            block.title = 'Clique para copiar';
            block.addEventListener('click', function() {
                const text = this.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    // Feedback visual
                    const original = this.style.background;
                    this.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                    this.style.opacity = '0.8';

                    setTimeout(() => {
                        this.style.background = original;
                        this.style.opacity = '1';
                    }, 200);

                    // Toast de sucesso
                    showToast('Código copiado!', 'success');
                });
            });
        });

        // Função para mostrar toast
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = 'toast toast-top toast-center z-50';
            toast.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas fa-check-circle"></i>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 2000);
        }

        // Atalhos de teclado
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + P para imprimir
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }

            // ESC para voltar ao dashboard
            if (e.key === 'Escape') {
                window.location.href = 'index.php';
            }

            // Ctrl/Cmd + números para navegar nas tabs
            if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '6') {
                e.preventDefault();
                const tabs = ['overview', 'features', 'technical', 'installation', 'api', 'changelog'];
                const tabIndex = parseInt(e.key) - 1;
                if (tabs[tabIndex]) {
                    const targetButton = document.querySelector(`.tab[data-tab="${tabs[tabIndex]}"]`);
                    if (targetButton) {
                        targetButton.click();
                    }
                }
            }
        });

        // Smooth scroll para âncoras
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Estatísticas animadas ao entrar na viewport
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-value').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>