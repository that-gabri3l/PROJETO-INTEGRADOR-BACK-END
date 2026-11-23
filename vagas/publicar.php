<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('empresa');

$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
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
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
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
            <div class="form-section">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h3 class="section-title">Informações básicas</h3>
                </div>
                <div class="section-body">
                    <div class="grid-2-cols">
                        <div class="col-full">
                            <label class="form-label">Título da vaga<span class="required-mark">*</span></label>
                            <input type="text" name="titulo" class="form-input" placeholder="Ex: Engenheiro de Software Sênior" required>
                        </div>
                        <div>
                            <label class="form-label">Área<span class="required-mark">*</span></label>
                            <select name="area" class="form-input" required>
                                <option value="">Selecione...</option>
                                <option value="Tecnologia">Tecnologia</option>
                                <option value="Design">Design</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Dados">Dados</option>
                                <option value="Gestão">Gestão</option>
                                <option value="Vendas">Vendas</option>
                                <option value="Jurídico">Jurídico</option>
                                <option value="Financeiro">Financeiro</option>
                                <option value="RH">RH</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Regime de trabalho<span class="required-mark">*</span></label>
                            <select name="regime" class="form-input" required>
                                <option value="">Selecione...</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Híbrido">Híbrido</option>
                                <option value="Remoto">Remoto</option>
                            </select>
                        </div>
                        <div class="col-full">
                            <label class="form-label">Localização<span class="required-mark">*</span></label>
                            <input type="text" name="localizacao" class="form-input" placeholder="Ex: São Paulo, SP" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 02 -->
            <div class="form-section">
                <div class="section-header">
                    <span class="section-number">02</span>
                    <h3 class="section-title">Remuneração</h3>
                </div>
                <div class="section-body">
                    <div class="grid-3-cols">
                        <div>
                            <label class="form-label">Salário mínimo (R$)</label>
                            <input type="number" name="salario_min" class="form-input" placeholder="Ex: 8000">
                        </div>
                        <div>
                            <label class="form-label">Salário máximo (R$)</label>
                            <input type="number" name="salario_max" class="form-input" placeholder="Ex: 12000">
                        </div>
                        <div>
                            <label class="form-label">Exibir salário?</label>
                            <select name="exibir_salario" class="form-input">
                                <option value="sim">Sim, exibir no anúncio</option>
                                <option value="nao">Não exibir</option>
                                <option value="combinar">A combinar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 03 -->
            <div class="form-section">
                <div class="section-header">
                    <span class="section-number">03</span>
                    <h3 class="section-title">Descrição e requisitos</h3>
                </div>
                <div class="section-body">
                    <div class="flex-col">
                        <div>
                            <label class="form-label">Descrição da vaga<span class="required-mark">*</span></label>
                            <textarea name="descricao" class="form-input" rows="5" placeholder="Descreva a vaga, responsabilidades do cargo e o que o candidato irá fazer no dia a dia..." required></textarea>
                        </div>
                        <div>
                            <label class="form-label">Requisitos<span class="required-mark">*</span></label>
                            <textarea name="requisitos" class="form-input" rows="4" placeholder="Liste os requisitos, um por linha. Ex:&#10;5+ anos de experiência com Java&#10;Inglês avançado&#10;Experiência com AWS" required></textarea>
                        </div>
                        <div>
                            <label class="form-label">Benefícios</label>
                            <textarea name="beneficios" class="form-input" rows="3" placeholder="Liste os benefícios, um por linha. Ex:&#10;Plano de saúde Bradesco&#10;Vale alimentação R$ 1.000&#10;Gympass"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="form-actions">
                <button type="submit" name="acao" value="rascunho" class="btn-secondary">Salvar como rascunho</button>
                <button type="submit" name="acao" value="publicar" class="btn-primary">Publicar vaga</button>
            </div>

        </form>
    </main>
        
    <script src="/JS/tema.js"></script>

</body>
</html>