# Infodin
Projeto com samuel, Gabriel, Perola e guilherme

## Banco de dados

1. Inicie o MySQL/MariaDB.
2. Acesse o phpMyAdmin ou o terminal do banco.
3. Execute o arquivo `database.sql` da raiz do projeto.
4. Confirme que foi criado o banco `infodin` e suas tabelas.
5. Configure, se necessário, `src/conexao.php` com host, usuário e senha do seu servidor.

O esquema inclui usuários, candidatos, empresas, vagas, candidaturas e vagas salvas. O arquivo pode ser executado novamente sem excluir tabelas ou dados existentes.

## Executar localmente

Na raiz do projeto:

```powershell
php -S localhost:8000
```

Acesse `http://localhost:8000`.
