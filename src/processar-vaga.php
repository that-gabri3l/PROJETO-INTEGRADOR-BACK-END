<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/sessao.php';

exigirTipo('empresa');
exigirTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /vagas/publicar.php');
    exit;
}

function erroVaga(string $mensagem): void
{
    header('Location: /vagas/publicar.php?erro=' . urlencode($mensagem));
    exit;
}

$titulo = trim($_POST['titulo'] ?? '');
$area = trim($_POST['area'] ?? '');
$regime = trim($_POST['regime'] ?? '');
$localizacao = trim($_POST['localizacao'] ?? '');
$salarioMin = trim($_POST['salario_min'] ?? '');
$salarioMax = trim($_POST['salario_max'] ?? '');
$exibirSalario = ($_POST['exibir_salario'] ?? 'nao') === 'sim';
$descricao = trim($_POST['descricao'] ?? '');
$requisitos = trim($_POST['requisitos'] ?? '');
$beneficios = trim($_POST['beneficios'] ?? '');
$status = ($_POST['acao'] ?? 'publicar') === 'rascunho' ? 'rascunho' : 'ativa';

if ($titulo === '' || $area === '' || $regime === '' || $localizacao === '' || $descricao === '' || $requisitos === '') {
    erroVaga('Preencha todos os campos obrigatórios.');
}

if (!in_array($area, [
    'Agronegócio', 'Alimentação e Bebidas', 'Arquitetura e Construção', 'Automotivo',
    'Comunicação e Mídia', 'Consultoria', 'Educação', 'Energia', 'Engenharia',
    'Finanças', 'Indústria e Manufatura', 'Imobiliário', 'Jurídico', 'Logística',
    'Meio Ambiente', 'Recursos Humanos', 'Saúde', 'Tecnologia', 'Telecomunicações',
    'Transportes', 'Turismo e Hotelaria', 'Varejo', 'Outro',
], true)) {
    erroVaga('Informe uma área válida.');
}

if (!in_array($regime, ['Presencial', 'Híbrido', 'Remoto'], true)) {
    erroVaga('Informe um regime válido.');
}

$salarioMin = $salarioMin !== '' ? (float) str_replace(',', '.', $salarioMin) : null;
$salarioMax = $salarioMax !== '' ? (float) str_replace(',', '.', $salarioMax) : null;

if (($salarioMin !== null && $salarioMin < 1) || ($salarioMax !== null && $salarioMax < 1)) {
    erroVaga('Os salários devem ser maiores ou iguais a R$ 1.');
}

if ($salarioMin !== null && $salarioMax !== null && $salarioMax < $salarioMin) {
    erroVaga('O salário máximo não pode ser menor que o mínimo.');
}

$empresaId = $_SESSION['perfil_id'] ?? null;
if (!$empresaId) {
    erroVaga('Complete o perfil da empresa antes de publicar vagas.');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO vagas
            (empresa_id, titulo, area, regime, localizacao, salario_min, salario_max, exibir_salario, descricao, requisitos, beneficios, status)
         VALUES
            (:empresa_id, :titulo, :area, :regime, :localizacao, :salario_min, :salario_max, :exibir_salario, :descricao, :requisitos, :beneficios, :status)' 
    );

    $stmt->execute([
        'empresa_id' => $empresaId,
        'titulo' => $titulo,
        'area' => $area,
        'regime' => $regime,
        'localizacao' => $localizacao,
        'salario_min' => $salarioMin,
        'salario_max' => $salarioMax,
        'exibir_salario' => $exibirSalario ? 1 : 0,
        'descricao' => $descricao,
        'requisitos' => $requisitos,
        'beneficios' => $beneficios !== '' ? $beneficios : null,
        'status' => $status,
    ]);

    $resultado = $status === 'rascunho' ? 'rascunho' : 'publicada';
    header('Location: /empresa/painel.php?vaga=' . $resultado);
    exit;
} catch (PDOException $e) {
    erroVaga('Não foi possível publicar a vaga. Tente novamente.');
}
