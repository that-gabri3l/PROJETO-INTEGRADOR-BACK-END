<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('empresa');

$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
$areas = [
    'Agronegócio',
    'Alimentação e Bebidas',
    'Arquitetura e Construção',
    'Automotivo',
    'Comunicação e Mídia',
    'Consultoria',
    'Educação',
    'Energia',
    'Engenharia',
    'Finanças',
    'Indústria e Manufatura',
    'Imobiliário',
    'Jurídico',
    'Logística',
    'Meio Ambiente',
    'Recursos Humanos',
    'Saúde',
    'Tecnologia',
    'Telecomunicações',
    'Transportes',
    'Turismo e Hotelaria',
    'Varejo',
    'Outro',
];
$regimes = ['Presencial', 'Híbrido', 'Remoto'];
$dados = [
    'titulo' => trim((string) ($_GET['titulo'] ?? '')),
    'area' => trim((string) ($_GET['area'] ?? '')),
    'regime' => trim((string) ($_GET['regime'] ?? '')),
    'localizacao' => trim((string) ($_GET['localizacao'] ?? '')),
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicar Vaga - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/CSS/publicar-vaga.css">     
<link rel="stylesheet" href="/CSS/mobile.css">
    
</head>
<body>

    <nav class="navbar">
        <a href="/index.php" class="navbar-logo"><!-- Imagem para o Modo Claro -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;"></a>
        <div class="navbar-actions">
            <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
            <a href="/empresa/painel.php" class="nav-btn">Meu painel</a>
        </div>
    </nav>

    <main class="container">
        <!-- Header -->
        <div class="page-header">
            <a href="/empresa/painel.php" class="btn-back">← Voltar ao painel</a>
            <span class="eyebrow">Nova publicação</span>
            <h1 class="page-title">Publicar vaga</h1>
        </div>

        <!-- Formulário apontando para a sua rota -->
        <form action="/src/processar-vaga.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($erro !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
            <!-- Section 01 -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h2 class="section-title">Informações da vaga</h2>
                </div>
                <div class="section-body">
                    <div class="grid-2-cols">
                        <div class="col-full">
                            <label class="form-label" for="titulo">Título da vaga<span class="required-mark">*</span></label>
                            <input id="titulo" type="text" name="titulo" class="form-input" maxlength="150" value="<?= htmlspecialchars($dados['titulo'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Engenheiro de Software Sênior" required>
                        </div>
                        <div>
                            <label class="form-label" for="area">Área<span class="required-mark">*</span></label>
                            <select id="area" name="area" class="form-input" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?>" <?= $dados['area'] === $area ? 'selected' : '' ?>><?= htmlspecialchars($area, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="regime">Regime de trabalho<span class="required-mark">*</span></label>
                            <select id="regime" name="regime" class="form-input" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($regimes as $regime): ?>
                                    <option value="<?= htmlspecialchars($regime, ENT_QUOTES, 'UTF-8') ?>" <?= $dados['regime'] === $regime ? 'selected' : '' ?>><?= htmlspecialchars($regime, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-full">
                            <label class="form-label" for="localizacao">Localização<span class="required-mark">*</span></label>
                            <input id="localizacao" type="text" name="localizacao" class="form-input" maxlength="150" value="<?= htmlspecialchars($dados['localizacao'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: São Paulo, SP" required>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 02 -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-number">02</span>
                    <h2 class="section-title">Remuneração e publicação</h2>
                </div>
                <div class="section-body">
                    <div class="grid-3-cols">
                        <div>
                            <label class="form-label" for="salario_min">Salário mínimo (R$)</label>
                            <input id="salario_min" type="number" name="salario_min" class="form-input" placeholder="Ex: 8000" min="1" max="9999999999.99" step="0.01">
                        </div>
                        <div>
                            <label class="form-label" for="salario_max">Salário máximo (R$)</label>
                            <input id="salario_max" type="number" name="salario_max" class="form-input" placeholder="Ex: 12000" min="1" max="9999999999.99" step="0.01">
                        </div>
                        <div>
                            <label class="form-label" for="exibir_salario">Exibir salário?</label>
                            <select id="exibir_salario" name="exibir_salario" class="form-input">
                                <option value="sim">Sim, exibir</option>
                                <option value="nao">Não exibir</option>
                                <option value="combinar">A combinar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 03 -->
            <section class="form-section">
                <div class="section-header">
                    <span class="section-number">03</span>
                    <h2 class="section-title">Descrição e requisitos</h2>
                </div>
                <div class="section-body flex-col">
                        <div>
                            <label class="form-label" for="descricao">Descrição da vaga<span class="required-mark">*</span></label>
                            <textarea id="descricao" name="descricao" class="form-input" rows="5" placeholder="Descreva a vaga, responsabilidades do cargo e o que o candidato irá fazer no dia a dia..." required></textarea>
                        </div>
                        <div>
                            <label class="form-label" for="requisitos">Requisitos<span class="required-mark">*</span></label>
                            <textarea id="requisitos" name="requisitos" class="form-input" rows="4" placeholder="Liste os requisitos, um por linha. Ex:&#10;5+ anos de experiência com Java&#10;Inglês avançado&#10;Experiência com AWS" required></textarea>
                        </div>
                        <div>
                            <label class="form-label" for="beneficios">Benefícios</label>
                            <textarea id="beneficios" name="beneficios" class="form-input" rows="3" placeholder="Liste os benefícios, um por linha. Ex:&#10;Plano de saúde Bradesco&#10;Vale alimentação R$ 1.000&#10;Gympass"></textarea>
                        </div>
                </div>
            </section>

            <!-- Ações -->
            <div class="form-actions">
                <a href="/empresa/painel.php" class="btn-secondary">Cancelar</a>
                <button type="submit" name="acao" value="rascunho" class="btn-secondary">Salvar como rascunho</button>
                <button type="submit" name="acao" value="publicar" class="btn-primary">Publicar vaga</button>
            </div>

        </form>
    </main>
        
    <script src="/JS/tema.js"></script>

</body>
</html>