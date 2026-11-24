<?php
require_once __DIR__ . '/../src/sessao.php';
$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
$dadosFormulario = $_SESSION['cadastro_empresa_dados'] ?? [];
unset($_SESSION['cadastro_empresa_dados']);
$etapaInicial = max(1, min(3, (int) ($_GET['etapa'] ?? 1)));
$valorCampo = static fn(string $campo): string => htmlspecialchars((string) ($dadosFormulario[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Empresarial - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/cadastro-empresa.css">  
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

    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 36px;">
            <a class="btn-back" href="/index.php">← Voltar</a>
            <span class="eyebrow">Cadastro empresarial</span>
            <h1 class="page-title">Registre sua empresa</h1>
            <p style="font-size: 14px; color: var(--muted-foreground); margin: 0; line-height: 1.5;">
                Publique vagas e encontre os melhores talentos do Brasil.
            </p>
        </div>

        <!-- Indicadores de Progresso -->
        <div class="step-container">
            <div class="step-item <?= $etapaInicial === 1 ? 'step-active' : 'step-done' ?>" id="indicator-1">
                <div class="step-circle"><?= $etapaInicial > 1 ? '✓' : '1' ?></div>
                <div class="step-label">Dados da empresa</div>
            </div>
            <div class="step-line <?= $etapaInicial > 1 ? 'step-done-line' : '' ?>" id="line-1"></div>
            
            <div class="step-item <?= $etapaInicial === 2 ? 'step-active' : ($etapaInicial > 2 ? 'step-done' : '') ?>" id="indicator-2">
                <div class="step-circle"><?= $etapaInicial > 2 ? '✓' : '2' ?></div>
                <div class="step-label">Contato</div>
            </div>
            <div class="step-line <?= $etapaInicial > 2 ? 'step-done-line' : '' ?>" id="line-2"></div>
            
            <div class="step-item <?= $etapaInicial === 3 ? 'step-active' : '' ?>" id="indicator-3">
                <div class="step-circle">3</div>
                <div class="step-label">Acesso e senha</div>
            </div>
        </div>

        <!-- Formulário Geral -->
        <div class="form-card">
            <!-- Aponte a action para a rota da API no seu backend -->
            <form data-current-step="<?= $etapaInicial ?>" action="/src/processar-empresa.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($erro !== ''): ?>
                    <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                
                <!-- PASSO 1: DADOS DA EMPRESA -->
                <div class="step-content <?= $etapaInicial === 1 ? 'active' : '' ?>" id="step-1">
                    <div class="form-group">
                        <div>
                            <label class="form-label" for="cnpj-empresa">CNPJ<span class="required">*</span></label>
                            <input id="cnpj-empresa" type="text" name="cnpj" class="form-input" placeholder="00.000.000/0000-00" value="<?= $valorCampo('cnpj') ?>" inputmode="numeric" pattern="[0-9]{2}\.[0-9]{3}\.[0-9]{3}/[0-9]{4}-[0-9]{2}" maxlength="18" required>
                        </div>
                        <div>
                            <label class="form-label">Razão social / Nome da empresa<span class="required">*</span></label>
                            <input type="text" name="nome_empresa" class="form-input" placeholder="Ex: Acme Tecnologia LTDA" value="<?= $valorCampo('nome_empresa') ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Setor de atuação<span class="required">*</span></label>
                            <select name="setor" class="form-input" required>
                                <option value="">Selecione...</option>
                                <option value="Tecnologia" <?= ($dadosFormulario['setor'] ?? '') === 'Tecnologia' ? 'selected' : '' ?>>Tecnologia</option>
                                <option value="Finanças" <?= ($dadosFormulario['setor'] ?? '') === 'Finanças' ? 'selected' : '' ?>>Finanças</option>
                                <option value="Saúde" <?= ($dadosFormulario['setor'] ?? '') === 'Saúde' ? 'selected' : '' ?>>Saúde</option>
                                <option value="Educação" <?= ($dadosFormulario['setor'] ?? '') === 'Educação' ? 'selected' : '' ?>>Educação</option>
                                <option value="Varejo" <?= ($dadosFormulario['setor'] ?? '') === 'Varejo' ? 'selected' : '' ?>>Varejo</option>
                                <option value="Logística" <?= ($dadosFormulario['setor'] ?? '') === 'Logística' ? 'selected' : '' ?>>Logística</option>
                                <option value="Outro" <?= ($dadosFormulario['setor'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Porte da empresa<span class="required">*</span></label>
                            <select name="porte" class="form-input" required>
                                <option value="">Selecione...</option>
                                <option value="Microempresa" <?= ($dadosFormulario['porte'] ?? '') === 'Microempresa' ? 'selected' : '' ?>>Microempresa (até 9 funcionários)</option>
                                <option value="Pequena" <?= ($dadosFormulario['porte'] ?? '') === 'Pequena' ? 'selected' : '' ?>>Pequena (10–49)</option>
                                <option value="Média" <?= ($dadosFormulario['porte'] ?? '') === 'Média' ? 'selected' : '' ?>>Média (50–249)</option>
                                <option value="Grande" <?= ($dadosFormulario['porte'] ?? '') === 'Grande' ? 'selected' : '' ?>>Grande (250+)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- PASSO 2: CONTATO -->
                <div class="step-content <?= $etapaInicial === 2 ? 'active' : '' ?>" id="step-2">
                    <div class="form-group">
                        <div>
                            <label class="form-label">E-mail corporativo<span class="required">*</span></label>
                            <input type="email" name="email_corporativo" class="form-input" placeholder="rh@empresa.com.br" value="<?= $valorCampo('email_corporativo') ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone_empresa" class="form-input" placeholder="(11) 99999-9999" value="<?= $valorCampo('telefone_empresa') ?>">
                        </div>
                        <div>
                            <label class="form-label">Site da empresa</label>
                            <input type="url" name="site" class="form-input" placeholder="https://empresa.com.br" value="<?= $valorCampo('site') ?>">
                        </div>
                        <div>
                            <label class="form-label">Cidade sede<span class="required">*</span></label>
                            <input type="text" name="cidade_sede" class="form-input" placeholder="São Paulo, SP" value="<?= $valorCampo('cidade_sede') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- PASSO 3: ACESSO -->
                <div class="step-content <?= $etapaInicial === 3 ? 'active' : '' ?>" id="step-3">
                    <div class="form-group">
                        <div style="padding: 14px; background-color: var(--secondary); border: 1px solid var(--border); font-size: 13px; color: var(--foreground); line-height: 1.5; border-radius: 2px;">
                            Uma senha de acesso será usada para entrar no painel da empresa e gerenciar vagas e candidatos.
                        </div>
                        
                        <div>
                            <label class="form-label" for="senha-empresa">Senha<span class="required">*</span></label>
                            <div class="password-field">
                                <input id="senha-empresa" type="password" name="senha" class="form-input" placeholder="Mínimo 8 caracteres" minlength="8" required>
                                <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-controls="senha-empresa" aria-pressed="false">Mostrar</button>
                            </div>
                        </div>
                        <div>
                            <label class="form-label" for="confirmar-senha-empresa">Confirmar senha<span class="required">*</span></label>
                            <div class="password-field">
                                <input id="confirmar-senha-empresa" type="password" name="confirmar_senha" class="form-input" placeholder="Repita a senha" minlength="8" required>
                                <button type="button" class="password-toggle" aria-label="Mostrar senha" aria-controls="confirmar-senha-empresa" aria-pressed="false">Mostrar</button>
                            </div>
                        </div>
                        
                        <div style="font-size: 12px; color: var(--muted-foreground); line-height: 1.5;">
                            Ao criar a conta você concorda com os 
                            <span style="color: var(--primary); text-decoration: underline; cursor: pointer;">Termos de Uso</span> 
                            e a 
                            <span style="color: var(--primary); text-decoration: underline; cursor: pointer;">Política de Privacidade</span>.
                        </div>
                    </div>
                </div>

                <!-- Botoes de Controle -->
                <div class="form-actions">
                    <button type="button" id="btnPrev" class="btn-secondary" style="display: <?= $etapaInicial === 1 ? 'none' : 'block' ?>;" onclick="changeStep(-1)">← Voltar</button>
                    <button type="button" id="btnNext" class="btn-primary" style="display: <?= $etapaInicial === 3 ? 'none' : 'block' ?>;" onclick="changeStep(1)">Continuar →</button>
                    <button type="submit" id="btnSubmit" class="btn-primary" style="display: <?= $etapaInicial === 3 ? 'block' : 'none' ?>;">Criar conta da empresa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de transição de abas (Idêntico ao do Candidato) -->
    <script src="/JS/empresa-avancar-passos.js"></script>
    <script src="/JS/alternar-senha.js"></script>
    <script src="/JS/tema.js"></script>

</body>
</html>