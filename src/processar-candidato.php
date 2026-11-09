<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Receber os campos do formulário
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cidade = trim($_POST['cidade']);
    $senha = $_POST['senha']; // A senha também é obrigatória para ele conseguir fazer login depois
    
    // Campos Opcionais (Se o usuário não preencher, o PHP salva como nulo)
    $telefone = $_POST['telefone'] ?? null;
    $data_nascimento = $_POST['data_nascimento'] ?? null;

    // 2. Validação: Verifica se os campos obrigatórios estão preenchidos
    if (empty($nome) || empty($email) || empty($cidade) || empty($senha)) {
        die("<script>
                alert('Erro: Nome, E-mail, Senha e Cidade são obrigatórios!');
                window.history.back();
            </script>");
    }

    // Criptografa a senha para segurança
    $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

    // 3. Lógica para Upload da Foto de Perfil (Opcional)
    $caminho_foto = null; // Fica nulo se ele não enviar foto
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $pasta_destino = 'uploads/fotos_perfil/';
        
        // Se a pasta 'uploads' não existir no seu projeto, o PHP cria ela agora
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }
        
        // Cria um nome único para a foto (evita que duas fotos com nome 'perfil.jpg' se substituam)
        $nome_arquivo = time() . '_' . $_FILES['foto_perfil']['name'];
        $caminho_completo = $pasta_destino . $nome_arquivo;
        
        // Move a foto da memória temporária para a pasta do seu projeto
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_completo)) {
            $caminho_foto = $caminho_completo; // Salva o caminho para colocar no MySQL
        }
    }

    try {
        // 4. Inserir no Banco de Dados
        $sql = "INSERT INTO candidatos (nome, email, senha, cidade, telefone, data_nascimento, foto_perfil) 
                VALUES (:nome, :email, :senha, :cidade, :telefone, :data_nascimento, :foto_perfil)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_criptografada);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':foto_perfil', $caminho_foto);
        
        $stmt->execute();
        
        echo "<script>
                alert('Cadastro concluído com sucesso!');
                window.location.href = 'login.html';
            </script>";
            
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código de e-mail duplicado
            echo "<script>
                    alert('Erro: Este e-mail já está cadastrado no sistema!');
                    window.history.back();
                </script>";
        } else {
            echo "Erro no banco de dados: " . $e->getMessage();
        }
    }
} else {
    header("Location: cadastro-candidato.html");
    exit();
}
?>