<?php
require_once __DIR__ . '/sessao.php';
exigirTipo('empresa');
exigirTokenCsrf();
require_once __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /empresa/painel.php');
    exit;
}

$vagaId = filter_var($_POST['vaga_id'] ?? null, FILTER_VALIDATE_INT);
$empresaId = $_SESSION['perfil_id'] ?? null;

function erroGerenciarVaga(?int $vagaId, string $mensagem): void
{
    $destino = $vagaId && $vagaId > 0
        ? '/empresa/editar-vaga.php?' . http_build_query(['id' => $vagaId, 'erro' => $mensagem])
        : '/empresa/painel.php';
    header('Location: ' . $destino);
    exit;
}

function textoDentroDoLimite(string $texto, int $limite): bool
{
    $comprimento = preg_match_all('/./us', $texto);
    return $comprimento !== false && $comprimento <= $limite;
}

if (!$vagaId || $vagaId < 1 || !$empresaId) {
    erroGerenciarVaga(null, 'Não foi possível identificar a vaga ou a empresa.');
}

$acao = $_POST['acao'] ?? '';
if (!in_array($acao, ['salvar', 'excluir'], true)) {
    erroGerenciarVaga($vagaId, 'Ação inválida.');
}

$dados = [];
if ($acao === 'salvar') {
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $area = trim((string) ($_POST['area'] ?? ''));
    $regime = trim((string) ($_POST['regime'] ?? ''));
    $localizacao = trim((string) ($_POST['localizacao'] ?? ''));
    $descricao = trim((string) ($_POST['descricao'] ?? ''));
    $requisitos = trim((string) ($_POST['requisitos'] ?? ''));
    $beneficios = trim((string) ($_POST['beneficios'] ?? ''));
    $status = (string) ($_POST['status'] ?? '');

    if ($titulo === '' || !textoDentroDoLimite($titulo, 150) || $area === '' || $regime === '' || $localizacao === '' || !textoDentroDoLimite($localizacao, 150) || $descricao === '' || $requisitos === '') {
        erroGerenciarVaga($vagaId, 'Preencha todos os campos obrigatórios e confira o título informado.');
    }
    $areasPermitidas = [
        'Agronegócio', 'Alimentação e Bebidas', 'Arquitetura e Construção', 'Automotivo',
        'Comunicação e Mídia', 'Consultoria', 'Educação', 'Energia', 'Engenharia',
        'Finanças', 'Indústria e Manufatura', 'Imobiliário', 'Jurídico', 'Logística',
        'Meio Ambiente', 'Recursos Humanos', 'Saúde', 'Tecnologia', 'Telecomunicações',
        'Transportes', 'Turismo e Hotelaria', 'Varejo', 'Outro',
        'Design', 'Marketing', 'Dados', 'Gestão', 'Vendas', 'Financeiro', 'RH',
    ];
    if (!in_array($area, $areasPermitidas, true)) {
        erroGerenciarVaga($vagaId, 'Informe uma área válida.');
    }
    if (!in_array($regime, ['Presencial', 'Híbrido', 'Remoto'], true)) {
        erroGerenciarVaga($vagaId, 'Informe um regime válido.');
    }
    if (!in_array($status, ['rascunho', 'ativa', 'pausada', 'encerrada'], true)) {
        erroGerenciarVaga($vagaId, 'Informe um status válido.');
    }

    $salarios = [];
    foreach (['salario_min', 'salario_max'] as $campo) {
        $valor = trim((string) ($_POST[$campo] ?? ''));
        if ($valor === '') {
            $salarios[$campo] = null;
            continue;
        }
        if (!preg_match('/^\d+(?:[.,]\d{1,2})?$/', $valor)) {
            erroGerenciarVaga($vagaId, 'Informe valores válidos para os salários.');
        }
        $salario = (float) str_replace(',', '.', $valor);
        if ($salario < 1 || $salario > 9999999999.99) {
            erroGerenciarVaga($vagaId, 'Os salários devem estar entre R$ 1 e R$ 9.999.999.999,99.');
        }
        $salarios[$campo] = $salario;
    }

    if ($salarios['salario_min'] !== null && $salarios['salario_max'] !== null && $salarios['salario_max'] < $salarios['salario_min']) {
        erroGerenciarVaga($vagaId, 'O salário máximo não pode ser menor que o mínimo.');
    }

    $exibirSalario = (string) ($_POST['exibir_salario'] ?? 'nao');
    if (!in_array($exibirSalario, ['sim', 'nao'], true)) {
        erroGerenciarVaga($vagaId, 'Informe uma opção válida para exibição do salário.');
    }

    $dados = [
        'titulo' => $titulo,
        'area' => $area,
        'regime' => $regime,
        'localizacao' => $localizacao,
        'salario_min' => $salarios['salario_min'],
        'salario_max' => $salarios['salario_max'],
        'exibir_salario' => $exibirSalario === 'sim' ? 1 : 0,
        'descricao' => $descricao,
        'requisitos' => $requisitos,
        'beneficios' => $beneficios !== '' ? $beneficios : null,
        'status' => $status,
    ];
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM vagas WHERE id = :id AND empresa_id = :empresa_id FOR UPDATE');
    $stmt->execute(['id' => $vagaId, 'empresa_id' => $empresaId]);
    if (!$stmt->fetchColumn()) {
        $pdo->rollBack();
        erroGerenciarVaga(null, 'A vaga não foi encontrada para esta empresa.');
    }

    if ($acao === 'excluir') {
        $stmt = $pdo->prepare('DELETE FROM vagas WHERE id = :id AND empresa_id = :empresa_id');
        $stmt->execute(['id' => $vagaId, 'empresa_id' => $empresaId]);
        $pdo->commit();
        header('Location: /empresa/painel.php?vaga=excluida');
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE vagas
        SET titulo = :titulo, area = :area, regime = :regime, localizacao = :localizacao,
            salario_min = :salario_min, salario_max = :salario_max, exibir_salario = :exibir_salario,
            descricao = :descricao, requisitos = :requisitos, beneficios = :beneficios, status = :status
        WHERE id = :id AND empresa_id = :empresa_id'
    );
    $stmt->execute($dados + ['id' => $vagaId, 'empresa_id' => $empresaId]);
    $pdo->commit();

    header('Location: /empresa/painel.php?vaga=atualizada');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Gerenciamento de vaga falhou: ' . $e->getMessage());
    erroGerenciarVaga($vagaId, 'Não foi possível salvar as alterações. Tente novamente.');
}
