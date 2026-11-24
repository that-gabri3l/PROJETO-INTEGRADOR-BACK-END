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

No painel da empresa, use **Gerenciar** em uma vaga para editar suas informações,
alterar o status ou excluí-la. A exclusão também remove candidaturas e vagas salvas
relacionadas à vaga.
