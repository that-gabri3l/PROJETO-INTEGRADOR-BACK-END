# Infodin
Projeto com samuel, Gabriel, Perola e guilherme

## Banco de dados

1. Inicie o MySQL/MariaDB.
2. Acesse o phpMyAdmin ou o terminal do banco.
3. Execute o arquivo `database.sql` da raiz do projeto no banco existente `jobboard_bd`.
4. Confirme que foram criadas as tabelas necessárias sem apagar dados já existentes.
5. Se o MySQL tiver senha, defina as variáveis antes de iniciar o PHP:

```powershell
$env:INFODIN_DB_HOST = "localhost"
$env:INFODIN_DB_NAME = "jobboard_bd"
$env:INFODIN_DB_USER = "root"
$env:INFODIN_DB_PASS = "SUA_SENHA"
php -S localhost:8000
```

Acesse `http://localhost:8000`.

## Recuperação de senha por e-mail

O fluxo usa a função nativa `mail()` do PHP e não requer Composer. O servidor PHP
precisa ter um serviço de envio de e-mail configurado; em Windows local, isso
normalmente requer configurar um relay de e-mail no `php.ini` ou usar uma
hospedagem que ofereça envio de e-mail.

Execute novamente o `database.sql` para criar a tabela `recuperacoes_senha` sem
remover as tabelas ou dados existentes. Acrescente estas variáveis ao bloco de
configuração acima, antes de iniciar o PHP:

```powershell
$env:INFODIN_BASE_URL = "http://localhost:8000"
$env:INFODIN_MAIL_FROM = "nao-responda@seu-dominio.com"
$env:INFODIN_RECOVERY_PEPPER = "<resultado do gerador>"
```

Gere `INFODIN_RECOVERY_PEPPER` com `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`,
copie o resultado para a variável e mantenha-o fora do repositório. Se o
servidor PHP já estiver iniciado, reinicie-o após configurar as variáveis. Em
produção, use `INFODIN_BASE_URL` com HTTPS e um remetente autorizado pelo
provedor de e-mail. A resposta `mail()` confirma apenas que o PHP encaminhou a
mensagem ao serviço configurado, não que ela chegou à caixa de entrada.

No painel da empresa, use **Gerenciar** em uma vaga para editar suas informações,
alterar o status ou excluí-la. A exclusão também remove candidaturas e vagas salvas
relacionadas à vaga.

Na lista de candidatos recentes do painel da empresa, **Ver perfil** abre as
informações profissionais e os dados enviados pelo candidato na candidatura.

No painel do candidato, a seção **Minhas candidaturas** apresenta o status e uma
explicação de cada candidatura enviada.

No perfil do candidato aberto pela empresa, candidaturas em andamento podem ser
movidas para **Em análise**, **Aprovada** ou **Recusada**. Status finais não podem
ser alterados.
