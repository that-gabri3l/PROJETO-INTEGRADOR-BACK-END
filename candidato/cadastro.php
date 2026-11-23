<?php
require_once __DIR__ . '/../src/sessao.php';
$erro = isset($_GET['erro']) ? trim((string) $_GET['erro']) : '';
$dadosFormulario = $_SESSION['cadastro_candidato_dados'] ?? [];
unset($_SESSION['cadastro_candidato_dados']);
$etapaInicial = max(1, min(3, (int) ($_GET['etapa'] ?? 1)));
$valorCampo = static fn(string $campo): string => htmlspecialchars((string) ($dadosFormulario[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
$regimesSelecionadosIniciais = array_filter(explode(',', (string) ($dadosFormulario['regime_preferido'] ?? '')));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Candidato - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/cadastro-candidato.css">

</head>

<body>

    <nav class="navbar">
        <a href="/index.php" class="navbar-logo"><!-- Imagem para o Modo Claro -->
            <img src="/images/logo-claro-infodin.png" alt="Logo do site" class="logo-light"
                style="height:40px; width: auto; object-fit: contain;">

            <!-- Imagem para o Modo Escuro -->
            <img src="/images/logo-infodin.png" alt="Logo do site" class="logo-dark"
                style="height: 40px; width: auto; object-fit: contain;"></a>
        <button id="theme-toggle" class="theme-toggle" title="Mudar tema">🌗</button>
    </nav>

    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 36px;">
            <a class="btn-back" href="/cadastro/escolha.php">← Voltar</a>
            <div class="eyebrow">Cadastro de candidato</div>
            <h1 class="page-title">Crie seu perfil</h1>
            <p style="font-size: 14px; color: var(--muted-foreground); margin: 0;">
                Gratuito. Encontre vagas e candidate-se em segundos.
            </p>
        </div>

        <!-- Indicador de Passos -->
        <div class="step-container">
            <div class="step-item <?= $etapaInicial === 1 ? 'step-active' : 'step-done' ?>" id="indicator-1">
                <div class="step-circle"><?= $etapaInicial > 1 ? '✓' : '1' ?></div>
                <div class="step-label">Dados pessoais</div>
            </div>
            <div class="step-line <?= $etapaInicial > 1 ? 'step-done-line' : '' ?>" id="line-1"></div>

            <div class="step-item <?= $etapaInicial === 2 ? 'step-active' : ($etapaInicial > 2 ? 'step-done' : '') ?>" id="indicator-2">
                <div class="step-circle"><?= $etapaInicial > 2 ? '✓' : '2' ?></div>
                <div class="step-label">Perfil profissional</div>
            </div>
            <div class="step-line <?= $etapaInicial > 2 ? 'step-done-line' : '' ?>" id="line-2"></div>

            <div class="step-item <?= $etapaInicial === 3 ? 'step-active' : '' ?>" id="indicator-3">
                <div class="step-circle">3</div>
                <div class="step-label">Acesso e senha</div>
            </div>
        </div>

        <!-- Formulário (Tudo em um único form para enviar via POST) -->
        <div class="form-card">
            <!-- Aponte o action para a sua rota do back-end e adicione o enctype -->
            <form id="registerForm" data-current-step="<?= $etapaInicial ?>" action="/src/processar-candidato.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($erro !== ''): ?>
                    <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <!-- PASSO 1 -->
                <div class="step-content <?= $etapaInicial === 1 ? 'active' : '' ?>" id="step-1">
                    <div class="form-group">

                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Nome completo<span class="required">*</span></label>
                            <input type="text" name="nome" class="form-input" placeholder="Ex: Maria Silva" value="<?= $valorCampo('nome') ?>" required>
                        </div>

                        <div class="grid-2" style="margin-bottom: 16px;">
                            <div>
                                <label class="form-label">E-mail<span class="required">*</span></label>
                                <input type="email" name="email" class="form-input" placeholder="maria@email.com" value="<?= $valorCampo('email') ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-input" placeholder="(11) 99999-9999" value="<?= $valorCampo('telefone') ?>">
                            </div>
                        </div>

                        <div class="grid-2" style="margin-bottom: 16px;">
                            <div>
                                <label class="form-label">Cidade<span class="required">*</span></label>
                                <input type="text" name="cidade" class="form-input" placeholder="São Paulo, SP" value="<?= $valorCampo('cidade') ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Data de nascimento</label>
                                <input type="date" name="data_nascimento" class="form-input" value="<?= $valorCampo('data_nascimento') ?>">
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="foto_perfil">Foto de perfil (JPG/PNG, até 2 MB)</label>
                            <input id="foto_perfil" type="file" name="foto_perfil" class="form-input" accept="image/png,image/jpeg">
                            <div id="foto-feedback" class="upload-feedback" role="status">Nenhuma foto selecionada.</div>
                            <img id="foto-preview" class="upload-preview" alt="Prévia da foto selecionada" hidden>
                        </div>

                    </div>
                </div>

            <!-- PASSO 2 -->
            <div class="step-content <?= $etapaInicial === 2 ? 'active' : '' ?>" id="step-2">
                <div class="form-group">
                    <div class="grid-2">
                        <div>
                            <label class="form-label">Cargo atual / desejado<span class="required">*</span></label>
                            <input type="text" name="cargo" class="form-input" placeholder="Ex: Desenvolvedora Frontend" value="<?= $valorCampo('cargo') ?>" required>
                        </div>
                        <div>
                            <label class="form-label">Área de atuação<span class="required">*</span></label>
                            <select name="area" class="form-input" required>
                                <option value="">Selecione...</option>
                                <option value="Tecnologia" <?= ($dadosFormulario['area'] ?? '') === 'Tecnologia' ? 'selected' : '' ?>>Tecnologia</option>
                                <option value="Design" <?= ($dadosFormulario['area'] ?? '') === 'Design' ? 'selected' : '' ?>>Design</option>
                                <option value="Marketing" <?= ($dadosFormulario['area'] ?? '') === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div>
                                <label class="form-label">Nível de experiência<span class="required">*</span></label>
                                <select name="experiencia" class="form-input" required>
                                    <option value="">Selecione...</option>
                                    <option value="Estagiário" <?= ($dadosFormulario['experiencia'] ?? '') === 'Estagiário' ? 'selected' : '' ?>>Estagiário</option>
                                    <option value="Júnior" <?= ($dadosFormulario['experiencia'] ?? '') === 'Júnior' ? 'selected' : '' ?>>Júnior (0–2 anos)</option>
                                    <option value="Pleno" <?= ($dadosFormulario['experiencia'] ?? '') === 'Pleno' ? 'selected' : '' ?>>Pleno (2–5 anos)</option>
                                    <option value="Sênior" <?= ($dadosFormulario['experiencia'] ?? '') === 'Sênior' ? 'selected' : '' ?>>Sênior (5+ anos)</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Escolaridade</label>
                                <select name="escolaridade" class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="Ensino médio" <?= ($dadosFormulario['escolaridade'] ?? '') === 'Ensino médio' ? 'selected' : '' ?>>Ensino médio</option>
                                    <option value="Graduação" <?= ($dadosFormulario['escolaridade'] ?? '') === 'Graduação' ? 'selected' : '' ?>>Graduação completa</option>
                                    <option value="Pós-graduação" <?= ($dadosFormulario['escolaridade'] ?? '') === 'Pós-graduação' ? 'selected' : '' ?>>Pós-graduação / MBA</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Regime de trabalho preferido</label>
                            <div style="display: flex; gap: 8px;" id="regime-buttons">
                                <button type="button" class="btn-checkbox <?= in_array('Presencial', $regimesSelecionadosIniciais, true) ? 'active' : '' ?>" onclick="toggleRegime(this, 'Presencial')">Presencial</button>
                                <button type="button" class="btn-checkbox <?= in_array('Híbrido', $regimesSelecionadosIniciais, true) ? 'active' : '' ?>" onclick="toggleRegime(this, 'Híbrido')">Híbrido</button>
                                <button type="button" class="btn-checkbox <?= in_array('Remoto', $regimesSelecionadosIniciais, true) ? 'active' : '' ?>" onclick="toggleRegime(this, 'Remoto')">Remoto</button>
                            </div>
                            <!-- Campo oculto que enviará os dados selecionados pro Back-End -->
                            <input type="hidden" name="regime_preferido" id="regime_input" value="<?= $valorCampo('regime_preferido') ?>">
                        </div>

                        <div>
                            <label class="form-label">LinkedIn</label>
                            <input type="url" name="linkedin" class="form-input" placeholder="https://linkedin.com/in/maria-silva" value="<?= $valorCampo('linkedin') ?>">
                        </div>

                        <div>
                            <label class="form-label">Sobre você</label>
                            <textarea name="sobre" class="form-input" rows="4" placeholder="Escreva um breve resumo profissional..."><?= $valorCampo('sobre') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- PASSO 3 -->
                <div class="step-content <?= $etapaInicial === 3 ? 'active' : '' ?>" id="step-3">
                    <div class="form-group">
                        <div style="padding: 14px 16px; background-color: var(--secondary); border: 1px solid var(--border); display: flex; gap: 12px; border-radius: 2px;">
                            <span style="font-size: 18px;">🔒</span>
                            <p style="margin: 0; font-size: 13px; line-height: 1.5;">Crie uma senha segura para acessar seu perfil.</p>
                        </div>

                        <div>
                            <label class="form-label">Senha<span class="required">*</span></label>
                            <input type="password" name="senha" class="form-input" placeholder="Mínimo 8 caracteres" minlength="8" required>
                        </div>
                        <div>
                            <label class="form-label">Confirmar senha<span class="required">*</span></label>
                            <input type="password" name="confirmar_senha" class="form-input" placeholder="Repita a senha" minlength="8" required>
                        </div>

                        <!-- Checkbox customizado -->
                        <label style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid var(--border); cursor: pointer;">
                            <input type="checkbox" name="notificacoes" value="sim" <?= !isset($dadosFormulario['notificacoes']) || $dadosFormulario['notificacoes'] === 'sim' ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                            <div>
                                <div style="font-size: 13px; font-weight: 500;">Receber alertas de vagas por e-mail</div>
                                <div style="font-size: 12px; color: var(--muted-foreground);">Notificações quando surgirem vagas compatíveis com seu perfil</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Botoes de Navegação do Formulário -->
                <div class="form-actions">
                    <button type="button" id="btnPrev" class="btn-secondary" style="display: <?= $etapaInicial === 1 ? 'none' : 'block' ?>;" onclick="changeStep(-1)">← Voltar</button>
                    <button type="button" id="btnNext" class="btn-primary" style="display: <?= $etapaInicial === 3 ? 'none' : 'block' ?>;" onclick="changeStep(1)">Continuar →</button>
                    <button type="submit" id="btnSubmit" class="btn-primary" style="display: <?= $etapaInicial === 3 ? 'block' : 'none' ?>;">Criar meu perfil</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Simples para Alternar os Passos -->
    <script src="/JS/candidato-avancar-passos.js"></script>

    <script src="/JS/tema.js"></script>
</body>

</html>