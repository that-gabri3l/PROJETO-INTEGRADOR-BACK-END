# Recuperação de senha por e-mail — contexto do projeto

Este documento registra decisões e passos da implementação para facilitar a
continuidade do trabalho. Não inclua credenciais, tokens reais ou senhas neste
arquivo.

Preferência de continuidade: registrar aqui futuras alterações relevantes,
decisões e validações desta funcionalidade quando ajudarem a retomada do trabalho.

## Objetivo e decisões

- Implementar “Esqueceu a senha?” para candidatos e empresas.
- Usar e-mail com link temporário para que a própria pessoa escolha uma nova
  senha antes de entrar.
- Não enviar a senha atual nem uma senha gerada pelo sistema.
- Não usar Composer. O envio é feito pela função nativa `mail()` do PHP.
- O VS Code é apenas o editor; o PHP e o servidor/relay de e-mail precisam estar
  configurados no ambiente em que a aplicação roda.

## Arquivos envolvidos

- `cadastro/login.php`: link de recuperação e aviso após redefinição bem-sucedida.
- `cadastro/recuperar-senha.php`: formulário para informar o e-mail.
- `cadastro/redefinir-senha.php`: formulário de nova senha; abrir o link por GET
  não consome o token, pois verificadores automáticos de e-mail podem acessá-lo.
- `src/solicitar-recuperacao-senha.php`: valida o pedido, registra a solicitação
  e solicita o envio do e-mail.
- `src/processar-redefinicao-senha.php`: valida e consome o token, grava a nova
  senha e invalida outros tokens pendentes da conta.
- `src/recuperacao-senha.php`: valida o formato do token e consulta tokens
  ativos.
- `src/enviar-email-recuperacao.php`: monta e envia a mensagem com `mail()`.
- `CSS/login.css`: estilos e estados de foco reutilizados pelas páginas.
- `database.sql`: cria a tabela `recuperacoes_senha`.
- `README.md`: descreve configuração e ativação.

## Fluxo e validações implementados

1. A página de recuperação aceita e valida o formato do e-mail e usa token CSRF.
2. A resposta não revela se o endereço está cadastrado.
3. A busca prioriza contas ativas em `usuarios`. Também contempla candidatos
   legados cujo `candidatos.usuario_id` seja `NULL`. Empresas usam a conta de
   `usuarios`.
4. O sistema gera um token aleatório de 32 bytes. Só o SHA-256 do token é
   armazenado; o token em texto claro vai no link do e-mail.
5. O token expira em 30 minutos, só pode ser usado uma vez e, após a redefinição,
   os demais tokens pendentes da mesma conta são invalidados.
6. Há limite de cinco pedidos por e-mail ou IP em 15 minutos. Os identificadores
   usados para o limite são armazenados como HMAC; registros com mais de um dia
   são removidos durante novos pedidos.
7. A nova senha precisa ter de 8 a 72 bytes, deve coincidir com a confirmação e
   não pode ser igual à senha atual. O servidor usa `password_hash()`.
8. A atualização ocorre em transação e usa consultas preparadas. O formulário
   de redefinição também usa CSRF.
9. O endereço-base do link é configurado no ambiente, não derivado do cabeçalho
   `Host` recebido na requisição.

## Banco de dados

A tabela `recuperacoes_senha` relaciona o token a `usuarios` ou a um candidato
legado e mantém hashes, expiração e data de uso. **Não são necessários INSERTs
manuais ou dados iniciais**: os registros são criados durante pedidos reais de
recuperação.

O phpMyAdmin apontou erro de análise na constraint `CHECK` da tabela. A constraint
foi removida de `database.sql` para compatibilidade com o analisador; o código
do processador define os campos de usuário/candidato e token de forma coerente.
As chaves estrangeiras e os índices foram mantidos.

O arquivo usa `CREATE TABLE IF NOT EXISTS`, então pode ser executado novamente
para criar tabelas ausentes. Isso não altera uma tabela que já exista. Para
conferir o banco em uso:

```sql
SHOW TABLES LIKE 'recuperacoes_senha';
DESCRIBE recuperacoes_senha;
SHOW CREATE TABLE recuperacoes_senha;
```

Em 2026-11-24, o usuário informou que importou o SQL corrigido no banco sem
erros. A conexão e o fluxo completo ainda não foram testados diretamente contra
esse banco. Se uma tabela com a estrutura anterior já tiver sido criada,
`CREATE TABLE IF NOT EXISTS` não a atualiza; confira-a antes de qualquer
alteração manual.

No último estado observado, `jobboard_bd.sql` estava como arquivo não rastreado
no repositório. Ele não foi inspecionado nem alterado; não confundir esse arquivo
com o `database.sql` versionado.

## Configuração necessária para funcionar

Configure no ambiente do processo PHP, sem salvar segredos no repositório:

- `INFODIN_BASE_URL`: URL pública da aplicação; HTTPS em produção.
- `INFODIN_MAIL_FROM`: endereço remetente autorizado pelo serviço de e-mail.
- `INFODIN_RECOVERY_PEPPER`: segredo aleatório de pelo menos 32 caracteres para
  gerar HMACs usados no limite de pedidos.
- As variáveis de banco `INFODIN_DB_*` já usadas pelo projeto, quando aplicável.

`mail()` precisa de um serviço de envio/relay configurado no PHP ou na
hospedagem. Windows local normalmente exige configuração adicional no `php.ini`.
Provedores que exigem SMTP autenticado podem não funcionar diretamente com
`mail()`; uma alternativa sem Composer é adaptar o envio para a API HTTPS do
provedor com cURL.

Para testar localmente sem enviar e-mails reais, a opção recomendada é usar
Mailpit como relay SMTP local (`127.0.0.1:1025`) e abrir sua caixa de entrada de
teste em `http://127.0.0.1:8025`. Essa configuração ainda não foi instalada nem
validada neste ambiente.

### Roteiro de teste local com Mailpit

1. Se Docker estiver instalado, rode em um terminal e mantenha-o aberto:

   ```powershell
   docker run --rm --name infodin-mailpit -p 127.0.0.1:1025:1025 -p 127.0.0.1:8025:8025 axllent/mailpit
   ```

   Se não houver Docker, use o executável local do Mailpit. O SMTP de teste
   escuta em `127.0.0.1:1025` e a interface de mensagens em
   `http://127.0.0.1:8025`; as mensagens ficam locais e não são entregues ao
   destinatário real.

2. No terminal do PHP CLI, descubra qual arquivo de configuração está ativo:

   ```powershell
   php --ini
   ```

   Edite o caminho indicado como `Loaded Configuration File`. Na seção
   `[mail function]`, ajuste ou adicione:

   ```ini
   SMTP = 127.0.0.1
   smtp_port = 1025
   sendmail_from = no-reply@example.com
   ```

   O Mailpit local não requer autenticação SMTP. Depois de salvar o `php.ini`,
   reinicie o servidor PHP. Se estiver usando Apache/XAMPP, configure o `php.ini`
   daquele Apache e reinicie o Apache em vez de usar o arquivo do CLI.

3. Em outro terminal PowerShell, na raiz do repositório, defina as variáveis e
   inicie o servidor:

   ```powershell
   $env:INFODIN_BASE_URL = "http://localhost:8000"
   $env:INFODIN_MAIL_FROM = "no-reply@example.com"
   $env:INFODIN_RECOVERY_PEPPER = (php -r "echo bin2hex(random_bytes(32));")
   php -S localhost:8000
   ```

   Defina também `INFODIN_DB_HOST`, `INFODIN_DB_NAME`, `INFODIN_DB_USER` e
   `INFODIN_DB_PASS` se os valores padrão não corresponderem ao banco local.
   Variáveis PowerShell `$env:` valem para aquele terminal e seus processos
   filhos; configure-as antes de iniciar `php -S`. Para produção, mantenha o
   pepper estável e protegido, em vez de gerar um novo a cada reinício.

4. Abra `http://127.0.0.1:8025` para deixar a caixa de entrada do Mailpit visível
   e `http://localhost:8000/cadastro/login.php` para testar a aplicação. Peça a
   recuperação usando o e-mail de uma conta cadastrada, abra a mensagem no
   Mailpit e siga o link para definir a nova senha.

## Verificações já realizadas

- `php -l` passou nos arquivos PHP novos e alterados.
- O formato de token aceitou um token hexadecimal de 64 caracteres e rejeitou
  uma entrada inválida.
- `git diff --check` passou.
- A página pública de solicitação respondeu HTTP 200 no servidor PHP local; o
  CSS também respondeu HTTP 200.
- O usuário informou que o SQL corrigido foi importado sem erros. A aplicação
  ainda não foi testada de ponta a ponta com esse banco nem com envio de e-mail.
- Mailpit e a configuração de `mail()` no `php.ini` ainda não foram instalados
  ou validados neste ambiente.

## Próximos passos ao retomar

1. Opcionalmente confirmar `recuperacoes_senha` e suas colunas no banco com os
   comandos SQL acima.
2. Configurar Mailpit, o `php.ini` usado pelo servidor e as variáveis de
   ambiente; reiniciar o PHP depois das alterações.
3. Testar pedido com uma conta existente e inexistente, link expirado ou já
   usado, senhas divergentes, senha igual à atual e limite de solicitações.
4. Após o teste local, configurar um relay/provedor autorizado e HTTPS antes de
   ativar a funcionalidade em produção.
