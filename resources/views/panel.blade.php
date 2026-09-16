<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel Comercial | Riviera Pescados</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🐟</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .tab-content { display: none; animation: fadeIn 0.2s ease-out; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .dropdown-menu { display: none; transform-origin: top; animation: scaleY 0.15s ease-out; }
        .dropdown-menu.active { display: flex; flex-direction: column; }
        @keyframes scaleY { from { opacity: 0; transform: scaleY(0.95); } to { opacity: 1; transform: scaleY(1); } }
        
        .sidebar-bg { background-color: #0b1120; }
        .sidebar-btn-active { background-color: #2563eb; color: #ffffff; box-shadow: 0 2px 5px rgba(37, 99, 235, 0.4); }
        .sidebar-btn { color: #94a3b8; background-color: transparent; }
        .sidebar-btn:hover { color: #ffffff; background-color: rgba(255,255,255,0.05); }

        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

        /* Estilos do Mapa de Hierarquia */
        .tree-line { position: absolute; background: #e2e8f0; z-index: 0; }
        .tree-card { position: relative; z-index: 10; }
    </style>
</head>
<body class="text-slate-800 h-screen w-full flex overflow-hidden antialiased text-sm">

    <!-- OVERLAYS E TOASTS -->
    <div id="toast-container" class="fixed top-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    <div id="custom-dialog-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9990] hidden items-center justify-center opacity-0 transition-opacity duration-200">
        <div id="custom-dialog-box" class="bg-white w-11/12 max-w-sm rounded-2xl shadow-xl p-6 transform scale-95 transition-transform duration-200 flex flex-col items-center">
            <div id="custom-dialog-icon" class="text-4xl mb-4 text-center"></div>
            <h3 id="custom-dialog-title" class="text-lg font-bold text-slate-900 text-center mb-1">Título</h3>
            <p id="custom-dialog-message" class="text-slate-500 text-xs font-medium text-center mb-4 w-full whitespace-pre-wrap">Mensagem</p>
            <input type="text" id="custom-dialog-input" class="hidden w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none mb-4 text-center">
            <div class="flex gap-2 w-full">
                <button id="custom-dialog-cancel" class="hidden flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors text-xs">Cancelar</button>
                <button id="custom-dialog-confirm" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-xs shadow-sm">OK</button>
            </div>
        </div>
    </div>

    <!-- ================= TELA DE LOGIN ================= -->
    <div id="auth-screen" class="fixed inset-0 bg-[#0b1120] z-[9000] flex items-center justify-center transition-opacity duration-300">
        <div class="absolute inset-0 w-full h-full opacity-10 pointer-events-none" style="background-image: radial-gradient(circle at 50% 50%, #3b82f6 0%, transparent 40%)"></div>
        
        <div id="auth-loading" class="text-white flex flex-col items-center relative z-10 hidden">
            <i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500 mb-3"></i>
            <p class="font-medium animate-pulse text-sm">Conectando à base de dados...</p>
        </div>

        <div id="login-box" class="bg-white p-8 rounded-2xl shadow-2xl w-11/12 max-w-[400px] relative z-10 transform transition-all">
            <div class="text-center mb-8">
                <div class="bg-blue-600 text-white w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-lg shadow-blue-600/30">
                    <i class="fa-solid fa-fish-fins"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Riviera Pescados</h2>
                <p class="text-slate-500 mt-1 font-medium text-xs uppercase tracking-widest">Plataforma Comercial</p>
            </div>

            <!-- Login -->
            <form id="form-login" onsubmit="app.login(event)" class="space-y-5">
                <div id="login-error-msg" class="hidden bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold p-3 rounded-lg flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> <span></span>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">E-mail corporativo</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input id="login-email" type="email" required placeholder="usuario@rivierapescados.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Senha de acesso</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input id="login-pass" type="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-10 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                        <button type="button" onclick="app.togglePass('login-pass', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors"><i class="fa-solid fa-eye text-sm"></i></button>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs font-medium text-slate-600 pt-1">
                    <label class="flex items-center gap-1.5 cursor-pointer"><input type="checkbox" id="login-keep" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer"> Manter logado</label>
                    <button type="button" onclick="app.switchAuth('recovery')" class="text-blue-600 font-semibold hover:underline">Esqueceu a senha?</button>
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg py-3 shadow-md flex justify-center items-center gap-2 transition-all mt-2 text-sm">
                    Acessar Painel <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Recuperação -->
            <form id="form-recovery" onsubmit="app.recoverPass(event)" class="space-y-5 hidden">
                <p class="text-xs font-medium text-slate-500 text-center mb-4">Informe seu e-mail para receber uma senha provisória e recuperar seu acesso.</p>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input id="rec-email" type="email" required placeholder="usuario@rivierapescados.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg py-3 shadow-md transition-all text-sm">Enviar Senha</button>
                <button type="button" onclick="app.switchAuth('login')" class="w-full text-slate-500 font-semibold hover:text-slate-800 transition-all text-xs">Voltar ao Login</button>
            </form>

            <!-- Reset Obrigatório -->
            <form id="form-reset" onsubmit="app.forceReset(event)" class="space-y-5 hidden">
                <div class="bg-amber-50 text-amber-800 text-xs font-medium p-3 rounded-lg flex items-center gap-2 mb-2 border border-amber-200">
                    <i class="fa-solid fa-shield-halved"></i> Crie sua senha definitiva por segurança.
                </div>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input id="reset-p1" type="password" required minlength="6" placeholder="Nova Senha" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-10 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <button type="button" onclick="app.togglePass('reset-p1', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600"><i class="fa-solid fa-eye text-sm"></i></button>
                </div>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input id="reset-p2" type="password" required minlength="6" placeholder="Confirmar Senha" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-10 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <button type="button" onclick="app.togglePass('reset-p2', this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600"><i class="fa-solid fa-eye text-sm"></i></button>
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg py-3 shadow-md transition-all text-sm">Salvar e Acessar</button>
            </form>
        </div>
    </div>

    <!-- ================= ESTRUTURA PRINCIPAL (SIDEBAR E MAIN) ================= -->
    <aside class="w-64 sidebar-bg text-slate-300 flex-shrink-0 hidden md:flex flex-col shadow-xl z-20 transition-all border-r border-slate-800/50">
        <div class="h-16 flex items-center px-5 mt-2 mb-2">
            <div class="bg-blue-600 text-white p-2 rounded-lg shadow-sm mr-3">
                <i class="fa-solid fa-fish-fins text-lg"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-tight leading-tight">Riviera Pescados</h1>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 space-y-1 mt-2" id="nav-menu">
            <!-- Menus Injetados via JS -->
        </nav>

        <div class="p-4 border-t border-slate-800/50 mt-auto">
            <div class="flex items-center gap-3 bg-white/5 hover:bg-white/10 p-2.5 rounded-xl cursor-pointer transition-colors" onclick="app.logout()" title="Sair do Sistema">
                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-sm text-sm">
                    <i id="ui-role-icon" class="fa-solid fa-user-tie"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="ui-role-title" class="text-[9px] font-bold text-blue-400 uppercase tracking-wider">Acesso</p>
                    <p id="ui-user-name" class="text-xs font-semibold text-white truncate">Nome</p>
                </div>
                <i class="fa-solid fa-right-from-bracket text-slate-500 text-sm mr-1"></i>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col h-screen overflow-hidden relative bg-slate-50">
        <header class="md:hidden bg-[#0b1120] text-white p-4 flex justify-between items-center shadow-md z-20 relative">
            <div class="flex items-center gap-2 font-bold text-sm"><i class="fa-solid fa-fish-fins text-blue-500 text-base"></i> Riviera</div>
            <button onclick="app.logout()" class="text-slate-400"><i class="fa-solid fa-right-from-bracket"></i></button>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-6 pb-20 scroll-smooth" id="main-content">
            
            <!-- VIEW: DASHBOARD -->
            <div id="tab-dashboard" class="tab-content active space-y-5">
                
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center bg-white px-5 py-4 rounded-xl shadow-sm border border-slate-200">
                    <div>
                        <h2 id="dash-title" class="text-xl font-bold tracking-tight text-slate-900">Força de Vendas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Visão consolidada de performance.</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2 mt-4 xl:mt-0">
                        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-1.5 py-1 shadow-sm">
                            <div class="relative" id="filter-box-mes">
                                <button onclick="app.toggleDropdown('drop-meses')" class="text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-md hover:bg-white flex items-center gap-1.5 transition">
                                    <i class="fa-regular fa-calendar text-slate-400"></i> <span id="lbl-meses">Meses</span> <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div id="drop-meses" class="dropdown-menu absolute top-full left-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-2 max-h-48 overflow-y-auto">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase mb-1 px-2 tracking-wider">Período</div>
                                    <div id="list-cb-meses" class="space-y-0.5"></div>
                                </div>
                            </div>
                            
                            <div class="w-px h-4 bg-slate-200 mx-0.5 hidden md:block" id="div-area"></div>

                            <div class="relative hidden md:block" id="filter-box-area">
                                <button onclick="app.toggleDropdown('drop-areas')" class="text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-md hover:bg-white flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-map-location-dot text-slate-400"></i> <span id="lbl-areas">Todas Áreas</span> <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div id="drop-areas" class="dropdown-menu absolute top-full left-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-2 max-h-48 overflow-y-auto">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase mb-1 px-2 tracking-wider">Áreas</div>
                                    <div id="list-cb-areas" class="space-y-0.5"></div>
                                </div>
                            </div>

                            <div class="w-px h-4 bg-slate-200 mx-0.5 hidden md:block" id="div-gestor"></div>

                            <div class="relative hidden md:block" id="filter-box-gestor">
                                <button onclick="app.toggleDropdown('drop-gestores')" class="text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-md hover:bg-white flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-user-tie text-slate-400"></i> <span id="lbl-gestores">Gestores</span> <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div id="drop-gestores" class="dropdown-menu absolute top-full left-0 mt-1 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-2 max-h-48 overflow-y-auto">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase mb-1 px-2 tracking-wider">Gestor</div>
                                    <div id="list-cb-gestores" class="space-y-0.5"></div>
                                </div>
                            </div>

                            <div class="w-px h-4 bg-slate-200 mx-0.5"></div>

                            <div class="relative" id="filter-box-reps">
                                <button onclick="app.toggleDropdown('drop-reps')" class="text-slate-600 text-xs font-semibold px-3 py-1.5 rounded-md hover:bg-white flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-users text-slate-400"></i> <span id="lbl-reps">Todos Reps</span> <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                                </button>
                                <div id="drop-reps" class="dropdown-menu absolute top-full right-0 mt-1 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 p-2 max-h-48 overflow-y-auto">
                                    <div class="text-[9px] font-bold text-slate-400 uppercase mb-1 px-2 tracking-wider">Força de Vendas</div>
                                    <div id="list-cb-reps" class="space-y-0.5"></div>
                                </div>
                            </div>
                        </div>
                        <button onclick="app.exportarBase()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-1.5 rounded-lg shadow-sm transition flex items-center gap-2 text-xs h-[34px]">
                            <i class="fa-solid fa-file-csv"></i> Exportar
                        </button>
                    </div>
                </div>

                <!-- 4 KPIs Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-semibold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Meta do Período <i class="fa-solid fa-bullseye text-slate-300 text-sm"></i></p>
                        <h3 id="kpi-meta" class="text-2xl font-bold text-slate-900 mt-1">R$ 0,00</h3>
                        <p id="kpi-meta-vol" class="text-[10px] text-blue-500 font-semibold mt-1">Vol: 0 kg | Alvo: R$ 0,00/kg</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-semibold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Faturamento Realizado <i class="fa-solid fa-hand-holding-dollar text-emerald-400 text-sm"></i></p>
                        <h3 id="kpi-real" class="text-2xl font-bold text-emerald-600 mt-1">R$ 0,00</h3>
                        <p id="kpi-vol" class="text-[10px] text-slate-400 font-semibold mt-1">Volume: 0 kg</p>
                        <div class="mt-2.5 w-full bg-slate-100 rounded-full h-1"><div id="kpi-bar-mini" class="bg-emerald-500 h-1 rounded-full transition-all" style="width: 0%"></div></div>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-semibold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Atingimento Atual <i class="fa-solid fa-chart-line text-slate-300 text-sm"></i></p>
                        <div class="flex items-center justify-between mt-1">
                            <h3 id="kpi-perc" class="text-2xl font-bold text-slate-900">0%</h3>
                            <div id="kpi-status" class="text-[10px] font-bold px-2 py-0.5 rounded-md">Status</div>
                        </div>
                    </div>
                    <div class="bg-[#1e293b] p-5 rounded-xl shadow-sm relative text-white">
                        <i class="fa-solid fa-rocket absolute right-4 top-4 text-3xl text-white/5 transform rotate-12"></i>
                        <p class="text-[11px] font-semibold text-indigo-300 flex justify-between uppercase tracking-wider mb-1">Projeção (Run Rate)</p>
                        <h3 id="kpi-runrate" class="text-2xl font-bold text-emerald-400 mt-1">R$ 0,00</h3>
                    </div>
                </div>

                <div class="bg-white px-5 py-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-5">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5"><i class="fa-solid fa-users text-blue-500"></i> Termômetro Equipe:</span>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> <span id="term-verde">0</span> Bateram a Meta</div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> <span id="term-azul">0</span> No Caminho</div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> <span id="term-verm">0</span> Alerta</div>
                </div>

                <!-- Gráficos Minimalistas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[260px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest"><i class="fa-solid fa-trophy text-amber-500 mr-1.5"></i> Top Representantes (%)</h3>
                        </div>
                        <div class="relative flex-1 w-full"><canvas id="chart-reps-perc"></canvas></div>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[260px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Equipe (Meta x Real)</h3>
                        </div>
                        <div class="relative flex-1 w-full"><canvas id="chart-equipe"></canvas></div>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[260px]" id="box-chart-area">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Faturamento por Área</h3>
                        </div>
                        <div class="relative flex-1 w-full flex items-center justify-center"><canvas id="chart-areas"></canvas></div>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[260px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Evolução Anual (<span id="evol-year"></span>)</h3>
                        </div>
                        <div class="relative flex-1 w-full"><canvas id="chart-evol"></canvas></div>
                    </div>
                    
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col min-h-[280px] lg:col-span-2">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Faturamento por Cliente (Top 10)</h3>
                        </div>
                        <div id="chart-clientes" class="space-y-3 max-h-[220px] overflow-y-auto pr-2"></div>
                    </div>
                </div>
            </div>

            <!-- VIEW: HIERARQUIA (DIRETORIA) -->
            <div id="tab-hierarquia" class="tab-content space-y-5">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 max-w-5xl mx-auto min-h-[75vh]">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900"><i class="fa-solid fa-sitemap text-blue-600 mr-2"></i> Mapa de Hierarquia e Performance</h2>
                            <p class="text-xs text-slate-500 mt-1">Visualize o organograma completo e o atingimento de cada nível em tempo real.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                            <i class="fa-regular fa-calendar text-slate-400 text-sm"></i>
                            <input type="month" id="hier-date" class="bg-transparent border-none text-sm font-semibold text-slate-800 outline-none cursor-pointer" onchange="app.renderHierarquia()">
                        </div>
                    </div>

                    <div id="hierarquia-container" class="space-y-6">
                        <!-- JS Injection -->
                    </div>
                </div>
            </div>

            <!-- VIEW: SKUS E PRODUTOS (DIRETORIA) -->
            <div id="tab-skus" class="tab-content space-y-4">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 max-w-4xl mx-auto">
                    <h2 class="text-lg font-bold mb-1 text-slate-900"><i class="fa-solid fa-boxes-stacked text-blue-600 mr-2"></i> Cadastro de SKUs (Produtos)</h2>
                    <p class="text-xs text-slate-500 mb-6">Cadastre os produtos para a equipe de vendas faturar semanalmente.</p>
                    
                    <form onsubmit="app.addSku(event)" class="space-y-4 mb-6 bg-slate-50 p-5 rounded-lg border border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição do Produto (SKU)</label>
                                <input required id="add-sku-nome" placeholder="Ex: Filé de Tilápia" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"/>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria</label>
                                <input required id="add-sku-cat" placeholder="Ex: Congelados" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Gramatura / Peso</label>
                                <input required id="add-sku-peso" placeholder="Ex: 500g" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"/>
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <button type="submit" class="w-full bg-slate-900 text-white font-semibold py-2 rounded-lg shadow-sm hover:bg-slate-800 transition text-sm">Adicionar Produto</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="p-3">Descrição do Produto</th>
                                    <th class="p-3">Categoria</th>
                                    <th class="p-3">Gramatura</th>
                                    <th class="p-3 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="list-skus" class="divide-y divide-slate-100 text-sm"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VIEW: ÁREAS OPERACIONAIS -->
            <div id="tab-areas" class="tab-content space-y-4">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 max-w-3xl mx-auto">
                    <h2 class="text-lg font-bold mb-1 text-slate-900"><i class="fa-solid fa-map-location-dot text-blue-600 mr-2"></i> Áreas Comerciais</h2>
                    <p class="text-xs text-slate-500 mb-6">Estruture as filiais ou divisões comerciais da empresa.</p>
                    
                    <form onsubmit="app.addArea(event)" class="flex flex-col sm:flex-row gap-3 mb-6 bg-slate-50 p-3 rounded-lg border border-slate-100">
                        <input required type="text" id="add-area-nome" placeholder="Nome da Filial / Região..." class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors" />
                        <button type="submit" class="bg-slate-900 text-white font-semibold px-4 py-2 rounded-lg shadow-sm hover:bg-slate-800 text-sm">Criar Área</button>
                    </form>
                    
                    <div id="list-areas" class="space-y-2 max-h-96 overflow-y-auto pr-1"></div>
                </div>
            </div>

            <!-- VIEW: GESTORES -->
            <div id="tab-gestores" class="tab-content space-y-4">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 max-w-3xl mx-auto">
                    <h2 class="text-lg font-bold mb-1 text-slate-900"><i class="fa-solid fa-user-cog text-emerald-500 mr-2"></i> Contas de Gestores</h2>
                    <p class="text-xs text-slate-500 mb-6">Crie acessos restritos para os líderes de área.</p>
                    
                    <form onsubmit="app.addGestor(event)" class="space-y-4 mb-6 bg-slate-50 p-5 rounded-lg border border-slate-100">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo</label>
                            <input required id="add-gestor-nome" placeholder="Ex: Carlos Silva" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-colors"/>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp / Telefone</label><input required type="text" id="add-gestor-tel" oninput="app.maskPhone(event)" placeholder="(11) 99999-9999" maxlength="15" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-colors"/></div>
                            <div><label class="block text-xs font-semibold text-slate-700 mb-1">E-mail corporativo (Login)</label><input required type="email" id="add-gestor-email" placeholder="gerente@riviera.com" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-colors"/></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><label class="block text-xs font-semibold text-slate-700 mb-1">Senha Provisória</label><input required type="text" id="add-gestor-pass" placeholder="Ex: 123456" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-colors"/></div>
                            <div><label class="block text-xs font-semibold text-slate-700 mb-1">Vincular à Área</label><select required id="add-gestor-area" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 focus:border-emerald-500 outline-none cursor-pointer transition-colors"></select></div>
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 text-white font-semibold py-2.5 rounded-lg shadow-sm hover:bg-emerald-700 transition mt-2 text-sm">Criar Conta</button>
                    </form>

                    <div id="list-gestores" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
                </div>
            </div>

            <!-- VIEW: PLANEJAMENTO GLOBAL (NÍVEL 1) -->
            <div id="tab-plan" class="tab-content space-y-4">
                <div class="bg-white p-6 md:p-8 rounded-xl shadow-sm border border-slate-200 max-w-4xl mx-auto flex flex-col min-h-[80vh]">
                    <h2 class="text-xl font-bold text-slate-900 mb-1 flex items-center gap-2"><i class="fa-solid fa-bullseye text-indigo-600"></i> Planejamento Estratégico Global</h2>
                    <p class="text-xs text-slate-500 mb-6">Defina o Objetivo Global. Distribua os valores em R$ para cada gestor de forma manual de acordo com a estratégia da empresa.</p>

                    <div class="bg-indigo-50/50 p-5 rounded-xl border border-indigo-100 mb-6 flex flex-col md:flex-row gap-5 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-bold text-indigo-900 mb-1.5 uppercase tracking-widest">Mês Alvo</label>
                            <input type="month" id="plan-date" class="w-full bg-white border border-indigo-200 rounded-lg px-3 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-indigo-500 transition-colors" onchange="app.renderPlanDir()">
                        </div>
                        <div class="flex-1 w-full relative">
                            <label class="block text-[10px] font-bold text-indigo-900 mb-1.5 uppercase tracking-widest">Objetivo Global (R$)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-indigo-400 text-sm">R$</span>
                                <input type="text" id="plan-obj-global" required placeholder="0,00" class="w-full bg-white border border-indigo-200 rounded-lg pl-9 pr-3 py-2.5 text-base font-bold text-indigo-700 outline-none focus:border-indigo-500 transition-colors" oninput="app.maskMoney(event); app.calcPlanDir()">
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto flex-1 mb-6 border border-slate-200 rounded-lg bg-white">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-white border-b border-slate-200 text-[10px] text-slate-500 uppercase tracking-widest font-bold">
                                <tr>
                                    <th class="p-3">Gestor / Área Comercial</th>
                                    <th class="p-3 w-44 text-right text-indigo-600 bg-indigo-50/30">Meta Alocada (R$) <i class="fa-solid fa-pen text-[9px] ml-1"></i></th>
                                    <th class="p-3 w-20 text-center text-slate-600">Peso (%)</th>
                                    <th class="p-3 w-36 text-right text-emerald-600 bg-emerald-50/30">Preço Médio Alvo (R$/Kg) <i class="fa-solid fa-pen text-[9px] ml-1"></i></th>
                                    <th class="p-3 w-32 text-right text-blue-600">Volume Esperado (Kg)</th>
                                </tr>
                            </thead>
                            <tbody id="plan-dir-grid" class="divide-y divide-slate-100 text-xs">
                                <!-- JS Injection -->
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center bg-slate-900 p-5 rounded-xl shadow-md mb-5">
                        <span class="font-semibold text-slate-300 text-sm">Soma Distribuída:</span>
                        <span id="plan-dir-total" class="text-2xl font-bold text-white leading-none">R$ 0,00</span>
                    </div>

                    <button onclick="app.savePlanDir()" id="btn-save-plan-dir" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition text-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Salvar e Liberar Metas
                    </button>
                </div>
            </div>

            <!-- VIEW: RELATÓRIOS (EXPORTAÇÃO) -->
            <div id="tab-relatorios" class="tab-content space-y-4">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 max-w-4xl mx-auto">
                    <h2 class="text-xl font-bold mb-1 text-slate-900"><i class="fa-solid fa-file-csv text-emerald-600 mr-2"></i> Relatórios Avançados</h2>
                    <p class="text-xs text-slate-500 mb-8">Exporte as bases de dados e os resultados do sistema diretamente para o Excel (CSV).</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-slate-200 rounded-xl p-5 bg-slate-50 hover:border-emerald-300 transition-colors">
                            <h3 class="font-bold text-slate-800 mb-2">Relatório de Faturamento</h3>
                            <p class="text-xs text-slate-500 mb-4">Exportar as vendas com detalhes de SKU, volume e valor faturado por representante.</p>
                            <button onclick="app.exportarVendas()" class="w-full bg-emerald-600 text-white font-semibold py-2 rounded-lg text-sm hover:bg-emerald-700 shadow-sm"><i class="fa-solid fa-download mr-1"></i> Faturamento (CSV)</button>
                        </div>
                        <div id="box-rel-reps" class="border border-slate-200 rounded-xl p-5 bg-slate-50 hover:border-blue-300 transition-colors hidden">
                            <h3 class="font-bold text-slate-800 mb-2">Base de Representantes</h3>
                            <p class="text-xs text-slate-500 mb-4">Exportar o cadastro completo da força de vendas da sua hierarquia.</p>
                            <button onclick="app.exportarReps()" class="w-full bg-white border border-slate-300 text-slate-700 font-semibold py-2 rounded-lg text-sm shadow-sm hover:bg-slate-100"><i class="fa-solid fa-download mr-1"></i> Baixar Equipe (CSV)</button>
                        </div>
                        <div id="box-rel-skus" class="border border-slate-200 rounded-xl p-5 bg-slate-50 hover:border-blue-300 transition-colors hidden">
                            <h3 class="font-bold text-slate-800 mb-2">Base de SKUs (Produtos)</h3>
                            <p class="text-xs text-slate-500 mb-4">Relação completa de todos os produtos cadastrados pela Diretoria.</p>
                            <button onclick="app.exportarSkus()" class="w-full bg-white border border-slate-300 text-slate-700 font-semibold py-2 rounded-lg text-sm shadow-sm hover:bg-slate-100"><i class="fa-solid fa-download mr-1"></i> Baixar Produtos (CSV)</button>
                        </div>
                        <div id="box-rel-areas" class="border border-slate-200 rounded-xl p-5 bg-slate-50 hover:border-blue-300 transition-colors hidden">
                            <h3 class="font-bold text-slate-800 mb-2">Áreas Comerciais & Gestores</h3>
                            <p class="text-xs text-slate-500 mb-4">Mapeamento das divisões e líderes cadastrados no sistema.</p>
                            <button onclick="app.exportarAreas()" class="w-full bg-white border border-slate-300 text-slate-700 font-semibold py-2 rounded-lg text-sm shadow-sm hover:bg-slate-100"><i class="fa-solid fa-download mr-1"></i> Baixar Estrutura (CSV)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GESTOR: EQUIPE -->
            <div id="tab-equipe" class="tab-content space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-5 rounded-xl shadow-sm border border-slate-200 gap-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Equipe de Vendas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Gestão de representantes.</p>
                    </div>
                    <button onclick="app.openModal('modal-rep')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm flex items-center gap-2 transition w-full md:w-auto justify-center"><i class="fa-solid fa-user-plus"></i> Adicionar Representante</button>
                </div>
                
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="p-4">Representante</th>
                                    <th class="p-4">Contato</th>
                                    <th class="p-4">Localização / Pastas</th>
                                    <th class="p-4 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="table-reps" class="divide-y divide-slate-100 text-sm"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- GESTOR: DISTRIBUIÇÃO E VENDAS -->
            <div id="tab-vendas" class="tab-content space-y-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-5 rounded-xl shadow-sm border border-slate-200 gap-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Acompanhamento Semanal & Metas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Defina as metas da sua equipe e lance o faturamento por Produto.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                        <i class="fa-regular fa-calendar text-slate-400 text-sm"></i>
                        <input type="month" id="vendas-date" class="bg-transparent border-none text-sm font-semibold text-slate-800 outline-none cursor-pointer" onchange="app.renderVendas()">
                    </div>
                </div>

                <!-- Bloqueio ou Resumo -->
                <div id="vendas-lock-container" class="hidden bg-amber-50 border border-amber-200 p-5 rounded-xl flex items-center gap-4 shadow-sm">
                    <i class="fa-solid fa-lock text-3xl text-amber-500"></i>
                    <div>
                        <p class="font-bold text-amber-800">Aguardando liberação da Diretoria</p>
                        <p class="text-xs text-amber-700 mt-0.5">A meta deste mês ainda não foi definida ou distribuída pelo Diretor Comercial para a sua área.</p>
                    </div>
                </div>

                <div id="vendas-active-container" class="bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex flex-col lg:flex-row justify-between items-center gap-4 shadow-sm hidden">
                    <div class="text-center lg:text-left w-full lg:w-auto">
                        <p class="text-indigo-600 font-bold text-[9px] uppercase tracking-widest mb-0.5">Meta da Área Comercial (Recebida)</p>
                        <h3 id="vendas-meta-area" class="text-2xl font-bold text-indigo-900">R$ 0,00</h3>
                    </div>
                    <div class="w-full lg:w-auto flex justify-center hidden md:block">
                        <button onclick="app.renderModalGestorIntel()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-lg shadow-sm transition flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Distribuir Meta p/ Equipe
                        </button>
                    </div>
                    <div class="text-center lg:text-right w-full lg:w-auto">
                        <p class="text-indigo-600 font-bold text-[9px] uppercase tracking-widest mb-0.5">Total Distribuído</p>
                        <h3 id="vendas-meta-dist" class="text-xl font-bold text-indigo-800">R$ 0,00</h3>
                    </div>
                    <button onclick="app.renderModalGestorIntel()" class="md:hidden w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg shadow-sm mt-1 flex justify-center items-center gap-2 text-sm"><i class="fa-solid fa-wand-magic-sparkles"></i> Distribuir p/ Equipe</button>
                </div>

                <div id="vendas-table-container" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hidden">
                    <div class="flex justify-between items-center px-6 pt-4 pb-2 border-b border-slate-100">
                        <p class="text-sm font-bold text-slate-500">Lançamentos Semanais / Expresso</p>
                        <button onclick="app.prepVendaGeral()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm flex items-center gap-2 transition text-xs">
                            <i class="fa-solid fa-bolt"></i> Lançamento Expresso
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-widest">
                                <tr>
                                    <th class="p-3">Representante</th>
                                    <th class="p-3 bg-slate-50 border-r border-slate-200">Objetivo Definido</th>
                                    <th class="p-3 bg-slate-100/50">Sem 1 (R$)</th>
                                    <th class="p-3 bg-slate-100/50">Sem 2 (R$)</th>
                                    <th class="p-3 bg-slate-100/50">Sem 3 (R$)</th>
                                    <th class="p-3 bg-slate-100/50 border-r border-slate-200">Sem 4 (R$)</th>
                                    <th class="p-3 text-emerald-600">Total Faturado</th>
                                    <th class="p-3 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="table-vendas" class="divide-y divide-slate-100 text-sm"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- REP: MEU DESEMPENHO -->
            <div id="tab-rep" class="tab-content space-y-4">
                <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900">Meu Desempenho</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Acompanhe suas vendas e atingimento.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                        <input type="month" id="rep-dash-date" onchange="app.renderRepDashboard()" class="bg-transparent border-none text-sm font-semibold text-slate-800 outline-none cursor-pointer" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-bold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Meu Objetivo <i class="fa-solid fa-bullseye text-blue-300"></i></p>
                        <h3 id="rep-kpi-meta" class="text-2xl font-bold text-slate-900 mt-1">R$ 0,00</h3>
                        <p id="rep-kpi-meta-vol" class="text-[10px] font-semibold text-blue-500 mt-0.5">Vol: 0 Kg | R$ 0,00/Kg</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-bold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Minhas Vendas <i class="fa-solid fa-hand-holding-dollar text-emerald-400"></i></p>
                        <h3 id="rep-kpi-real" class="text-2xl font-bold text-emerald-600 mt-1">R$ 0,00</h3>
                        <p id="rep-kpi-vol" class="text-[10px] font-semibold text-emerald-500 mt-0.5">Vol: 0 Kg | R$ 0,00/Kg</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm relative">
                        <p class="text-[11px] font-bold text-slate-500 flex justify-between uppercase tracking-wider mb-1">Atingimento <i class="fa-solid fa-chart-line text-slate-300"></i></p>
                        <h3 id="rep-kpi-perc" class="text-2xl font-bold text-slate-900 mt-1">0%</h3>
                        <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5"><div id="rep-kpi-bar" class="bg-blue-600 h-1.5 rounded-full transition-all" style="width: 0%"></div></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-4">Minhas Vendas por Semana</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-center whitespace-nowrap text-sm border-collapse">
                            <thead><tr class="bg-slate-50 text-[10px] text-slate-500 uppercase"><th class="p-3 border-r border-slate-200">Semana 1</th><th class="p-3 border-r border-slate-200">Semana 2</th><th class="p-3 border-r border-slate-200">Semana 3</th><th class="p-3">Semana 4</th></tr></thead>
                            <tbody id="rep-table-semanas" class="divide-x divide-slate-100 font-semibold text-slate-700 border-t border-slate-200"></tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-4">Vendas por Produto (Top 10)</h3>
                    <div id="rep-chart-clientes" class="space-y-3 max-h-[260px] overflow-y-auto pr-2"></div>
                </div>
            </div>

        </div>
    </main>

    <!-- MODAL: ADD REPRESENTANTE -->
    <div id="modal-rep" class="modal fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9000] flex items-center justify-center opacity-0 hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl relative border-t-4 border-blue-600">
            <button onclick="app.closeModal('modal-rep')" class="absolute top-4 right-4 text-slate-400 hover:text-rose-500"><i class="fa-solid fa-xmark text-lg"></i></button>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Novo Representante</h3>
            <p class="text-xs text-slate-500 mb-5">Login automático (crie uma senha de acesso).</p>
            
            <form onsubmit="app.saveRep(event)" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2"><label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo</label><input required type="text" id="r-nome" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp</label><input required type="text" id="r-wpp" oninput="app.maskPhone(event)" maxlength="15" placeholder="(11) 99999-9999" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">E-mail (Login)</label><input required type="email" id="r-email" placeholder="vendedor@email.com" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-1"><label class="block text-xs font-semibold text-slate-700 mb-1">CEP</label><div class="relative"><input required type="text" id="r-cep" maxlength="9" oninput="app.maskCEP(event)" onblur="app.fetchCep(this.value)" placeholder="00000-000" class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-8 py-2 text-sm focus:border-blue-500 outline-none transition-colors"><i class="fa-solid fa-search absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i></div></div>
                    <div class="col-span-2"><label class="block text-xs font-semibold text-slate-700 mb-1">Endereço</label><input required type="text" id="r-end" placeholder="Rua, Bairro..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Pastas / Regiões</label><input required type="text" id="r-pastas" placeholder="Ex: Varejo SP" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                    <div><label class="block text-xs font-semibold text-slate-700 mb-1">Senha Inicial (Login)</label><input required type="text" id="r-pass" placeholder="Defina a senha" value="123" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-blue-500 outline-none transition-colors"></div>
                </div>
                <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-slate-100">
                    <button type="button" onclick="app.closeModal('modal-rep')" class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition text-sm">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition text-sm">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD VENDA (SEMANAL/EXPRESSA POR SKU) -->
    <div id="modal-venda" class="modal fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9000] flex items-center justify-center opacity-0 hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl relative border-t-[6px] border-emerald-500">
            <button onclick="app.closeModal('modal-venda')" class="absolute top-4 right-4 text-slate-400 hover:text-rose-500 text-lg"><i class="fa-solid fa-xmark"></i></button>
            <h3 class="text-lg font-bold text-emerald-800 mb-1">Lançar Faturamento Semanal</h3>
            <p class="text-xs font-medium text-slate-500 mb-4 flex items-center gap-1.5"><i class="fa-solid fa-user-tag text-emerald-500"></i> Rep: <span id="mv-rep-name" class="font-bold text-slate-700"></span></p>
            
            <form onsubmit="app.saveVenda(event)" class="space-y-4">
                <input type="hidden" id="mv-pd">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="mv-rep-container" class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Vendedor Responsável</label>
                        <select required id="mv-rep-id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-bold text-slate-700 focus:border-emerald-500 outline-none transition-colors cursor-pointer"></select>
                    </div>

                    <div id="mv-sku-container" class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Produto Vendido (SKU)</label>
                        <select required id="mv-sku" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-emerald-500 outline-none transition-colors cursor-pointer"></select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Semana de Referência</label>
                        <select required id="mv-semana" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-emerald-500 outline-none transition-colors cursor-pointer">
                            <option value="S1">Semana 1</option><option value="S2">Semana 2</option><option value="S3">Semana 3</option><option value="S4">Semana 4</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Volume Negociado (Kg)</label>
                        <input required type="number" step="0.01" id="mv-volume" placeholder="Ex: 500" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-colors">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Faturado (R$)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-sm">R$</span>
                            <input required type="text" id="mv-valor" oninput="app.maskMoney(event)" placeholder="0,00" class="w-full bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg pl-9 pr-3 py-2.5 text-lg font-bold focus:border-emerald-500 outline-none transition-colors">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-emerald-600 text-white font-semibold py-2.5 rounded-lg shadow-sm hover:bg-emerald-700 transition mt-4 text-sm">Confirmar Lançamento</button>
            </form>
        </div>
    </div>

    <!-- MODAL: INTELIGÊNCIA DE METAS (GESTOR - 100% MANUAL/FLEXÍVEL) -->
    <div id="modal-gestor-intel" class="modal fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9000] flex items-center justify-center opacity-0 hidden p-4">
        <div class="bg-white rounded-2xl p-6 md:p-8 w-full max-w-4xl shadow-2xl relative border-t-[6px] border-indigo-600 flex flex-col max-h-[90vh]">
            <button onclick="app.closeModal('modal-gestor-intel')" class="absolute top-4 right-4 text-slate-400 hover:text-rose-500 text-lg"><i class="fa-solid fa-xmark"></i></button>
            <div class="flex justify-between items-center mb-1">
                <h3 class="text-xl font-bold text-slate-900"><i class="fa-solid fa-wand-magic-sparkles text-indigo-600 mr-1"></i> Distribuição Inteligente</h3>
            </div>
            <p class="text-xs text-slate-500 mb-6">Preencha o histórico (Amarelo) para usar o Peso (%) automático, OU digite diretamente a Meta Final (R$) para cada um.</p>
            
            <div class="flex gap-4 mb-5 items-end bg-indigo-50 p-4 rounded-xl border border-indigo-100 shadow-sm">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-indigo-800 uppercase tracking-widest mb-1">Meta da Área Recebida</label>
                    <input type="text" id="g-intel-meta-area" readonly class="w-full bg-transparent text-xl font-black text-indigo-900 outline-none">
                </div>
                <div>
                    <button onclick="app.iaGestorPlan()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2 text-xs transition border border-indigo-200 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Puxar Automático (IA)
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto flex-1 pr-1 mb-4 border border-slate-200 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-white border-b border-slate-200 text-[9px] text-slate-500 uppercase tracking-widest font-bold">
                        <tr>
                            <th class="p-3">Representante</th>
                            <th class="p-3 w-24 text-center border-l border-slate-200 bg-amber-50 text-amber-800" id="th-m3">Mês -3</th>
                            <th class="p-3 w-24 text-center bg-amber-50 text-amber-800" id="th-m2">Mês -2</th>
                            <th class="p-3 w-24 text-center bg-amber-50 text-amber-800" id="th-m1">Mês -1</th>
                            <th class="p-3 w-28 text-right border-l border-slate-200">Média Calculada</th>
                            <th class="p-3 w-36 text-right text-indigo-600 bg-indigo-50/50">Meta Final (Editável)</th>
                        </tr>
                    </thead>
                    <tbody id="g-intel-grid" class="divide-y divide-slate-100 text-xs">
                        <!-- JS Injection -->
                    </tbody>
                </table>
            </div>

            <div class="mt-2 pt-4 border-t border-slate-200 flex justify-between items-center bg-slate-900 p-4 rounded-xl shadow-md">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Distribuído</p>
                    <p class="text-xl font-bold text-white leading-none mt-1" id="mi-total-soma">R$ 0,00</p>
                </div>
                <button id="btn-save-intel" onclick="app.salvarInteligencia()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm transition text-sm">
                    Salvar Metas
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS CORE -->
    <script>
        const state = { 
            users: [], areas: [], reps: [], metasGlobal: {}, metasRep: {}, vendas: [], 
            historicoManual: {}, skus: [],
            currentUser: null, activeTab: 'dashboard', charts: {}, 
            filters: { meses: [], areas: [], gestores: [], reps: [], initialized: false }
        };

        const utils = {
            id: () => Math.random().toString(36).substring(2,10),
            money: (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v||0),
            volume: (v) => new Intl.NumberFormat('pt-BR').format(v||0) + ' kg',
            escape: (v) => { const el = document.createElement('div'); el.textContent = v ?? ''; return el.innerHTML; },
            unmask: (s) => { if(!s) return 0; if(typeof s === 'number') return s; return parseFloat(String(s).replace(/\./g, '').replace(',', '.')) || 0; },
            formatInput: (v) => { if(v === undefined || v === null || isNaN(v)) return ""; return parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            csvCell: (value) => {
                let text = String(value ?? '');
                if(/^[=+\-@\t\r]/.test(text)) text = `'${text}`;
                return `"${text.replace(/"/g, '""')}"`;
            },
            downloadCsv: (filename, headers, rows) => {
                const lines = [headers, ...rows].map(row => row.map(utils.csvCell).join(';'));
                const blob = new Blob(["\ufeff"+lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 0);
                utils.toast('Relatório gerado com sucesso.');
            },
            toast: (msg, type='success') => {
                const c = document.getElementById('toast-container'); const t = document.createElement('div');
                t.className = `px-5 py-3 rounded-lg shadow-lg text-white font-semibold text-xs animate-[fadeIn_0.2s_ease-out] flex items-center gap-2 ${type==='error'?'bg-rose-600':'bg-emerald-600'}`;
                t.innerHTML = `<i class="fa-solid ${type==='error'?'fa-circle-exclamation':'fa-circle-check'} text-base"></i> ${msg}`;
                c.appendChild(t); setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(10px)'; t.style.transition='all 0.3s'; setTimeout(()=>t.remove(),300); }, 4000);
            },
            dialog: (type, title, msg, val='', btn='OK') => {
                return new Promise(res => {
                    const o = document.getElementById('custom-dialog-overlay'); const b = document.getElementById('custom-dialog-box');
                    if(!o || !b) return res(val);
                    document.getElementById('custom-dialog-title').innerText = title; document.getElementById('custom-dialog-message').innerText = msg;
                    const i = document.getElementById('custom-dialog-input'); const c = document.getElementById('custom-dialog-cancel');
                    const ok = document.getElementById('custom-dialog-confirm'); const ic = document.getElementById('custom-dialog-icon');
                    
                    i.classList.add('hidden'); c.classList.add('hidden');
                    if(type==='prompt') { i.classList.remove('hidden'); c.classList.remove('hidden'); i.value=val; ic.innerHTML='<i class="fa-solid fa-pen-to-square text-blue-500"></i>'; ok.className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg"; }
                    if(type==='confirm-danger') { c.classList.remove('hidden'); ic.innerHTML='<i class="fa-solid fa-triangle-exclamation text-rose-500"></i>'; ok.className="flex-1 px-4 py-2 bg-rose-600 text-white rounded-lg"; }
                    if(type==='alert') { ic.innerHTML='<i class="fa-solid fa-envelope-open-text text-blue-500"></i>'; ok.className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg"; }
                    ok.innerText = btn;

                    const close = (v) => { o.classList.add('opacity-0'); b.classList.replace('scale-100','scale-95'); setTimeout(()=>{ o.classList.add('hidden'); o.classList.remove('flex'); },200); ok.onclick=null; c.onclick=null; res(v); };
                    ok.onclick = () => close(type==='prompt'?i.value:true); c.onclick = () => close(false);
                    o.classList.remove('hidden'); o.classList.add('flex'); requestAnimationFrame(()=>{ o.classList.remove('opacity-0'); b.classList.replace('scale-95','scale-100'); });
                });
            },
            mesesNomes: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
        };

        const app = {
            api: async (url, options = {}) => {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        ...(options.headers || {})
                    }
                });
                const body = await response.json().catch(() => ({}));
                if(body.csrfToken) document.querySelector('meta[name="csrf-token"]').content = body.csrfToken;
                if(!response.ok) {
                    const message = Object.values(body.errors || {}).flat()[0] || body.message || 'Não foi possível concluir a operação.';
                    throw new Error(message);
                }
                return body;
            },
            hydrate: (data) => {
                state.users = data.users || []; state.areas = data.areas || []; state.reps = data.reps || [];
                state.metasGlobal = data.metasGlobal || {}; state.metasRep = data.metasRep || {}; state.vendas = data.vendas || [];
                state.historicoManual = data.historicoManual || {}; state.skus = data.skus || [];
            },
            init: async () => {
                const dataAtual = new Date(); const mesAtual = (dataAtual.getMonth()+1).toString().padStart(2,'0'); const anoAtual = dataAtual.getFullYear();
                const currentPeriod = `${anoAtual}-${mesAtual}`;
                
                const elIds = ['dash-start', 'dash-end', 'hier-date', 'plan-date', 'vendas-date', 'rep-dash-date'];
                elIds.forEach(id => { const el = document.getElementById(id); if(el) el.value = currentPeriod; });
                state.filters.meses = [currentPeriod];

                try {
                    const session = await app.api('/app/session');
                    if(session.requiresPasswordChange) {
                        window.pendingUserForPasswordChange = session.user;
                        app.switchAuth('reset');
                        return;
                    }
                    if(session.authenticated) {
                        app.hydrate(await app.api('/app/state'));
                        state.currentUser = session.user;
                        return app.loadPanel();
                    }
                } catch(error) {
                    utils.toast(error.message, 'error');
                }
                const authScr = document.getElementById('auth-screen'); if(authScr) authScr.classList.remove('hidden');
            },
            saveDb: async () => {
                try {
                    await app.api('/app/state', { method: 'PUT', body: JSON.stringify({
                        users: state.users, areas: state.areas, reps: state.reps, skus: state.skus,
                        metasGlobal: state.metasGlobal, metasRep: state.metasRep,
                        historicoManual: state.historicoManual, vendas: state.vendas
                    }) });
                } catch(error) {
                    utils.toast(error.message, 'error');
                    throw error;
                }
            },
            
            toggleSidebar: () => {
                const sb = document.getElementById('sidebar'); const ov = document.getElementById('mobile-overlay');
                if(sb && ov) { sb.classList.toggle('-translate-x-full'); ov.classList.toggle('hidden'); }
            },

            // --- AUTH ---
            switchAuth: (view) => {
                ['form-login', 'form-recovery', 'form-reset'].forEach(id => { const el = document.getElementById(id); if(el) el.classList.add('hidden'); });
                const err = document.getElementById('login-error-msg'); if(err) err.classList.add('hidden');
                const v = document.getElementById(`form-${view}`); if(v) v.classList.remove('hidden');
            },
            togglePass: (id, btn) => {
                const i = document.getElementById(id); if(!i) return;
                if(i.type==='password'){ i.type='text'; btn.innerHTML='<i class="fa-solid fa-eye-slash text-xs"></i>'; }
                else { i.type='password'; btn.innerHTML='<i class="fa-solid fa-eye text-xs"></i>'; }
            },
            login: async (e) => {
                e.preventDefault(); const err = document.getElementById('login-error-msg'); if(err) err.classList.add('hidden');
                const em = document.getElementById('login-email').value.trim().toLowerCase(); const pa = document.getElementById('login-pass').value.trim();
                const k = document.getElementById('login-keep').checked; 
                try {
                    const result = await app.api('/app/login', { method: 'POST', body: JSON.stringify({ email: em, password: pa, remember: k }) });
                    if(result.requiresPasswordChange) { window.pendingUserForPasswordChange = result.user; const p1=document.getElementById('reset-p1'); if(p1)p1.value=''; const p2=document.getElementById('reset-p2'); if(p2)p2.value=''; return app.switchAuth('reset'); }
                    app.hydrate(await app.api('/app/state')); state.currentUser = result.user; app.loadPanel();
                } catch(error) { if(err) { err.querySelector('span').innerText = error.message; err.classList.remove('hidden'); } }
            },
            recoverPass: async (e) => {
                e.preventDefault(); const em = document.getElementById('rec-email').value.trim().toLowerCase();
                try {
                    const result = await app.api('/app/recover', { method: 'POST', body: JSON.stringify({ email: em }) });
                    const temp = result.temporaryPassword;
                    await utils.dialog('alert', 'Simulação E-mail', `Destinatário: ${em}\n\nSenha provisória: ${temp}\n\nTroque no primeiro acesso.`);
                    const emL = document.getElementById('login-email'); if(emL) emL.value = em; const paL = document.getElementById('login-pass'); if(paL) paL.value = temp;
                    app.switchAuth('login');
                } catch(error) { utils.toast(error.message, 'error'); }
            },
            forceReset: async (e) => {
                e.preventDefault(); const p1 = document.getElementById('reset-p1').value; const p2 = document.getElementById('reset-p2').value;
                if(p1!==p2) return utils.toast("Senhas não conferem.", "error");
                try {
                    const result = await app.api('/app/reset-password', { method: 'POST', body: JSON.stringify({ password: p1, password_confirmation: p2 }) });
                    app.hydrate(await app.api('/app/state')); state.currentUser = result.user; app.loadPanel(); utils.toast("Senha gravada!");
                } catch(error) { utils.toast(error.message, 'error'); }
            },
            logout: async () => {
                try { await app.api('/app/logout', { method: 'POST', body: '{}' }); } catch(error) {}
                state.currentUser = null;
                const authScr = document.getElementById('auth-screen'); if(authScr) authScr.classList.remove('hidden');
                const f = document.getElementById('form-login'); if(f) f.reset(); app.switchAuth('login');
            },

            // --- NAVEGAÇÃO E PERFIS ---
            loadPanel: () => {
                const authScr = document.getElementById('auth-screen'); if(authScr) authScr.classList.add('hidden');
                const u = state.currentUser; const isAd = u.role === 'admin' || u.role === 'diretor'; const isRep = u.role === 'representante';
                state.filters = { meses: [], areas: [], gestores: [], reps: [], initialized: false };
                const nameEl = document.getElementById('ui-user-name'); if(nameEl) nameEl.innerText = u.nome;
                const nav = document.getElementById('nav-menu');
                
                if (isAd) {
                    document.getElementById('ui-role-title').innerText = 'Diretoria';
                    document.getElementById('ui-role-icon').className = 'fa-solid fa-user-tie';
                    nav.innerHTML = `
                        <p class="px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-2 mb-2">Visão Geral</p>
                        <button onclick="app.switchTab('dashboard')" id="btn-tab-dashboard" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-chart-pie w-4 text-center"></i> Dashboard</button>
                        <button onclick="app.switchTab('hierarquia')" id="btn-tab-hierarquia" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-sitemap w-4 text-center"></i> Mapa de Hierarquia</button>
                        <p class="px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-6 mb-2">Administração</p>
                        <button onclick="app.switchTab('plan')" id="btn-tab-plan" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all mt-1 bg-indigo-900/30 text-indigo-300 hover:text-white text-sm"><i class="fa-solid fa-bullseye w-4 text-center"></i> Plan. Global</button>
                        <button onclick="app.switchTab('areas')" id="btn-tab-areas" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-building w-4 text-center"></i> Áreas Comerciais</button>
                        <button onclick="app.switchTab('gestores')" id="btn-tab-gestores" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-user-cog w-4 text-center"></i> Contas Gestores</button>
                        <button onclick="app.switchTab('skus')" id="btn-tab-skus" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm mt-1"><i class="fa-solid fa-boxes-stacked w-4 text-center"></i> SKUs & Produtos</button>
                        <div class="mt-4 px-1 pb-4">
                            <button onclick="app.switchTab('relatorios')" id="btn-tab-relatorios" class="sidebar-btn flex items-center gap-2.5 w-full px-4 py-3 rounded-xl transition-all bg-emerald-900/30 text-emerald-400 hover:text-white text-sm font-semibold"><i class="fa-solid fa-file-csv w-4 text-center"></i> Relatórios</button>
                        </div>
                    `;
                    document.getElementById('filter-box-area').classList.remove('hidden'); document.getElementById('filter-box-gestor').classList.remove('hidden');
                    document.getElementById('div-area').classList.remove('hidden'); document.getElementById('div-gestor').classList.remove('hidden');
                } else if (!isRep) {
                    const area = state.areas.find(a=>a.id===u.areaId);
                    document.getElementById('ui-role-title').innerText = `Gestor de Área`;
                    document.getElementById('ui-role-icon').className = 'fa-solid fa-user-tag';
                    nav.innerHTML = `
                        <p class="px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-2 mb-2">Visão Geral</p>
                        <button onclick="app.switchTab('dashboard')" id="btn-tab-dashboard" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-chart-pie w-4 text-center"></i> Dashboard</button>
                        <p class="px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-6 mb-2">Gestão</p>
                        <button onclick="app.switchTab('equipe')" id="btn-tab-equipe" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-users w-4 text-center"></i> Representantes</button>
                        <button onclick="app.switchTab('vendas')" id="btn-tab-vendas" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-target w-4 text-center"></i> Metas & Vendas</button>
                        <div class="mt-4 px-1 pb-4">
                            <button onclick="app.switchTab('relatorios')" id="btn-tab-relatorios" class="sidebar-btn flex items-center gap-2.5 w-full px-4 py-3 rounded-xl transition-all bg-emerald-900/30 text-emerald-400 hover:text-white text-sm font-semibold"><i class="fa-solid fa-file-csv w-4 text-center"></i> Relatórios</button>
                        </div>
                    `;
                    document.getElementById('filter-box-area').classList.add('hidden'); document.getElementById('filter-box-gestor').classList.add('hidden');
                    document.getElementById('div-area').classList.add('hidden'); document.getElementById('div-gestor').classList.add('hidden');
                } else {
                    document.getElementById('ui-role-title').innerText = `Representante`;
                    document.getElementById('ui-role-icon').className = 'fa-solid fa-briefcase';
                    nav.innerHTML = `
                        <p class="px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-2 mb-2">Vendas</p>
                        <button onclick="app.switchTab('rep')" id="btn-tab-rep" class="sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg font-semibold transition-all text-sm"><i class="fa-solid fa-chart-line w-4 text-center"></i> Meu Desempenho</button>
                        <div class="mt-4 px-1 pb-4">
                            <button onclick="app.switchTab('relatorios')" id="btn-tab-relatorios" class="sidebar-btn flex items-center gap-2.5 w-full px-4 py-3 rounded-xl transition-all bg-emerald-900/30 text-emerald-400 hover:text-white text-sm font-semibold"><i class="fa-solid fa-file-csv w-4 text-center"></i> Meus Relatórios</button>
                        </div>
                    `;
                }

                const sb = document.getElementById('sidebar'); const ov = document.getElementById('mobile-overlay');
                if(sb && ov) { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); }

                if (isRep) app.switchTab('rep'); else app.switchTab('dashboard');
            },
            switchTab: (tab) => {
                state.activeTab = tab;
                document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                const tEl = document.getElementById(`tab-${tab}`); if(tEl) tEl.classList.add('active');
                
                document.querySelectorAll('#nav-menu button').forEach(el => { 
                    el.className = el.id.includes('relatorios') 
                    ? "sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg transition-all mt-1 text-sm font-semibold bg-emerald-900/30 text-emerald-400 hover:text-white"
                    : (el.id.includes('plan') ? "sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg transition-all mt-1 text-sm font-semibold bg-indigo-900/30 text-indigo-300 hover:text-white" : "sidebar-btn flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg transition-all text-sm font-semibold"); 
                });
                const btn = document.getElementById(`btn-tab-${tab}`);
                if(btn) { btn.classList.add('sidebar-btn-active'); btn.classList.remove('sidebar-btn', 'bg-emerald-900/30', 'bg-indigo-900/30'); }
                
                const sb = document.getElementById('sidebar'); const ov = document.getElementById('mobile-overlay');
                if(sb && ov && !sb.classList.contains('-translate-x-full')) { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); }

                if(tab==='dashboard') app.buildFilterLists();
                if(tab==='areas') app.renderAdminAreas();
                if(tab==='gestores') app.renderAdminGestores();
                if(tab==='plan') app.renderPlanDir();
                if(tab==='equipe') app.renderEquipe();
                if(tab==='vendas') app.renderVendas();
                if(tab==='rep') app.renderRepDashboard();
                if(tab==='hierarquia') app.renderHierarquia();
                if(tab==='skus') app.renderSkus();
                if(tab==='relatorios') {
                    const bxA = document.getElementById('box-rel-areas');
                    const bxS = document.getElementById('box-rel-skus');
                    const bxR = document.getElementById('box-rel-reps');
                    const isAd = state.currentUser.role === 'admin' || state.currentUser.role === 'diretor';
                    const isGestor = state.currentUser.role === 'gerente';
                    
                    if(bxA) bxA.style.display = isAd ? 'block' : 'none';
                    if(bxS) bxS.style.display = isAd ? 'block' : 'none';
                    if(bxR) bxR.style.display = (isAd || isGestor) ? 'block' : 'none';
                }
            },

            // --- UI BÁSICA ---
            openModal: (id) => { const m = document.getElementById(id); if(m) { m.classList.remove('hidden'); m.classList.add('flex'); setTimeout(()=>{m.classList.remove('opacity-0');},10); } },
            closeModal: (id) => { const m = document.getElementById(id); if(m) { m.classList.add('opacity-0'); setTimeout(()=> { m.classList.add('hidden'); m.classList.remove('flex'); }, 200); } },
            maskPhone: (e) => window.phoneMask(e.target),
            maskCEP: (e) => { let v=e.target.value.replace(/\D/g,''); if(v.length>5) v=`${v.slice(0,5)}-${v.slice(5,8)}`; e.target.value=v; },
            maskMoney: (e) => {
                let v = e.target.value.replace(/\D/g, ""); if (v === "") { e.target.value = ""; return; }
                v = (parseInt(v) / 100).toFixed(2) + ""; v = v.replace(".", ","); v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1."); e.target.value = v;
            },
            fetchCep: async (val) => {
                const cep = val.replace(/\D/g,'');
                if(cep.length===8) { try { const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`); const d = await res.json(); const rEnd = document.getElementById('r-end'); if(!d.erro && rEnd) rEnd.value = `${d.logradouro}, ${d.bairro} - ${d.localidade}/${d.uf}`; } catch(e){} }
            },
            toggleDropdown: (id) => {
                document.querySelectorAll('.dropdown-menu').forEach(el => { if(el.id!==id) el.classList.remove('active'); });
                const el = document.getElementById(id); if(el) el.classList.toggle('active');
            },
            filteredAreaIds: () => {
                const isDirector = state.currentUser.role === 'admin' || state.currentUser.role === 'diretor';
                if(!isDirector) return [state.currentUser.areaId];

                const managerAreas = new Set(state.users
                    .filter(user => user.role === 'gerente' && state.filters.gestores.includes(user.id))
                    .map(user => user.areaId));
                return state.filters.areas.filter(areaId => managerAreas.has(areaId));
            },
            selectAllVisibleReps: () => {
                const visibleAreas = app.filteredAreaIds();
                state.filters.reps = state.reps.filter(rep => visibleAreas.includes(rep.areaId)).map(rep => rep.id);
            },

            // --- CHECKBOX FILTERS ---
            buildFilterLists: () => {
                const u = state.currentUser; const isAd = u.role === 'admin' || u.role === 'diretor';
                const dataAtual = new Date();
                const currentPeriod = `${dataAtual.getFullYear()}-${String(dataAtual.getMonth()+1).padStart(2, '0')}`;
                let pds = new Set();
                state.vendas.forEach(v => pds.add(v.period)); 
                Object.keys(state.metasGlobal).forEach(p => pds.add(p));
                Object.keys(state.metasRep).forEach(p => pds.add(p));
                const sortedPds = Array.from(pds).sort().reverse();

                if(!state.filters.initialized) {
                    state.filters.meses = [pds.has(currentPeriod) ? currentPeriod : sortedPds[0]].filter(Boolean);
                    state.filters.areas = isAd ? state.areas.map(area => area.id) : [];
                    state.filters.gestores = isAd ? state.users.filter(user => user.role === 'gerente').map(user => user.id) : [];
                    const initialAreas = isAd ? state.filters.areas : [u.areaId];
                    state.filters.reps = state.reps.filter(rep => initialAreas.includes(rep.areaId)).map(rep => rep.id);
                    state.filters.initialized = true;
                }
                
                const listM = document.getElementById('list-cb-meses');
                if(listM) {
                    listM.innerHTML = sortedPds.map(m => `
                        <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="checkbox" value="${m}" ${state.filters.meses.includes(m)?'checked':''} onchange="app.updateFilters('meses', this)" class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                            <span class="text-xs font-semibold text-slate-700">${m}</span>
                        </label>`).join('');
                }
                const lblM = document.getElementById('lbl-meses'); if(lblM) lblM.innerText = state.filters.meses.length ? `${state.filters.meses.length} Mês(es)` : 'Selecione';

                if(isAd) {
                    const listA = document.getElementById('list-cb-areas');
                    if(listA) {
                        listA.innerHTML = `
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition border-b border-slate-100 mb-1">
                                <input type="checkbox" onchange="app.toggleAll('areas', this)" ${state.filters.areas.length===state.areas.length?'checked':''} class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                                <span class="text-[9px] font-bold text-slate-800 uppercase tracking-wider">Todas</span>
                            </label>` + state.areas.map(a => `
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                                <input type="checkbox" value="${a.id}" ${state.filters.areas.includes(a.id)?'checked':''} onchange="app.updateFilters('areas', this)" class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                                <span class="text-xs font-semibold text-slate-700">${a.nome}</span>
                            </label>`).join('');
                    }
                    const lblA = document.getElementById('lbl-areas'); if(lblA) lblA.innerText = state.filters.areas.length===state.areas.length ? 'Todas Áreas' : `${state.filters.areas.length} Área(s)`;
                }

                if(isAd) {
                    const mgrs = state.users.filter(x=>x.role==='gerente');
                    const listG = document.getElementById('list-cb-gestores');
                    if(listG) {
                        listG.innerHTML = `
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition border-b border-slate-100 mb-1">
                                <input type="checkbox" onchange="app.toggleAll('gestores', this)" ${state.filters.gestores.length===mgrs.length?'checked':''} class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                                <span class="text-[9px] font-bold text-slate-800 uppercase tracking-wider">Todos</span>
                            </label>` + mgrs.map(g => `
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                                <input type="checkbox" value="${g.id}" ${state.filters.gestores.includes(g.id)?'checked':''} onchange="app.updateFilters('gestores', this)" class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                                <span class="text-xs font-semibold text-slate-700">${g.nome}</span>
                            </label>`).join('');
                    }
                    const lblG = document.getElementById('lbl-gestores'); if(lblG) lblG.innerText = state.filters.gestores.length===mgrs.length ? 'Todos Gestores' : `${state.filters.gestores.length} Gestor(es)`;
                }

                const visAreas = app.filteredAreaIds();
                const repsCtx = state.reps.filter(r => visAreas.includes(r.areaId));
                const listR = document.getElementById('list-cb-reps');
                if(listR) {
                    listR.innerHTML = `
                        <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition border-b border-slate-100 mb-1">
                            <input type="checkbox" onchange="app.toggleAll('reps', this)" ${state.filters.reps.length===repsCtx.length?'checked':''} class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                            <span class="text-[9px] font-bold text-slate-800 uppercase tracking-wider">Todos</span>
                        </label>` + repsCtx.map(r => `
                        <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded-lg cursor-pointer transition">
                            <input type="checkbox" value="${r.id}" ${state.filters.reps.includes(r.id)?'checked':''} onchange="app.updateFilters('reps', this)" class="rounded text-blue-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                            <span class="text-xs font-semibold text-slate-700">${r.nome}</span>
                        </label>`).join('');
                }
                const lblR = document.getElementById('lbl-reps'); if(lblR) lblR.innerText = state.filters.reps.length===repsCtx.length ? 'Todos Reps' : `${state.filters.reps.length} Rep(s)`;

                app.renderDashboardData();
            },
            toggleAll: (tipo, el) => {
                const c = el.checked;
                if(tipo==='areas') state.filters.areas = c ? state.areas.map(a=>a.id) : [];
                if(tipo==='gestores') state.filters.gestores = c ? state.users.filter(x=>x.role==='gerente').map(a=>a.id) : [];
                if(tipo==='reps') {
                    if(c) app.selectAllVisibleReps();
                    else state.filters.reps = [];
                }
                if(tipo==='areas' || tipo==='gestores') app.selectAllVisibleReps();
                app.buildFilterLists();
            },
            updateFilters: (tipo, el) => {
                const v = el.value;
                if(el.checked) { if(!state.filters[tipo].includes(v)) state.filters[tipo].push(v); }
                else { state.filters[tipo] = state.filters[tipo].filter(x => x !== v); }
                if(tipo==='areas' || tipo==='gestores') app.selectAllVisibleReps();
                app.buildFilterLists();
            },

            // --- DASHBOARD DATA ---
            renderDashboardData: () => {
                const u = state.currentUser; const isAd = u.role === 'admin' || u.role === 'diretor';
                const dataAtual = new Date(); const mesAtual = (dataAtual.getMonth()+1).toString().padStart(2,'0');
                const currentPeriod = `${dataAtual.getFullYear()}-${mesAtual}`;
                
                const fMeses = state.filters.meses;
                const fReps = new Set(state.filters.reps);

                let totMeta = 0; let totReal = 0; let totVol = 0; const mapRep = {}; const mapCli = {}; const mapArea = {};
                
                fMeses.forEach(pd => {
                    if(state.metasRep[pd]) {
                        Object.entries(state.metasRep[pd]).forEach(([rId, val]) => {
                            if(fReps.has(rId)) { totMeta += val; if(!mapRep[rId]) mapRep[rId] = {m:0, v:0, nome: state.reps.find(x=>x.id===rId)?.nome||''}; mapRep[rId].m += val; }
                        });
                    }
                });

                state.vendas.forEach(v => {
                    if(fMeses.includes(v.period) && fReps.has(v.repId)) {
                        totReal += v.valor;
                        if(v.volume) totVol += v.volume;
                        if(!mapRep[v.repId]) mapRep[v.repId] = {m:0, v:0, nome: state.reps.find(x=>x.id===v.repId)?.nome||''};
                        mapRep[v.repId].v += v.valor; 
                        
                        const clientName = v.cliente || 'Cliente não informado';
                        mapCli[clientName] = (mapCli[clientName]||0) + v.valor;
                        mapArea[v.areaId] = (mapArea[v.areaId]||0) + v.valor;
                    }
                });

                const perc = totMeta > 0 ? (totReal / totMeta) * 100 : 0;
                const km = document.getElementById('kpi-meta'); if(km) km.innerText = utils.money(totMeta);
                const kr = document.getElementById('kpi-real'); if(kr) kr.innerText = utils.money(totReal);
                const kv = document.getElementById('kpi-vol'); if(kv) kv.innerText = `Volume: ${utils.volume(totVol)}`;
                const kp = document.getElementById('kpi-perc'); if(kp) kp.innerText = perc.toFixed(1) + '%';
                
                const kStatus = document.getElementById('kpi-status');
                if(kStatus) {
                    if(perc >= 100) { kStatus.className = 'mt-3 text-[9px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 uppercase tracking-widest border border-emerald-200'; kStatus.innerText = 'Meta Batida'; }
                    else if(perc >= 70) { kStatus.className = 'mt-3 text-[9px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700 uppercase tracking-widest border border-blue-200'; kStatus.innerText = 'No Caminho'; }
                    else { kStatus.className = 'mt-3 text-[9px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-700 uppercase tracking-widest border border-rose-200'; kStatus.innerText = 'Alerta'; }
                }

                const kBar = document.getElementById('kpi-bar-mini');
                if(kBar) {
                    kBar.style.width = Math.min(perc, 100) + '%';
                    kBar.className = `h-1.5 rounded-full transition-all duration-1000 ${perc>=100?'bg-emerald-500':'bg-blue-600'}`;
                }

                let verde=0, azul=0, verm=0;
                Object.values(mapRep).forEach(r => { const mp = r.m ? (r.v / r.m) : 0; if(mp>=1) verde++; else if(mp>=0.7) azul++; else verm++; });
                const tv = document.getElementById('term-verde'); if(tv) tv.innerText = verde; 
                const ta = document.getElementById('term-azul'); if(ta) ta.innerText = azul; 
                const tr = document.getElementById('term-verm'); if(tr) tr.innerText = verm;
                
                const kRun = document.getElementById('kpi-runrate');
                if(kRun) {
                    if(fMeses.length === 1 && fMeses[0] === currentPeriod) {
                        const currentDay = dataAtual.getDate();
                        const daysInMonth = new Date(dataAtual.getFullYear(), dataAtual.getMonth() + 1, 0).getDate();
                        if(currentDay > 0) {
                            const proj = (totReal / currentDay) * daysInMonth;
                            kRun.innerText = utils.money(proj);
                        }
                    } else {
                        kRun.innerText = "Fechado / Multi-Mês";
                    }
                }

                Chart.defaults.font.family = 'Inter'; Chart.defaults.color = '#64748b'; Chart.defaults.font.size = 11;

                const arrReps = Object.values(mapRep).map(x => ({ ...x, p: x.m>0 ? (x.v/x.m)*100:0 })).sort((a,b)=>b.p-a.p).slice(0,5);
                if(state.charts['c1']) state.charts['c1'].destroy();
                const ctx1 = document.getElementById('chart-reps-perc');
                if(ctx1) {
                    state.charts['c1'] = new Chart(ctx1.getContext('2d'), {
                        type: 'bar',
                        data: { labels: arrReps.map(x=>x.nome.split(' ')[0]), datasets: [{ data: arrReps.map(x=>x.p), backgroundColor: arrReps.map(x=>x.p>=100?'#10b981':'#f59e0b'), borderRadius: 4 }] },
                        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { grid: {display: false}, ticks: {font: {weight: 'bold'}} } } }
                    });
                }

                const arrEq = Object.values(mapRep).sort((a,b)=>b.v-a.v);
                if(state.charts['c2']) state.charts['c2'].destroy();
                const ctx2 = document.getElementById('chart-equipe');
                if(ctx2) {
                    state.charts['c2'] = new Chart(ctx2.getContext('2d'), {
                        type: 'bar',
                        data: { labels: arrEq.map(x=>x.nome.split(' ')[0]), datasets: [ { label: 'Meta', data: arrEq.map(x=>x.m), backgroundColor: '#94a3b8', borderRadius: 4 }, { label: 'Real', data: arrEq.map(x=>x.v), backgroundColor: '#3b82f6', borderRadius: 4 } ] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: {weight: 'bold'} } } }, scales: { y: { display: false }, x: { grid: {display: false}, ticks: {font: {weight: 'bold'}} } } }
                    });
                }

                const boxA = document.getElementById('box-chart-area');
                if(boxA) boxA.style.display = isAd ? 'flex' : 'none';
                if(isAd) {
                    if(state.charts['c3']) state.charts['c3'].destroy();
                    const ctx3 = document.getElementById('chart-areas');
                    if(ctx3) {
                        const aLabs = []; const aData = [];
                        state.areas.forEach(a => { if(mapArea[a.id]) { aLabs.push(a.nome); aData.push(mapArea[a.id]); } });
                        state.charts['c3'] = new Chart(ctx3.getContext('2d'), {
                            type: 'doughnut',
                            data: { labels: aLabs, datasets: [{ data: aData, backgroundColor: ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#f43f5e'], borderWidth: 0 }] },
                            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, font: {weight: 'bold'} } } } }
                        });
                    }
                }

                if(state.charts['c4']) state.charts['c4'].destroy();
                const anoAtualEvol = dataAtual.getFullYear(); 
                const elEvol = document.getElementById('evol-year'); if(elEvol) elEvol.innerText = anoAtualEvol;
                const arrEvol = Array(12).fill(0);
                state.vendas.forEach(v => { const [vy, vm] = v.period.split('-'); if(vy == anoAtualEvol && fReps.has(v.repId)) arrEvol[parseInt(vm)-1] += v.valor; });
                const ctx4 = document.getElementById('chart-evol');
                if(ctx4) {
                    let grad = ctx4.getContext('2d').createLinearGradient(0,0,0,200); grad.addColorStop(0, 'rgba(59, 130, 246, 0.2)'); grad.addColorStop(1, 'rgba(59, 130, 246, 0)');
                    state.charts['c4'] = new Chart(ctx4.getContext('2d'), {
                        type: 'line',
                        data: { labels: utils.mesesNomes.map(m=>m.substring(0,3)), datasets: [{ label: 'Faturamento', data: arrEvol, borderColor: '#3b82f6', backgroundColor: grad, borderWidth: 3, fill: true, tension: 0.4, pointRadius: 0 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { grid: {display: false}, ticks: {font: {weight: 'bold'}} } } }
                    });
                }

                const topCli = Object.entries(mapCli).sort((a,b)=>b[1]-a[1]).slice(0,10);
                const elCli = document.getElementById('chart-clientes');
                if(elCli) {
                    if(!topCli.length) elCli.innerHTML = `<p class="text-slate-400 text-xs italic py-4 text-center font-bold">Sem dados no período.</p>`;
                    else elCli.innerHTML = topCli.map(c => `
                        <div class="mb-2">
                            <div class="flex justify-between text-[11px] font-bold text-slate-700 mb-1"><span class="truncate pr-2">${c[0]}</span><span class="text-indigo-600">${utils.money(c[1])}</span></div>
                            <div class="h-1.5 bg-slate-100 rounded-full"><div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" style="width: ${(c[1]/topCli[0][1])*100}%"></div></div>
                        </div>`).join('');
                }
            },

            // --- MAPA DE HIERARQUIA ---
            renderHierarquia: () => {
                const dateEl = document.getElementById('hier-date');
                const container = document.getElementById('hierarquia-container');
                if(!dateEl || !container) return;

                const today = new Date();
                const currentPeriod = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2, '0')}`;
                const period = dateEl.value || currentPeriod;
                dateEl.value = period;

                const periodGoals = state.metasRep[period] || {};
                const periodSales = state.vendas.filter(v => v.period === period);
                const globalGoal = state.metasGlobal[period] || { total: 0, areasMeta: {} };
                const totalGoal = Number(globalGoal.total) || Object.values(periodGoals).reduce((total, value) => total + Number(value || 0), 0);
                const totalSales = periodSales.reduce((total, sale) => total + Number(sale.valor || 0), 0);
                const totalPercentage = totalGoal > 0 ? (totalSales / totalGoal) * 100 : 0;

                const performance = (percentage) => {
                    if(percentage >= 100) return { label: 'Meta batida', badge: 'bg-emerald-100 text-emerald-700 border-emerald-200', bar: 'bg-emerald-500' };
                    if(percentage >= 70) return { label: 'No caminho', badge: 'bg-blue-100 text-blue-700 border-blue-200', bar: 'bg-blue-500' };
                    return { label: 'Alerta', badge: 'bg-rose-100 text-rose-700 border-rose-200', bar: 'bg-rose-500' };
                };

                const directorPerformance = performance(totalPercentage);
                const areaCards = state.areas.map(area => {
                    const representatives = state.reps.filter(rep => rep.areaId === area.id);
                    const representativeIds = new Set(representatives.map(rep => rep.id));
                    const manager = state.users.find(user => user.role === 'gerente' && user.areaId === area.id);
                    const areaGoal = Number(globalGoal.areasMeta?.[area.id]) || representatives.reduce((total, rep) => total + Number(periodGoals[rep.id] || 0), 0);
                    const areaSales = periodSales.filter(sale => representativeIds.has(sale.repId)).reduce((total, sale) => total + Number(sale.valor || 0), 0);
                    const areaPercentage = areaGoal > 0 ? (areaSales / areaGoal) * 100 : 0;
                    const areaPerformance = performance(areaPercentage);

                    const representativeCards = representatives.map(rep => {
                        const goal = Number(periodGoals[rep.id] || 0);
                        const sales = periodSales.filter(sale => sale.repId === rep.id).reduce((total, sale) => total + Number(sale.valor || 0), 0);
                        const percentage = goal > 0 ? (sales / goal) * 100 : 0;
                        const repPerformance = performance(percentage);

                        return `
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-slate-800" title="${utils.escape(rep.nome)}">${utils.escape(rep.nome)}</p>
                                        <p class="mt-0.5 text-[9px] font-bold uppercase tracking-wider text-slate-400">Representante</p>
                                    </div>
                                    <span class="shrink-0 rounded border px-1.5 py-0.5 text-[8px] font-bold uppercase ${repPerformance.badge}">${percentage.toFixed(0)}%</span>
                                </div>
                                <div class="mt-2 flex justify-between gap-2 text-[10px] font-semibold">
                                    <span class="text-slate-400">${utils.money(goal)}</span>
                                    <span class="text-slate-700">${utils.money(sales)}</span>
                                </div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full rounded-full ${repPerformance.bar}" style="width: ${Math.min(percentage, 100)}%"></div>
                                </div>
                            </div>`;
                    }).join('');

                    return `
                        <section class="tree-card overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-100 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"><i class="fa-solid fa-building"></i></div>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-bold text-slate-900">${utils.escape(area.nome)}</h3>
                                            <p class="truncate text-[10px] font-medium text-slate-500">${manager ? utils.escape(manager.nome) : 'Gestor não definido'}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded border px-2 py-1 text-[9px] font-bold uppercase ${areaPerformance.badge}">${areaPerformance.label}</span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <div><p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Meta</p><p class="mt-0.5 text-sm font-bold text-slate-700">${utils.money(areaGoal)}</p></div>
                                    <div class="text-right"><p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Realizado</p><p class="mt-0.5 text-sm font-bold text-emerald-600">${utils.money(areaSales)}</p></div>
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full ${areaPerformance.bar}" style="width: ${Math.min(areaPercentage, 100)}%"></div></div>
                                <p class="mt-1.5 text-right text-[9px] font-bold text-slate-400">${areaPercentage.toFixed(1)}% de atingimento</p>
                            </div>
                            <div class="grid grid-cols-1 gap-2 p-3 sm:grid-cols-2">
                                ${representativeCards || '<p class="col-span-full py-5 text-center text-xs font-semibold text-slate-400">Nenhum representante vinculado.</p>'}
                            </div>
                        </section>`;
                }).join('');

                container.innerHTML = `
                    <div class="flex flex-col items-center">
                        <div class="tree-card w-full max-w-md rounded-xl bg-slate-900 p-5 text-white shadow-lg">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-lg"><i class="fa-solid fa-user-tie"></i></div>
                                    <div><p class="text-[9px] font-bold uppercase tracking-[0.2em] text-blue-300">Diretoria comercial</p><h3 class="mt-0.5 text-base font-bold">Riviera Pescados</h3></div>
                                </div>
                                <span class="rounded border px-2 py-1 text-[9px] font-bold uppercase ${directorPerformance.badge}">${directorPerformance.label}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3 border-t border-slate-700 pt-4 text-center">
                                <div><p class="text-[9px] font-bold uppercase text-slate-400">Meta</p><p class="mt-1 text-xs font-bold">${utils.money(totalGoal)}</p></div>
                                <div><p class="text-[9px] font-bold uppercase text-slate-400">Realizado</p><p class="mt-1 text-xs font-bold text-emerald-400">${utils.money(totalSales)}</p></div>
                                <div><p class="text-[9px] font-bold uppercase text-slate-400">Atingimento</p><p class="mt-1 text-xs font-bold text-blue-300">${totalPercentage.toFixed(1)}%</p></div>
                            </div>
                        </div>
                        <div class="h-8 w-px bg-slate-300"></div>
                        <div class="h-px w-3/4 bg-slate-300"></div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">${areaCards}</div>`;
            },

            // --- PRODUTOS / SKUS ---
            addSku: (e) => {
                e.preventDefault();
                state.skus.push({
                    id: 'sku_'+utils.id(), nome: document.getElementById('add-sku-nome').value.trim(),
                    categoria: document.getElementById('add-sku-cat').value.trim(),
                    gramatura: document.getElementById('add-sku-peso').value.trim()
                });
                app.saveDb(); e.target.reset(); app.renderSkus(); utils.toast('Produto adicionado.');
            },
            renderSkus: () => {
                const list = document.getElementById('list-skus'); if(!list) return;
                if(!state.skus.length) return list.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-slate-400 font-bold">Nenhum produto cadastrado.</td></tr>`;
                list.innerHTML = state.skus.map(sku => `
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-bold text-slate-800">${sku.nome}</td>
                        <td class="p-3 text-slate-600">${sku.categoria}</td>
                        <td class="p-3 text-slate-600">${sku.gramatura}</td>
                        <td class="p-3 text-right"><button onclick="app.delSku('${sku.id}')" class="text-slate-400 hover:text-rose-500 px-2"><i class="fa-solid fa-trash text-xs"></i></button></td>
                    </tr>`).join('');
            },
            delSku: async (id) => {
                if(state.vendas.some(v => v.skuId === id)) return utils.toast('Produto possui vendas vinculadas.', 'error');
                if(await utils.dialog('confirm-danger', 'Excluir Produto', 'Tem certeza?', '', 'Excluir')) {
                    state.skus = state.skus.filter(s => s.id !== id); app.saveDb(); app.renderSkus();
                }
            },

            // --- ADMINISTRAÇÃO ---
            addArea: (e) => {
                e.preventDefault(); const n = document.getElementById('add-area-nome').value.trim();
                state.areas.push({ id: 'a_'+utils.id(), nome: n }); app.saveDb(); utils.toast("Área criada.");
                document.getElementById('add-area-nome').value = ''; app.renderAdminAreas();
            },
            renderAdminAreas: () => {
                const ls = document.getElementById('list-areas');
                if(ls) {
                    if(state.areas.length === 0) ls.innerHTML = `<p class="text-xs text-slate-400">Nenhuma área.</p>`;
                    else ls.innerHTML = state.areas.map(a => `
                        <div class="p-3 bg-white border border-slate-200 rounded-lg flex justify-between items-center shadow-sm">
                            <span class="font-bold text-slate-800 text-sm flex items-center gap-2"><i class="fa-solid fa-map-location-dot text-blue-500 text-xs"></i> ${a.nome}</span>
                            <div>
                                <button onclick="app.editArea('${a.id}')" class="text-slate-400 hover:text-blue-500 px-2 transition-colors"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button onclick="app.delArea('${a.id}')" class="text-slate-400 hover:text-rose-500 px-2 transition-colors"><i class="fa-solid fa-trash text-xs"></i></button>
                            </div>
                        </div>`).join('');
                }
            },
            editArea: async (id) => {
                const a = state.areas.find(x=>x.id===id);
                const n = await utils.dialog('prompt', 'Editar Área', 'Novo nome para a área:', a.nome);
                if(n && n.trim()!=='') { a.nome = n.trim(); app.saveDb(); app.renderAdminAreas(); utils.toast("Área atualizada."); }
            },
            delArea: async (id) => {
                if(state.users.some(u=>u.areaId===id)) return utils.toast("Área possui Gestor.", "error");
                if(await utils.dialog('confirm-danger', 'Excluir Área', 'Tem certeza?', '', 'Excluir')) { state.areas = state.areas.filter(a=>a.id!==id); app.saveDb(); app.renderAdminAreas(); }
            },
            addGestor: (e) => {
                e.preventDefault();
                const em = document.getElementById('add-gestor-email').value.trim().toLowerCase();
                if(state.users.some(u=>u.email===em)) return utils.toast("E-mail já existe.", "error");
                state.users.push({ id: 'u_'+utils.id(), role: 'gerente', nome: document.getElementById('add-gestor-nome').value, email: em, pass: document.getElementById('add-gestor-pass').value, areaId: document.getElementById('add-gestor-area').value, telefone: document.getElementById('add-gestor-tel').value, mustChangePassword: true });
                app.saveDb(); utils.toast("Gestor cadastrado."); e.target.reset(); app.renderAdminGestores();
            },
            renderAdminGestores: () => {
                const addA = document.getElementById('add-gestor-area'); if(addA) addA.innerHTML = `<option disabled selected value="">Selecione...</option>` + state.areas.map(a => `<option value="${a.id}">${a.nome}</option>`).join('');
                const lsG = document.getElementById('list-gestores');
                if(lsG) {
                    if(state.users.filter(u=>u.role==='gerente').length === 0) lsG.innerHTML = `<p class="text-xs text-slate-400 p-4 font-bold">Nenhum gestor.</p>`;
                    else lsG.innerHTML = state.users.filter(u=>u.role==='gerente').map(u => `
                        <div class="p-3 bg-white border border-slate-200 rounded-lg flex justify-between items-center shadow-sm">
                            <div><p class="font-bold text-slate-800 text-sm">${u.nome}</p><p class="text-[10px] text-slate-500 font-medium mt-0.5">${u.email}</p></div>
                            <div class="flex items-center gap-3"><span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase">${state.areas.find(x=>x.id===u.areaId)?.nome||'Sem área'}</span><button onclick="app.delGestor('${u.id}')" class="text-slate-400 hover:text-rose-500 px-1 transition-colors"><i class="fa-solid fa-trash text-xs"></i></button></div>
                        </div>`).join('');
                }
            },
            delGestor: async (id) => { if(await utils.dialog('confirm-danger', 'Excluir Gestor', 'Tem certeza?', '', 'Excluir')) { state.users = state.users.filter(u=>u.id!==id); app.saveDb(); app.renderAdminGestores(); } },

            // --- PLAN. GLOBAL IA (NÍVEL 1: DIRETORIA -> ÁREAS) ---
            renderPlanDir: () => {
                const elD = document.getElementById('plan-date'); if(!elD) return;
                const pd = elD.value; const mG = state.metasGlobal[pd] || { total: 0, areasMeta: {} };
                const objVal = document.getElementById('plan-obj-global'); if(objVal) objVal.value = utils.formatInput(mG.total);
                
                const grid = document.getElementById('plan-dir-grid');
                if(grid) {
                    grid.innerHTML = state.areas.map(a => {
                        const gestor = state.users.find(u => u.role === 'gerente' && u.areaId === a.id);
                        const valMeta = mG.areasMeta && mG.areasMeta[a.id] !== undefined ? mG.areasMeta[a.id] : 0; 
                        
                        return `
                        <tr class="hover:bg-slate-50 border-b border-slate-50">
                            <td class="p-3">
                                <div class="font-bold text-slate-800 text-sm"><i class="fa-solid fa-map-location-dot text-blue-500 mr-1.5"></i> ${a.nome}</div>
                                <div class="text-[9px] text-slate-400 ml-5 uppercase tracking-wider font-bold mt-1">Gestor: ${gestor?gestor.nome:'Nenhum'}</div>
                            </td>
                            <td class="p-2 text-right">
                                <div class="relative w-full">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-indigo-300 font-bold text-[10px]">R$</span>
                                    <input type="text" id="pd-meta-${a.id}" value="${utils.formatInput(valMeta)}" oninput="app.maskMoney(event); app.updatePlanDirTotal()" class="w-full border border-indigo-200 rounded-lg pl-7 pr-2 py-1.5 text-right text-xs font-black text-indigo-700 outline-none shadow-sm bg-indigo-50 focus:ring-2 focus:ring-indigo-500 transition-shadow">
                                </div>
                            </td>
                            <td class="p-2 text-center font-black text-slate-400 text-[10px]" id="pd-perc-${a.id}">0.0%</td>
                        </tr>`;
                    }).join('');
                    app.calcPlanDir(); 
                }
            },
            calcPlanDir: () => {
                app.updatePlanDirTotal();
            },
            updatePlanDirTotal: () => {
                let totalAreaMetas = 0;
                const objEl = document.getElementById('plan-obj-global');
                const globalVal = objEl ? utils.unmask(objEl.value) : 0;
                
                state.areas.forEach(a => { 
                    const el = document.getElementById(`pd-meta-${a.id}`); 
                    const pEl = document.getElementById(`pd-perc-${a.id}`);
                    if(el) {
                        const val = utils.unmask(el.value);
                        totalAreaMetas += val; 
                        if (pEl && document.activeElement !== pEl) {
                            pEl.innerText = globalVal > 0 ? ((val / globalVal) * 100).toFixed(1) + '%' : '0.0%';
                        }
                    }
                });
                
                const elTot = document.getElementById('plan-dir-total'); 
                if(elTot) elTot.innerText = utils.money(totalAreaMetas);
                
                const btn = document.getElementById('btn-save-plan-dir');
                if(btn) {
                    if(Math.abs(totalAreaMetas - globalVal) > 0.1 && globalVal > 0) { elTot.classList.replace('text-white', 'text-rose-400'); btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); }
                    else { elTot.classList.replace('text-rose-400', 'text-white'); btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-not-allowed'); }
                }
            },
            savePlanDir: () => {
                const pd = document.getElementById('plan-date').value; const total = utils.unmask(document.getElementById('plan-obj-global').value);
                const areasMeta = {}; state.areas.forEach(a => areasMeta[a.id] = utils.unmask(document.getElementById(`pd-meta-${a.id}`).value));
                state.metasGlobal[pd] = { total, areasMeta }; app.saveDb(); utils.toast("Cotas liberadas para os Gestores!");
            },

            // --- GESTOR: EQUIPE E VENDAS ---
            saveRep: (e) => {
                e.preventDefault(); const em = document.getElementById('r-email').value.trim().toLowerCase();
                if(state.users.some(u=>u.email===em)) return utils.toast("E-mail já usado.", "error");
                
                const repId = 'r_'+utils.id(); const nome = document.getElementById('r-nome').value;
                state.reps.push({ id: repId, areaId: state.currentUser.areaId, nome, telefone: document.getElementById('r-wpp').value, email: em, cep: document.getElementById('r-cep').value, endereco: document.getElementById('r-end').value, pastas: document.getElementById('r-pastas').value, clientes: document.getElementById('r-clientes').value });
                state.users.push({ id: 'u_'+utils.id(), role: 'representante', nome, email: em, pass: document.getElementById('r-pass').value, repId, areaId: state.currentUser.areaId, mustChangePassword: true });
                app.saveDb(); utils.toast("Acesso de Representante criado."); e.target.reset(); app.closeModal('modal-rep'); app.renderEquipe();
            },
            renderEquipe: () => {
                const reps = state.reps.filter(r => r.areaId === state.currentUser.areaId); const tb = document.getElementById('table-reps');
                if(!tb) return;
                if(!reps.length) return tb.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-slate-400 font-bold text-sm">Nenhum integrante cadastrado.</td></tr>`;
                tb.innerHTML = reps.map(r => `
                    <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                        <td class="p-4"><p class="font-bold text-slate-900 text-sm">${r.nome}</p><p class="text-[10px] text-slate-500 mt-1"><i class="fa-solid fa-map-pin text-slate-400"></i> ${r.endereco}</p></td>
                        <td class="p-4"><p class="font-semibold text-slate-700 flex items-center gap-1.5 text-xs"><i class="fa-brands fa-whatsapp text-emerald-500"></i> ${r.telefone}</p><p class="text-[10px] text-slate-500 mt-1"><i class="fa-solid fa-envelope text-blue-400"></i> ${r.email}</p></td>
                        <td class="p-4"><span class="bg-indigo-50 border border-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider"><i class="fa-solid fa-folder-open"></i> ${r.pastas}</span><p class="text-xs font-medium mt-2 text-slate-500 truncate w-48"><span class="font-bold text-slate-400">Foco:</span> ${r.clientes||'-'}</p></td>
                        <td class="p-4 text-right">
                            <button onclick="app.editRep('${r.id}')" class="text-slate-400 hover:text-blue-500 p-1.5 transition-colors"><i class="fa-solid fa-pen text-xs"></i></button>
                            <button onclick="app.delRep('${r.id}')" class="text-slate-400 hover:text-rose-500 p-1.5 transition-colors"><i class="fa-solid fa-trash text-xs"></i></button>
                        </td>
                    </tr>`).join('');
            },
            editRep: async (id) => {
                const r = state.reps.find(x=>x.id===id);
                const n = await utils.dialog('prompt', 'Editar Representante', 'Altere o nome:', r.nome);
                if(n && n.trim()!=='') { r.nome = n.trim(); const u = state.users.find(x=>x.repId===id); if(u) u.nome = n.trim(); app.saveDb(); app.renderEquipe(); utils.toast("Nome atualizado."); }
            },
            delRep: async (id) => { if(await utils.dialog('confirm-danger','Excluir','Excluir e revogar acesso?','','Excluir')){ state.reps = state.reps.filter(r=>r.id!==id); state.users = state.users.filter(u=>u.repId!==id); app.saveDb(); app.renderEquipe(); } },

            renderVendas: () => {
                const elD = document.getElementById('vendas-date'); if(!elD) return;
                const pd = elD.value; const aId = state.currentUser.areaId;
                const mG = state.metasGlobal[pd]; 
                const metaArea = mG && mG.areasMeta && mG.areasMeta[aId] ? mG.areasMeta[aId] : 0;
                
                const lockCont = document.getElementById('vendas-lock-container');
                const activeCont = document.getElementById('vendas-active-container');
                const tableCont = document.getElementById('vendas-table-container');

                if(metaArea <= 0) {
                    if(lockCont) lockCont.classList.remove('hidden');
                    if(activeCont) activeCont.classList.add('hidden');
                    if(tableCont) tableCont.classList.add('opacity-50', 'pointer-events-none');
                } else {
                    if(lockCont) lockCont.classList.add('hidden');
                    if(activeCont) activeCont.classList.remove('hidden');
                    if(tableCont) tableCont.classList.remove('opacity-50', 'pointer-events-none');
                    const vmaEl = document.getElementById('vendas-meta-area'); if(vmaEl) vmaEl.innerText = utils.money(metaArea);
                }

                const myReps = state.reps.filter(r => r.areaId === aId); const tb = document.getElementById('table-vendas'); let distTotal = 0;
                if(tb) {
                    const sortedReps = [...myReps].sort((a,b) => {
                        const m1 = state.metasRep[pd] && state.metasRep[pd][a.id] ? state.metasRep[pd][a.id] : 0;
                        const r1 = state.vendas.filter(v => v.repId === a.id && v.period === pd).reduce((acc,v)=>acc+v.valor,0);
                        const p1 = m1>0 ? r1/m1 : 0;
                        const m2 = state.metasRep[pd] && state.metasRep[pd][b.id] ? state.metasRep[pd][b.id] : 0;
                        const r2 = state.vendas.filter(v => v.repId === b.id && v.period === pd).reduce((acc,v)=>acc+v.valor,0);
                        const p2 = m2>0 ? r2/m2 : 0;
                        return p2 - p1;
                    });

                    tb.innerHTML = sortedReps.map(r => {
                        const valMeta = state.metasRep[pd] && state.metasRep[pd][r.id] !== undefined ? state.metasRep[pd][r.id] : 0;
                        distTotal += valMeta;
                        
                        const vReps = state.vendas.filter(v => v.repId === r.id && v.period === pd);
                        const s1 = vReps.filter(v=>v.semana==='S1').reduce((a,b)=>a+b.valor,0);
                        const s2 = vReps.filter(v=>v.semana==='S2').reduce((a,b)=>a+b.valor,0);
                        const s3 = vReps.filter(v=>v.semana==='S3').reduce((a,b)=>a+b.valor,0);
                        const s4 = vReps.filter(v=>v.semana==='S4').reduce((a,b)=>a+b.valor,0);
                        const faturado = s1+s2+s3+s4;
                        const volTotal = vReps.reduce((a,b)=>a+(b.volume||0),0);
                        
                        const perc = valMeta>0 ? (faturado/valMeta)*100 : 0;
                        const trophy = perc >= 100 ? `<i class="fa-solid fa-trophy text-amber-500 ml-2" title="Meta Batida!"></i>` : '';

                        return `
                        <tr class="hover:bg-slate-50 transition-colors border-b border-slate-100">
                            <td class="p-3 font-bold text-slate-800 text-xs">${r.nome} ${trophy}</td>
                            <td class="p-3">
                                <span class="font-black text-slate-500 text-xs bg-slate-100 px-2 py-1 rounded">${utils.money(valMeta)}</span>
                            </td>
                            <td class="p-2 text-[10px] font-semibold text-slate-500 border-l border-slate-100 bg-slate-50/50">${utils.money(s1)}</td>
                            <td class="p-2 text-[10px] font-semibold text-slate-500 bg-slate-50/50">${utils.money(s2)}</td>
                            <td class="p-2 text-[10px] font-semibold text-slate-500 bg-slate-50/50">${utils.money(s3)}</td>
                            <td class="p-2 text-[10px] font-semibold text-slate-500 border-r border-slate-100 bg-slate-50/50">${utils.money(s4)}</td>
                            <td class="p-3">
                                <p class="font-black text-emerald-600 text-sm">${utils.money(faturado)}</p>
                                <p class="text-[9px] font-bold uppercase tracking-wider mt-0.5 ${perc>=100?'text-emerald-500':'text-slate-400'}">${perc.toFixed(1)}% Atingido</p>
                            </td>
                            <td class="p-3 text-[10px] font-bold text-indigo-500">${utils.volume(volTotal)}</td>
                            <td class="p-3 text-right">
                                <button onclick="app.prepVenda('${r.id}', '${r.nome}', '${pd}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition flex items-center gap-1.5 ml-auto text-[10px] uppercase tracking-wider"><i class="fa-solid fa-plus text-xs"></i> Lançar</button>
                            </td>
                        </tr>`;
                    }).join('');
                }
                const vmdEl = document.getElementById('vendas-meta-dist');
                if(vmdEl) {
                    vmdEl.innerText = utils.money(distTotal);
                    if(Math.abs(distTotal - metaArea) > 0.1 && metaArea > 0) vmdEl.className = 'text-xl font-bold text-rose-500 mt-1';
                    else vmdEl.className = 'text-xl font-bold text-indigo-800 mt-1';
                }
            },
            
            // INTELIGÊNCIA: DISTRIBUIÇÃO GESTOR
            renderModalGestorIntel: () => {
                const pdEl = document.getElementById('vendas-date'); if(!pdEl) return;
                const pd = pdEl.value; const aId = state.currentUser.areaId;
                const mG = state.metasGlobal[pd]; const metaArea = mG && mG.areasMeta && mG.areasMeta[aId] ? mG.areasMeta[aId] : 0;
                
                if(metaArea <= 0) return utils.toast("A Diretoria precisa definir a meta da sua área primeiro.", "error");

                const maEl = document.getElementById('g-intel-meta-area'); if(maEl) maEl.value = utils.formatInput(metaArea);
                
                const [anoStr, mesStr] = pd.split('-'); let dataCalc = new Date(anoStr, mesStr-1, 1);
                dataCalc.setMonth(dataCalc.getMonth()-1); const m1Name = utils.mesesNomes[dataCalc.getMonth()] || ''; const p1 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;
                dataCalc.setMonth(dataCalc.getMonth()-1); const m2Name = utils.mesesNomes[dataCalc.getMonth()] || ''; const p2 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;
                dataCalc.setMonth(dataCalc.getMonth()-1); const m3Name = utils.mesesNomes[dataCalc.getMonth()] || ''; const p3 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;

                const th1 = document.getElementById('th-m1'); if(th1) th1.innerText = m1Name;
                const th2 = document.getElementById('th-m2'); if(th2) th2.innerText = m2Name;
                const th3 = document.getElementById('th-m3'); if(th3) th3.innerText = m3Name;

                const myReps = state.reps.filter(r => r.areaId === aId);
                const ls = document.getElementById('g-intel-grid');
                if(ls) {
                    if(!state.historicoManual) state.historicoManual = {};
                    
                    ls.innerHTML = myReps.map(r => {
                        let v1 = state.historicoManual[`${r.id}_${p1}`] !== undefined ? state.historicoManual[`${r.id}_${p1}`] : state.vendas.filter(v => v.repId === r.id && v.period === p1).reduce((acc,b)=>acc+b.valor,0);
                        let v2 = state.historicoManual[`${r.id}_${p2}`] !== undefined ? state.historicoManual[`${r.id}_${p2}`] : state.vendas.filter(v => v.repId === r.id && v.period === p2).reduce((acc,b)=>acc+b.valor,0);
                        let v3 = state.historicoManual[`${r.id}_${p3}`] !== undefined ? state.historicoManual[`${r.id}_${p3}`] : state.vendas.filter(v => v.repId === r.id && v.period === p3).reduce((acc,b)=>acc+b.valor,0);
                        const valMeta = state.metasRep[pd] && state.metasRep[pd][r.id] !== undefined ? state.metasRep[pd][r.id] : 0;
                        return `
                        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                            <td class="p-2 font-bold text-slate-800 text-[11px] truncate" title="${r.nome}">${r.nome}</td>
                            <td class="p-2 border-l border-slate-100">
                                <input type="text" id="gi-m3-${r.id}" value="${utils.formatInput(v3)}" oninput="app.maskMoney(event); app.calcGestorIntel()" class="w-full bg-amber-50 focus:bg-white border border-amber-200 rounded-lg px-2 py-1.5 text-right text-xs font-bold text-slate-700 outline-none transition-colors focus:border-blue-500">
                            </td>
                            <td class="p-2">
                                <input type="text" id="gi-m2-${r.id}" value="${utils.formatInput(v2)}" oninput="app.maskMoney(event); app.calcGestorIntel()" class="w-full bg-amber-50 focus:bg-white border border-amber-200 rounded-lg px-2 py-1.5 text-right text-xs font-bold text-slate-700 outline-none transition-colors focus:border-blue-500">
                            </td>
                            <td class="p-2">
                                <input type="text" id="gi-m1-${r.id}" value="${utils.formatInput(v1)}" oninput="app.maskMoney(event); app.calcGestorIntel()" class="w-full bg-amber-50 focus:bg-white border border-amber-200 rounded-lg px-2 py-1.5 text-right text-xs font-bold text-slate-700 outline-none transition-colors focus:border-blue-500">
                            </td>
                            <td class="p-2 border-l border-slate-100 text-right font-black text-slate-400 text-[10px]" id="gi-med-${r.id}">0,00</td>
                            <td class="p-2 text-right">
                                <input type="text" id="gi-meta-${r.id}" value="${utils.formatInput(valMeta)}" oninput="app.maskMoney(event); app.updateGestorIntelTotal()" class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-right text-xs font-black text-indigo-600 outline-none bg-indigo-50/30 focus:ring-2 focus:ring-indigo-500 transition-colors" title="Você pode editar a meta final livremente">
                            </td>
                        </tr>`;
                    }).join('');
                }
                app.openModal('modal-gestor-intel');
                app.calcGestorIntel(); 
            },
            calcGestorIntel: () => {
                const maEl = document.getElementById('g-intel-meta-area'); if(!maEl) return;
                const metaArea = utils.unmask(maEl.value);
                const myReps = state.reps.filter(r => r.areaId === state.currentUser.areaId);
                let totalHist = 0;
                
                myReps.forEach(r => { 
                    const v1 = utils.unmask(document.getElementById(`gi-m1-${r.id}`)?.value);
                    const v2 = utils.unmask(document.getElementById(`gi-m2-${r.id}`)?.value);
                    const v3 = utils.unmask(document.getElementById(`gi-m3-${r.id}`)?.value);
                    const media = (v1+v2+v3)/3;
                    const medEl = document.getElementById(`gi-med-${r.id}`); if(medEl) medEl.innerText = utils.formatInput(media);
                    totalHist += media; 
                });
                
                myReps.forEach(r => {
                    const mediaEl = document.getElementById(`gi-med-${r.id}`);
                    const media = mediaEl ? utils.unmask(mediaEl.innerText) : 0;
                    const metaEl = document.getElementById(`gi-meta-${r.id}`);
                    if(metaEl && document.activeElement !== metaEl) {
                        let perc = totalHist > 0 ? (media / totalHist) : (myReps.length ? 1 / myReps.length : 0);
                        const nVal = metaArea * perc;
                        metaEl.value = utils.formatInput(nVal);
                    }
                });
                app.updateGestorIntelTotal();
            },
            iaGestorPlan: () => {
                if(document.activeElement instanceof HTMLElement) document.activeElement.blur();
                app.calcGestorIntel();
                utils.toast('Sugestão calculada pelo histórico dos últimos três meses.');
            },
            updateGestorIntelTotal: () => {
                let total = 0;
                const maEl = document.getElementById('g-intel-meta-area');
                const metaArea = maEl ? utils.unmask(maEl.value) : 0;
                
                state.reps.filter(r => r.areaId === state.currentUser.areaId).forEach(r => { 
                    const el = document.getElementById(`gi-meta-${r.id}`); 
                    if(el) { total += utils.unmask(el.value); }
                });
                
                const elTot = document.getElementById('mi-total-soma'); if(elTot) elTot.innerText = utils.money(total);
                
                const btn = document.getElementById('btn-save-intel');
                if(btn) {
                    if(Math.abs(total - metaArea) > 0.1 && metaArea > 0) { elTot.classList.replace('text-white', 'text-rose-400'); btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); }
                    else { elTot.classList.replace('text-rose-400', 'text-white'); btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-not-allowed'); }
                }
            },
            salvarInteligencia: () => {
                const pdEl = document.getElementById('vendas-date'); if(!pdEl) return;
                const pd = pdEl.value; const aId = state.currentUser.areaId;
                const myReps = state.reps.filter(r => r.areaId === aId);
                
                if(!state.metasRep[pd]) state.metasRep[pd] = {};
                
                const [anoStr, mesStr] = pd.split('-'); let dataCalc = new Date(anoStr, mesStr-1, 1);
                dataCalc.setMonth(dataCalc.getMonth()-1); const p1 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;
                dataCalc.setMonth(dataCalc.getMonth()-1); const p2 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;
                dataCalc.setMonth(dataCalc.getMonth()-1); const p3 = `${dataCalc.getFullYear()}-${(dataCalc.getMonth()+1).toString().padStart(2,'0')}`;

                if(!state.historicoManual) state.historicoManual = {};

                myReps.forEach(r => { 
                    const mEl = document.getElementById(`gi-meta-${r.id}`);
                    if(mEl) state.metasRep[pd][r.id] = utils.unmask(mEl.value); 
                    
                    state.historicoManual[`${r.id}_${p1}`] = utils.unmask(document.getElementById(`gi-m1-${r.id}`)?.value);
                    state.historicoManual[`${r.id}_${p2}`] = utils.unmask(document.getElementById(`gi-m2-${r.id}`)?.value);
                    state.historicoManual[`${r.id}_${p3}`] = utils.unmask(document.getElementById(`gi-m3-${r.id}`)?.value);
                });

                app.saveDb(); utils.toast("Distribuição salva!"); app.closeModal('modal-gestor-intel'); app.renderVendas();
            },

            // --- LANÇAMENTOS (SEMANAL EXPRESSO) ---
            prepVendaGeral: () => {
                const pdEl = document.getElementById('vendas-date'); if(!pdEl) return;
                const pd = pdEl.value;
                const sel = document.getElementById('mv-rep-id');
                const myReps = state.reps.filter(r => r.areaId === state.currentUser.areaId);
                if(sel) sel.innerHTML = myReps.map(r => `<option value="${r.id}">${r.nome}</option>`).join('');
                const sku = document.getElementById('mv-sku'); if(sku) sku.innerHTML = state.skus.map(s => `<option value="${s.id}">${s.nome} - ${s.gramatura}</option>`).join('');
                const rcEl = document.getElementById('mv-rep-container'); if(rcEl) rcEl.classList.remove('hidden');
                
                const pdIn = document.getElementById('mv-pd'); if(pdIn) pdIn.value = pd;
                const cEl = document.getElementById('mv-cliente'); if(cEl) cEl.value = ''; 
                const vEl = document.getElementById('mv-valor'); if(vEl) vEl.value = ''; 
                const volEl = document.getElementById('mv-volume'); if(volEl) volEl.value = '';
                app.openModal('modal-venda');
            },
            prepVenda: (rId, rName, pd) => {
                const sel = document.getElementById('mv-rep-id');
                if(sel) sel.innerHTML = `<option value="${rId}">${rName}</option>`;
                const sku = document.getElementById('mv-sku'); if(sku) sku.innerHTML = state.skus.map(s => `<option value="${s.id}">${s.nome} - ${s.gramatura}</option>`).join('');
                const rcEl = document.getElementById('mv-rep-container'); if(rcEl) rcEl.classList.add('hidden');
                
                const pdIn = document.getElementById('mv-pd'); if(pdIn) pdIn.value = pd; 
                const cEl = document.getElementById('mv-cliente'); if(cEl) cEl.value = ''; 
                const vEl = document.getElementById('mv-valor'); if(vEl) vEl.value = ''; 
                const volEl = document.getElementById('mv-volume'); if(volEl) volEl.value = '';
                app.openModal('modal-venda');
            },
            saveVenda: (e) => {
                e.preventDefault(); 
                const rEl = document.getElementById('mv-rep-id'); if(!rEl) return;
                const pdEl = document.getElementById('mv-pd'); if(!pdEl) return;
                const semEl = document.getElementById('mv-semana'); if(!semEl) return;
                const cEl = document.getElementById('mv-cliente');
                const vEl = document.getElementById('mv-valor'); if(!vEl) return;
                const volEl = document.getElementById('mv-volume');
                const skuEl = document.getElementById('mv-sku'); if(!skuEl) return;

                const rId = rEl.value; 
                const pd = pdEl.value;
                const sem = semEl.value;
                let cli = cEl ? cEl.value.trim() : ""; 
                if(!cli) cli = "Venda Direta / Semanal"; 

                const val = utils.unmask(vEl.value);
                if(val<=0) return utils.toast("Insira um valor válido.", "error");
                const vol = volEl ? parseFloat(volEl.value) || 0 : 0;

                state.vendas.push({ id: 'v_'+utils.id(), period: pd, repId: rId, areaId: state.currentUser.areaId, skuId: skuEl.value, cliente: cli, valor: val, volume: vol, semana: sem, ts: Date.now() });
                app.saveDb(); utils.toast(`Faturamento lançado com sucesso!`); app.closeModal('modal-venda'); app.renderVendas();
            },

            // --- REPRESENTANTE VIEW ---
            renderRepDashboard: () => {
                const u = state.currentUser; 
                const pdEl = document.getElementById('rep-dash-date'); if(!pdEl) return;
                const pd = pdEl.value;
                const m = state.metasRep[pd] && state.metasRep[pd][u.repId] ? state.metasRep[pd][u.repId] : 0;
                const v = state.vendas.filter(x => x.repId === u.repId && x.period === pd);
                const r = v.reduce((a,b)=>a+b.valor,0); const p = m>0 ? (r/m)*100 : 0;
                const vol = v.reduce((a,b)=>a+(b.volume||0),0);

                const kmEl = document.getElementById('rep-kpi-meta'); if(kmEl) kmEl.innerText = utils.money(m);
                const krEl = document.getElementById('rep-kpi-real'); if(krEl) krEl.innerText = utils.money(r);
                const mvolEl = document.getElementById('rep-kpi-vol'); if(mvolEl) mvolEl.innerText = `${utils.volume(vol)} vend.`;
                const mpEl = document.getElementById('rep-kpi-perc'); if(mpEl) mpEl.innerText = p.toFixed(1)+'%';
                const mbEl = document.getElementById('rep-kpi-bar'); 
                if(mbEl) { mbEl.style.width = Math.min(p,100)+'%'; mbEl.className = `h-1.5 rounded-full transition-all duration-1000 ${p>=100?'bg-emerald-500':'bg-blue-600'}`; }

                const s1 = v.filter(x=>x.semana==='S1').reduce((a,b)=>a+b.valor,0);
                const s2 = v.filter(x=>x.semana==='S2').reduce((a,b)=>a+b.valor,0);
                const s3 = v.filter(x=>x.semana==='S3').reduce((a,b)=>a+b.valor,0);
                const s4 = v.filter(x=>x.semana==='S4').reduce((a,b)=>a+b.valor,0);
                const tSem = document.getElementById('rep-table-semanas');
                if(tSem) tSem.innerHTML = `<tr><td class="p-3 text-emerald-600">${utils.money(s1)}</td><td class="p-3 text-emerald-600">${utils.money(s2)}</td><td class="p-3 text-emerald-600">${utils.money(s3)}</td><td class="p-3 text-emerald-600">${utils.money(s4)}</td></tr>`;

                const mapCli = {}; v.forEach(x => mapCli[x.cliente] = (mapCli[x.cliente]||0)+x.valor);
                const topCli = Object.entries(mapCli).sort((a,b)=>b[1]-a[1]);
                const ls = document.getElementById('rep-chart-clientes');
                if(ls) {
                    if(!topCli.length) ls.innerHTML = `<p class="text-slate-400 text-xs font-bold italic py-8 text-center">Nenhuma venda registrada.</p>`;
                    else ls.innerHTML = topCli.map(c => `
                        <div class="mb-3">
                            <div class="flex justify-between text-xs font-bold text-slate-800 mb-1.5"><span class="truncate pr-4">${c[0]}</span> <span class="text-indigo-600">${utils.money(c[1])}</span></div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-indigo-500 rounded-full" style="width: ${(c[1]/topCli[0][1])*100}%"></div></div>
                        </div>`).join('');
                }
            },

            exportarBase: () => {
                const fReps = new Set(state.filters.reps);
                const fMeses = [...state.filters.meses].sort();
                const filtered = state.vendas.filter(v => fMeses.includes(v.period) && fReps.has(v.repId));
                if(!filtered.length) return utils.toast('Sem dados para os filtros selecionados.', 'error');

                const firstPeriod = fMeses[0] || 'inicio';
                const lastPeriod = fMeses[fMeses.length - 1] || 'fim';
                app.downloadSales(filtered, `Vendas_Riviera_${firstPeriod}_a_${lastPeriod}.csv`);
            },
            visibleReps: () => {
                const user = state.currentUser;
                if(user.role === 'representante') return state.reps.filter(rep => rep.id === user.repId);
                if(user.role === 'gerente') return state.reps.filter(rep => rep.areaId === user.areaId);
                return state.reps;
            },
            visibleSales: () => {
                const representativeIds = new Set(app.visibleReps().map(rep => rep.id));
                return state.vendas.filter(sale => representativeIds.has(sale.repId));
            },
            downloadSales: (sales, filename) => {
                const sortedSales = [...sales].sort((a, b) => `${a.period}-${a.repId}-${a.semana}`.localeCompare(`${b.period}-${b.repId}-${b.semana}`));
                const rows = sortedSales.map(sale => {
                    const area = state.areas.find(item => item.id === sale.areaId);
                    const representative = state.reps.find(item => item.id === sale.repId);
                    const sku = state.skus.find(item => item.id === sale.skuId);

                    return [
                        sale.period, area?.nome || '', representative?.nome || '', sale.semana || '',
                        sale.cliente || '', sku?.nome || '', sku?.categoria || '', sku?.gramatura || '',
                        Number(sale.valor || 0).toFixed(2).replace('.', ','),
                        Number(sale.volume || 0).toFixed(3).replace('.', ','),
                    ];
                });

                utils.downloadCsv(filename, [
                    'Período', 'Área', 'Representante', 'Semana', 'Cliente',
                    'Produto', 'Categoria', 'Gramatura', 'Valor da venda (R$)', 'Volume (kg)',
                ], rows);
            },
            exportarVendas: () => {
                const sales = app.visibleSales();
                if(!sales.length) return utils.toast('Nenhuma venda disponível para exportação.', 'error');
                app.downloadSales(sales, `Faturamento_Riviera_${state.currentUser.role}_${new Date().toISOString().slice(0, 10)}.csv`);
            },
            exportarReps: () => {
                const representatives = app.visibleReps();
                if(!representatives.length) return utils.toast('Nenhum representante disponível para exportação.', 'error');

                const rows = representatives.map(rep => [
                    state.areas.find(area => area.id === rep.areaId)?.nome || '', rep.nome, rep.email,
                    rep.telefone || '', rep.cep || '', rep.endereco || '', rep.pastas || '', rep.clientes || '',
                ]);
                utils.downloadCsv(`Equipe_Riviera_${state.currentUser.role}.csv`, [
                    'Área', 'Representante', 'E-mail', 'Telefone', 'CEP', 'Endereço', 'Portfólio', 'Foco de clientes',
                ], rows);
            },
            exportarSkus: () => {
                const isDirector = ['admin', 'diretor'].includes(state.currentUser.role);
                if(!isDirector) return utils.toast('Relatório disponível apenas para a Diretoria.', 'error');
                if(!state.skus.length) return utils.toast('Nenhum produto disponível para exportação.', 'error');

                const rows = state.skus.map(sku => [sku.id, sku.nome, sku.categoria, sku.gramatura]);
                utils.downloadCsv('Produtos_Riviera.csv', ['Código', 'Produto', 'Categoria', 'Gramatura'], rows);
            },
            exportarAreas: () => {
                const isDirector = ['admin', 'diretor'].includes(state.currentUser.role);
                if(!isDirector) return utils.toast('Relatório disponível apenas para a Diretoria.', 'error');
                if(!state.areas.length) return utils.toast('Nenhuma área disponível para exportação.', 'error');

                const rows = state.areas.map(area => {
                    const managers = state.users.filter(user => user.role === 'gerente' && user.areaId === area.id);
                    return [
                        area.nome,
                        managers.map(manager => manager.nome).join(' | '),
                        managers.map(manager => manager.email).join(' | '),
                        managers.map(manager => manager.telefone || '').join(' | '),
                        state.reps.filter(rep => rep.areaId === area.id).length,
                    ];
                });
                utils.downloadCsv('Estrutura_Comercial_Riviera.csv', [
                    'Área', 'Gestor', 'E-mail do gestor', 'Telefone do gestor', 'Representantes',
                ], rows);
            }
        };

        window.app = app;
        document.addEventListener('DOMContentLoaded', app.init);
    </script>
</body>
</html>
