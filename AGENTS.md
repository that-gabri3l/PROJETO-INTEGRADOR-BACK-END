# AGENTS.md — Projeto Infodin

## Objetivo do projeto

Este repositório contém o front-end e o processamento PHP inicial da plataforma de empregos Infodin. O sistema engloba página inicial, busca de vagas, cadastro/login, painéis de candidatos e empresas e publicação de vagas.

A aplicação é deliberadamente simples: PHP tradicional, HTML, CSS e JavaScript vanilla, sem framework, Composer, npm ou build de frontend.

## Estrutura e responsabilidade dos arquivos

- `index.php`: página inicial, busca por vagas e destaques.
- `cadastro/escolha.php`: escolha entre candidato e empresa.
- `cadastro/login.php`: interface de login.
- `candidato/cadastro.php`: cadastro de candidato em três passos.
- `candidato/painel.php`: painel do candidato, com candidaturas, status, vagas salvas e perfil.
- `empresa/cadastro.php`: cadastro de empresa em três passos.
- `empresa/painel.php`: painel da empresa, com vagas e candidaturas recentes.
- `empresa/editar-vaga.php`: edição, alteração de status e exclusão de vaga da empresa autenticada.
- `empresa/ver-candidato.php`: perfil do candidato associado a uma candidatura de vaga da empresa; permite avançar o status da candidatura.
- `vagas/index.php`: listagem de vagas ativas com busca e filtros conectados ao banco.
- `vagas/detalhes.php`: detalhes de uma vaga ativa consultada no banco.
- `vagas/publicar.php`: formulário visual de publicação de vaga.
- `src/conexao.php`: conexão PDO com MySQL/MariaDB.
- `src/processar-candidato.php`: processa o cadastro de candidatos e upload de foto.
- `src/processar-login.php`: autentica usuários e inicia a sessão correspondente.
- `src/processar-empresa.php`: processa o cadastro de empresas.
- `src/processar-vaga.php`: cria vagas para empresas autenticadas.
- `src/gerenciar-vaga.php`: atualiza ou exclui vagas, verificando a empresa proprietária.
- `src/processar-candidatura.php`: registra a candidatura do usuário candidato.
- `src/atualizar-status-candidatura.php`: valida e grava as transições de status autorizadas para a empresa proprietária.
- `src/sessao.php`: inicialização de sessão, autorização por tipo de usuário e proteção CSRF.
- `JS/tema.js`: alternância e persistência do tema em `localStorage` (`theme`).
- `JS/candidato-avancar-passos.js`: navegação por passos do cadastro de candidato.
- `JS/empresa-avancar-passos.js`: navegação por passos do cadastro de empresa.
- `JS/empresa-gerenciar-vaga.js`: confirmação da exclusão de vaga.
- `CSS/`: estilos separados por página ou área, incluindo `painel-candidato.css`, `painel-empresa.css` e `perfil-candidato-empresa.css`.
- `images/`: logotipos claro e escuro.
- `logs/`: reserva atual para arquivos de log.
 
## Fluxos e rotas existentes

- Página inicial → `/vagas/index.php?q=...`
- Cadastro → `cadastro/escolha.php` → `candidato/cadastro.php` ou `empresa/cadastro.php`
- Login → `cadastro/login.php` → `src/processar-login.php`
- Candidato → `candidato/painel.php` → seção “Minhas candidaturas”, com o status atual de cada candidatura
- Empresa → `empresa/painel.php` → gerenciar vagas em `empresa/editar-vaga.php`
- Empresa → candidaturas recentes → `empresa/ver-candidato.php`, com perfil e atualização do status
- Publicar vaga → `vagas/publicar.php`
- Detalhes da vaga → `vagas/detalhes.php`

O processamento de login, cadastro de empresa, publicação de vagas e envio de candidaturas existe em `src/`. Não presumir que os antigos links `/api/...` sejam endpoints implementados.

## Banco de dados

O projeto usa o banco MySQL/MariaDB chamado `jobboard_bd`, com conexão configurada em `src/conexao.php`:

- Host: configurável por `INFODIN_DB_HOST`, padrão `localhost`
- Banco: configurável por `INFODIN_DB_NAME`, padrão `jobboard_bd`
- Usuário: configurável por `INFODIN_DB_USER`, padrão `root`
- Senha: configurável por `INFODIN_DB_PASS`, padrão vazia
- Charset: `utf8mb4`
- PDO em modo de exceção

O modelo completo está versionado em `database.sql` e inclui usuários, candidatos, empresas, vagas, candidaturas e vagas salvas. O campo `candidaturas.status` aceita `recebida`, `em_analise`, `aprovada` e `recusada`.

O cadastro inicial de candidato persiste:

- `nome`
- `email`
- `senha` (hash)
- `cidade`
- `telefone`
- `data_nascimento`
- `foto_perfil`

`src/atualizar-candidato.php` também persiste os campos profissionais do perfil (`cargo`, `area`, `experiencia`, `escolaridade`, `regime_preferido`, `linkedin` e `sobre`). O perfil visto pela empresa usa esses dados e os campos da candidatura `portifolio`, `mensagem`, `status` e `criado_em`. A empresa só pode acessar candidaturas associadas a vagas próprias.

Transições de status implementadas para candidaturas:

- `recebida` → `em_analise` ou `recusada`
- `em_analise` → `aprovada` ou `recusada`
- `aprovada` e `recusada` são estados finais e não podem ser alterados pela interface.

O painel do candidato lê o status atual da candidatura no banco. Ao atualizar o status pela página da empresa, o candidato verá o novo estado ao carregar novamente o painel.

## Sessões e autenticação

As páginas privadas usam `src/sessao.php`, que inicia a sessão e fornece `exigirTipo()` para restringir painéis e operações ao tipo de usuário correto. Formulários que alteram dados devem incluir e validar token CSRF com `gerarTokenCsrf()` e `exigirTokenCsrf()`. Operações sobre vagas e candidaturas também devem validar no banco se o registro pertence à empresa autenticada; não confiar em IDs enviados pelo navegador.

## Convenções a seguir

- Manter o código em português nas interfaces, campos de formulário e textos visuais existentes.
- Usar PHP sem framework até que uma migração seja solicitada.
- Usar JavaScript vanilla e manter scripts separados em `JS/`.
- Usar um arquivo CSS específico da página e carregar `CSS/mobile.css` quando necessário.
- Preservar o tema claro/escuro baseado em `:root` e `[data-theme="dark"]`.
- Usar caminhos consistentes e compatíveis com Linux: `CSS/`, `JS/` e `images/` na raiz do projeto.
- Para incluir arquivos PHP entre diretórios, preferir caminhos absolutos controlados pelo PHP:

```php
require_once __DIR__ . '/../src/conexao.php';
```

- Não misturar rotas relativas entre páginas em subdiretórios e URLs absolutas da aplicação.
- Validar sempre no servidor: campos obrigatórios, confirmação de senha, e-mail, tamanho e tipo de arquivo.
- Não exibir mensagens técnicas ou detalhes de exceções PDO ao usuário final.
- Usar `password_hash()` ao cadastrar e `password_verify()` ao autenticar.
- Escapar dados exibidos com `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Preferir consultas preparadas com PDO em todas as operações SQL.
- Não assumir que o projeto está instalado na raiz do domínio; usar caminhos relativos ou uma base configurável quando apropriado.

## Convenções de HTML, PHP e CSS

- Páginas PHP devem manter `<!doctype html>`, `lang="pt-BR"` e separar conteúdo visual de processamento server-side.
- Formulários devem informar `method` e `action` válidos em `.php`.
- Usar `multipart/form-data` apenas quando houver upload.
- Não remover atributos `accept` dos uploads sem substituir por validação real no servidor.
- Evitar estilos inline em novas páginas; usar o arquivo CSS correspondente em `CSS/`.
- Reutilizar as variáveis CSS existentes em vez de criar novas cores aleatórias.
- No tema claro/esdcuro, manter contraste e legibilidade dos componentes.
- Todo elemento interativo deve ter estado `hover`, `focus` e, quando aplicável, estado desativado.

## JavaScript

- Scripts existentes usam `"use strict"` e manipulação direta do DOM.
- Reutilizar a chave `theme` no `localStorage` ao manter o sistema de tema.
- Verificar se o elemento existe antes de associar eventos, pois nem toda página possui botão de tema.
- Não duplicar lógica de formulário entre páginas; centralizar validações compartilhadas somente se for introduzido um módulo comum.
- Validar no navegador e também no servidor; a validação front-end não substitui segurança.

## Problemas conhecidos que novas alterações devem corrigir ou evitar ampliar

- `candidato/cadastro.php` usa um `action` relativo que pode resolver incorretamente.
- `src/processar-candidato.php` usa redirecionamentos para nomes `.html` que não existem; usar páginas `.php` atuais.
- O processador não possui tabela criada automaticamente nem migration.
- Senha e confirmação de senha aparecem em mais de uma etapa do cadastro.
- O cadastro inicial de candidato ainda não persiste todos os campos profissionais; a edição de perfil persiste esses campos.
- Alguns links usam letras misturadas em `JS/` e `js/`; preservar a capitalização real `JS/`.
- `vagas/detalhes.php`, `vagas/publicar.php` e outras páginas possuem links antigos para arquivos `.html` ou `#`.
- `CSS/mobile.css` não está carregado por todas as páginas.
- Todo novo endpoint de upload deve validar tipo real, tamanho e nome seguro no servidor; não confiar apenas no atributo HTML `accept`.
- O diretório `.vs/` não pertence ao projeto e não deve ser alterado nem versionado.

## Diretrizes para implementar mudanças

### Pequenas correções

1. Ler o PHP, CSS e JS da página afetada antes de editar.
2. Corrigir apenas a responsabilidade solicitada, sem transformar o projeto inteiro em uma arquitetura diferente.
3. Preservar nomes de IDs e campos já usados por formulários e scripts.
4. Corrigir caminhos relativos quebrados e manter consistência de maiúsculas/minúsculas.
5. Após alterações, verificar ação do formulário, links, scripts carregados e estilos carregados.

### Funcionalidades completas

Ao implementar login, cadastro, vagas ou candidaturas:

- Criar esquema SQL ou migrations versionadas.
- Criar módulos PHP claros para conexão, autenticação e operações do domínio.
- Reutilizar `src/sessao.php` para autorização de painéis e validar CSRF em formulários que alteram dados.
- Usar consultas parametrizadas.
- Validar todas as entradas no servidor.
- Fazer todos os redirecionamentos para rotas `.php` existentes.
- Substituir dados hardcoded por consultas PDO somente após criar o esquema correspondente.
- Incluir estados de sucesso, erro, vazio e carregamento onde relevante.

### Interface

- Manter o visual existente: cards, bordas sutis, gradientes discretos e tipografia do projeto.
- Manter responsividade em desktop e mobile.
- Validar contraste dos temas claro e escuro.
- Não alterar o fluxo de passos do formulário sem atualizar tanto o HTML quanto o JavaScript correspondente.

## Execução local

Não há scripts npm ou Composer. Em ambiente local com PHP instalado, a partir da raiz:

```powershell
php -S localhost:8000
```

O projeto também pode ser executado por Apache/Nginx, desde que o diretório raiz seja publicado corretamente. Para recursos persistentes, MySQL/MariaDB deve existir com o banco `jobboard_bd` e as tabelas do `database.sql`; o diretório `src/uploads/fotos_perfil/` precisa permitir escrita pelo processo PHP.