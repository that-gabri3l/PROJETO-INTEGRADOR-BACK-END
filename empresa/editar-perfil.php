<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('empresa');
require_once __DIR__ . '/../src/conexao.php';

$empresaId = $_SESSION['perfil_id'] ?? null;
if (!$empresaId) {
    header('Location: /empresa/painel.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM empresas WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $empresaId]);
$empresa = $stmt->fetch();
if (!$empresa) {
    header('Location: /empresa/painel.php');
    exit;
}

$erro = trim((string) ($_GET['erro'] ?? ''));
$salvo = ($_GET['salvo'] ?? '') === '1';
$senhaAlterada = ($_GET['senha'] ?? '') === 'alterada';
$setores = ['Tecnologia', 'Finanças', 'Saúde', 'Educação', 'Varejo', 'Logística', 'Outro'];
$portes = ['Microempresa', 'Pequena', 'Média', 'Grande'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar empresa - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/cadastro-empresa.css">
    <link rel="stylesheet" href="/CSS/mobile.css">
</head>
<body>
    <nav class="navbar">
        <a href="/empresa/painel.php" class="navbar-logo">
            <img src="/images/logo-claro-infodin.png" alt="Infodin" class="logo-light" style="height:40px;width:auto;object-fit:contain;">
            <img src="/images/logo-infodin.png" alt="Infodin" class="logo-dark" style="height:40px;width:auto;object-fit:contain;">
        </a>
        <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema" aria-label="Alternar tema"><svg class="bi bi-moon-fill" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/></svg></button>
    </nav>

    <main class="container">
        <div style="margin-bottom:28px;">
            <a class="btn-back" href="/empresa/painel.php">← Voltar ao painel</a>
            <div class="eyebrow">Perfil empresarial</div>
            <h1 class="page-title">Editar empresa</h1>
        </div>

        <div class="form-card">
            <?php if ($erro !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($salvo): ?>
                <div class="form-success" role="status">Perfil da empresa atualizado.</div>
            <?php endif; ?>
            <?php if ($senhaAlterada): ?>
                <div class="form-success" role="status">Senha alterada com sucesso.</div>
            <?php endif; ?>

            <form action="/src/atualizar-empresa.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <?php if (!empty($empresa['logo'])): ?>
                        <div>
                            <label class="form-label">Logo atual</label>
                            <img src="<?= htmlspecialchars($empresa['logo'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo atual de <?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>" style="width:104px;height:104px;object-fit:contain;border:1px solid var(--border);border-radius:var(--radius);background:var(--background);padding:8px;">
                        </div>
                    <?php endif; ?>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="nome">Nome público<span class="required">*</span></label>
                            <input id="nome" name="nome" class="form-input" value="<?= htmlspecialchars($empresa['nome'], ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>
                        </div>
                        <div>
                            <label class="form-label" for="razao_social">Razão social</label>
                            <input id="razao_social" name="razao_social" class="form-input" value="<?= htmlspecialchars($empresa['razao_social'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="180">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="cnpj">CNPJ<span class="required">*</span></label>
                            <input id="cnpj" name="cnpj" class="form-input" value="<?= htmlspecialchars($empresa['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="18" required>
                        </div>
                        <div>
                            <label class="form-label" for="setor">Setor<span class="required">*</span></label>
                            <select id="setor" name="setor" class="form-input" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($setores as $setor): ?>
                                    <option value="<?= htmlspecialchars($setor, ENT_QUOTES, 'UTF-8') ?>" <?= ($empresa['setor'] ?? '') === $setor ? 'selected' : '' ?>><?= htmlspecialchars($setor, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="porte">Porte<span class="required">*</span></label>
                            <select id="porte" name="porte" class="form-input" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($portes as $porte): ?>
                                    <option value="<?= htmlspecialchars($porte, ENT_QUOTES, 'UTF-8') ?>" <?= ($empresa['porte'] ?? '') === $porte ? 'selected' : '' ?>><?= htmlspecialchars($porte, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="email_corporativo">E-mail corporativo<span class="required">*</span></label>
                            <input id="email_corporativo" type="email" name="email_corporativo" class="form-input" value="<?= htmlspecialchars($empresa['email_corporativo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="telefone">Telefone</label>
                            <input id="telefone" name="telefone" class="form-input" value="<?= htmlspecialchars($empresa['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="30">
                        </div>
                        <div>
                            <label class="form-label" for="cidade_sede">Cidade sede<span class="required">*</span></label>
                            <input id="cidade_sede" name="cidade_sede" class="form-input" value="<?= htmlspecialchars($empresa['cidade_sede'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="120" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="site">Site</label>
                        <input id="site" type="url" name="site" class="form-input" value="<?= htmlspecialchars($empresa['site'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255" placeholder="https://empresa.com.br">
                    </div>

                    <div>
                        <label class="form-label" for="descricao">Sobre a empresa</label>
                        <textarea id="descricao" name="descricao" class="form-input" rows="5"><?= htmlspecialchars($empresa['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div>
                        <label id="logo-label" class="form-label" for="logo" data-default-text="Imagem de perfil da empresa (JPG/PNG, até 2 MB)" aria-live="polite">Imagem de perfil da empresa (JPG/PNG, até 2 MB)</label>
                        <input id="logo" type="file" name="logo" class="form-input" accept="image/png,image/jpeg">
                        <img id="logo-preview" class="upload-preview" alt="Prévia da nova logo" hidden>
                    </div>

                    <fieldset class="password-settings">
                        <legend>Alterar senha</legend>
                        <p class="password-help">Opcional. Preencha os três campos para trocar a senha da empresa.</p>
                        <div class="password-fields">
                            <div>
                                <label class="form-label" for="senha-atual">Senha atual</label>
                                <div class="password-field">
                                    <input id="senha-atual" type="password" name="senha_atual" class="form-input" autocomplete="current-password">
                                    <button type="button" class="password-toggle" aria-label="Mostrar senha atual" aria-controls="senha-atual" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label" for="nova-senha">Nova senha</label>
                                <div class="password-field">
                                    <input id="nova-senha" type="password" name="nova_senha" class="form-input" minlength="8" autocomplete="new-password">
                                    <button type="button" class="password-toggle" aria-label="Mostrar nova senha" aria-controls="nova-senha" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>
                            <div>
                                <label class="form-label" for="confirmar-nova-senha">Confirmar nova senha</label>
                                <div class="password-field">
                                    <input id="confirmar-nova-senha" type="password" name="confirmar_senha" class="form-input" minlength="8" autocomplete="new-password">
                                    <button type="button" class="password-toggle" aria-label="Mostrar confirmação da senha" aria-controls="confirmar-nova-senha" aria-pressed="false">Mostrar</button>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="form-actions">
                    <a href="/empresa/painel.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </main>
    <script src="/JS/empresa-editar-perfil.js"></script>
    <script src="/JS/alternar-senha.js"></script>
    <script src="/JS/tema.js"></script>
</body>
</html>