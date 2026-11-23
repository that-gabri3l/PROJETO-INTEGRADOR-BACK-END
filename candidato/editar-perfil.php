<?php
require_once __DIR__ . '/../src/sessao.php';
exigirTipo('candidato');
require_once __DIR__ . '/../src/conexao.php';

$candidatoId = $_SESSION['perfil_id'] ?? null;
if (!$candidatoId) {
    header('Location: /candidato/painel.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM candidatos WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $candidatoId]);
$candidato = $stmt->fetch();

if (!$candidato) {
    header('Location: /candidato/painel.php');
    exit;
}

$erro = trim((string) ($_GET['erro'] ?? ''));
$salvo = ($_GET['salvo'] ?? '') === '1';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil - Infodin</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&family=Work+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CSS/cadastro-candidato.css">
    <link rel="stylesheet" href="/CSS/mobile.css">
</head>
<body>
    <nav class="navbar">
        <a href="/candidato/painel.php" class="navbar-logo">
            <img src="/images/logo-claro-infodin.png" alt="Infodin" class="logo-light" style="height:40px;width:auto;object-fit:contain;">
            <img src="/images/logo-infodin.png" alt="Infodin" class="logo-dark" style="height:40px;width:auto;object-fit:contain;">
        </a>
        <button id="theme-toggle" class="theme-toggle" type="button" title="Mudar tema">🌗</button>
    </nav>

    <main class="container">
        <div style="margin-bottom: 28px;">
            <a class="btn-back" href="/candidato/painel.php">← Voltar ao painel</a>
            <div class="eyebrow">Perfil do candidato</div>
            <h1 class="page-title">Editar perfil</h1>
        </div>

        <div class="form-card">
            <?php if ($erro !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($salvo): ?>
                <div class="form-success" role="status">Perfil atualizado com sucesso.</div>
            <?php endif; ?>

            <form action="/src/atualizar-candidato.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerarTokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <?php if (!empty($candidato['foto_perfil'])): ?>
                        <div>
                            <label class="form-label">Foto atual</label>
                            <img src="<?= htmlspecialchars($candidato['foto_perfil'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil atual" style="width:88px;height:88px;object-fit:cover;border-radius:50%;">
                        </div>
                    <?php endif; ?>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="nome">Nome completo<span class="required">*</span></label>
                            <input id="nome" type="text" name="nome" class="form-input" value="<?= htmlspecialchars($candidato['nome'], ENT_QUOTES, 'UTF-8') ?>" maxlength="150" required>
                        </div>
                        <div>
                            <label class="form-label" for="email">E-mail<span class="required">*</span></label>
                            <input id="email" type="email" name="email" class="form-input" value="<?= htmlspecialchars($candidato['email'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="telefone">Telefone</label>
                            <input id="telefone" type="tel" name="telefone" class="form-input" value="<?= htmlspecialchars($candidato['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="30">
                        </div>
                        <div>
                            <label class="form-label" for="cidade">Cidade<span class="required">*</span></label>
                            <input id="cidade" type="text" name="cidade" class="form-input" value="<?= htmlspecialchars($candidato['cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="120" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="data_nascimento">Data de nascimento</label>
                            <input id="data_nascimento" type="date" name="data_nascimento" class="form-input" value="<?= htmlspecialchars($candidato['data_nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label class="form-label" for="foto_perfil">Alterar foto (JPG/PNG, até 2 MB)</label>
                            <input id="foto_perfil" type="file" name="foto_perfil" class="form-input" accept="image/png,image/jpeg">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="cargo">Cargo desejado</label>
                            <input id="cargo" type="text" name="cargo" class="form-input" value="<?= htmlspecialchars($candidato['cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="150">
                        </div>
                        <div>
                            <label class="form-label" for="area">Área de atuação</label>
                            <input id="area" type="text" name="area" class="form-input" value="<?= htmlspecialchars($candidato['area'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label class="form-label" for="experiencia">Experiência</label>
                            <select id="experiencia" name="experiencia" class="form-input">
                                <option value="">Selecione...</option>
                                <?php foreach (['Estagiário', 'Júnior', 'Pleno', 'Sênior'] as $nivel): ?>
                                    <option value="<?= htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8') ?>" <?= ($candidato['experiencia'] ?? '') === $nivel ? 'selected' : '' ?>><?= htmlspecialchars($nivel, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="escolaridade">Escolaridade</label>
                            <input id="escolaridade" type="text" name="escolaridade" class="form-input" value="<?= htmlspecialchars($candidato['escolaridade'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="regime_preferido">Regimes de trabalho preferidos</label>
                        <input id="regime_preferido" type="text" name="regime_preferido" class="form-input" value="<?= htmlspecialchars($candidato['regime_preferido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex.: Remoto, Híbrido">
                    </div>

                    <div>
                        <label class="form-label" for="linkedin">LinkedIn</label>
                        <input id="linkedin" type="url" name="linkedin" class="form-input" value="<?= htmlspecialchars($candidato['linkedin'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="255" placeholder="https://linkedin.com/in/seu-perfil">
                    </div>

                    <div>
                        <label class="form-label" for="sobre">Sobre você</label>
                        <textarea id="sobre" name="sobre" class="form-input" rows="5"><?= htmlspecialchars($candidato['sobre'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <label style="display:flex;align-items:center;gap:10px;">
                        <input type="checkbox" name="notificacoes" value="1" <?= !empty($candidato['notificacoes']) ? 'checked' : '' ?>>
                        Receber alertas de vagas por e-mail
                    </label>
                </div>

                <div class="form-actions">
                    <a href="/candidato/painel.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </main>

    <script src="/JS/tema.js"></script>
</body>
</html>