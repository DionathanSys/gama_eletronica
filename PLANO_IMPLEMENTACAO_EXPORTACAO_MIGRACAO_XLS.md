# Plano de implementacao da exportacao de migracao XLS

Este plano detalha os meios necessarios para implementar a exportacao descrita em `PLANO_EXPORTACAO_MIGRACAO_XLS.md`, adaptando a proposta para a estrutura atual do projeto.

## Objetivo

Criar uma rotina executavel sem UI para exportar dados do banco legado em arquivos `.xlsx`, com rastreabilidade, baixo consumo de memoria e preservacao das chaves antigas necessarias para migracao.

## O que precisa existir

Para a exportacao funcionar de ponta a ponta, o projeto precisa ter:

1. conexao dedicada com o banco legado
2. writer `.xlsx` em streaming
3. service orquestrador do lote
4. uma action por tabela exportada
5. manifesto de saida
6. ponto de entrada executavel, preferencialmente via comando Artisan
7. testes de comportamento cobrindo o fluxo principal

## Ajustes de infraestrutura

### 1. Conexao `legacy` no banco

Adicionar uma conexao dedicada em `config/database.php`.

Objetivo:

- separar leitura do legado da conexao principal
- permitir apontar para outro host/banco sem alterar as actions
- evitar uso acidental de models do sistema atual na base errada

Variaveis de ambiente sugeridas:

- `LEGACY_DB_CONNECTION=mysql`
- `LEGACY_DB_HOST=`
- `LEGACY_DB_PORT=`
- `LEGACY_DB_DATABASE=`
- `LEGACY_DB_USERNAME=`
- `LEGACY_DB_PASSWORD=`

Observacao:

- como o projeto atual usa MySQL e a leitura sera via query builder, a primeira versao pode reaproveitar o mesmo driver com configuracao separada.

### 2. Dependencia de escrita `.xlsx`

Hoje o projeto nao declara `openspout/openspout` diretamente em `composer.json`.

Plano recomendado:

1. verificar se o uso indireto via PowerGrid e suficiente no runtime
2. se nao for garantido, adicionar dependencia direta de OpenSpout

Objetivo:

- evitar acoplamento tecnico com uma dependencia transitiva
- garantir um writer estavel para exportacao em streaming

### 3. Pasta de saida padrao

Definir estrutura fixa em `storage/app/exports/legacy-migration/{timestamp}/`.

Cada lote deve gerar:

- um `.xlsx` por tabela
- um `manifesto.json`
- opcionalmente um `.zip`

## Estrutura de codigo proposta

```text
app/Services/LegacyMigration/
app/Services/LegacyMigration/Actions/
app/Services/LegacyMigration/Support/
app/Console/Commands/
```

## Componentes a implementar

### 1. `LegacyDatabaseService`

Classe sugerida:

- `App\Services\LegacyMigration\LegacyDatabaseService`

Responsabilidades:

- devolver a conexao `legacy`
- expor helper simples para `table()`
- centralizar verificacoes de tabela e coluna

Metodos sugeridos:

```php
public function connection()
public function table(string $table)
public function hasTable(string $table): bool
public function hasColumns(string $table, array $columns): array
```

Motivo:

- reduz repeticao de `DB::connection('legacy')`
- concentra validacoes estruturais antes da exportacao

### 2. `LegacyXlsxWriterService`

Classe sugerida:

- `App\Services\LegacyMigration\LegacyXlsxWriterService`

Responsabilidades:

- criar arquivos `.xlsx`
- escrever cabecalho
- receber linhas em streaming
- fechar o writer com seguranca
- retornar metadados do arquivo gerado

Metodos sugeridos:

```php
public function write(string $fileName, array $headers, iterable $rows, string $directory): array
```

Cuidados de implementacao:

- trabalhar com `iterable`, nao com array gigante em memoria
- converter booleanos e nulos de forma estavel
- serializar JSON em texto valido quando necessario
- preservar documentos numericos como texto

### 3. `LegacyMigrationExportService`

Classe sugerida:

- `App\Services\LegacyMigration\LegacyMigrationExportService`

Responsabilidades:

- validar parametros de execucao
- montar pasta do lote
- decidir quais tabelas exportar
- executar as actions na ordem correta
- consolidar manifesto
- retornar resumo final

Metodos sugeridos:

```php
public function export(array $filters = []): array
```

Saida esperada:

- diretorio gerado
- arquivos criados
- quantidade de linhas por tabela
- avisos
- erros

### 4. `ExportTableResult`

Primeira versao pode ser um array estruturado, mas o projeto ganha previsibilidade se houver um objeto pequeno para padronizar retorno.

Classe sugerida:

- `App\Services\LegacyMigration\Support\ExportTableResult`

Campos sugeridos:

- `table`
- `file_name`
- `file_path`
- `rows`
- `warnings`
- `errors`
- `skipped`

### 5. `ManifestBuilder`

Classe sugerida:

- `App\Services\LegacyMigration\Support\ManifestBuilder`

Responsabilidades:

- agregar resultado de cada tabela
- registrar filtros aplicados
- registrar divergencias conhecidas
- escrever `manifesto.json`

## Actions por tabela

Implementar uma action por tabela, todas seguindo o mesmo contrato.

Assinatura sugerida:

```php
public function execute(array $filters = []): ExportTableResult
```

Actions previstas:

1. `ExportPartnersAction`
2. `ExportEquipmentsAction`
3. `ExportServicesAction`
4. `ExportInvoicesAction`
5. `ExportServiceOrdersAction`
6. `ExportServiceOrderItemsAction`
7. `ExportAccountsReceivableAction`
8. `ExportOutgoingInvoicesAction`
9. `ExportOutgoingInvoiceServiceOrdersAction`

Padrao interno de cada action:

1. validar tabela e colunas obrigatorias
2. montar query base no legado
3. aplicar filtros
4. ordenar por `id`
5. processar em `chunk`
6. transformar cada linha para o formato XLS
7. delegar escrita ao writer
8. devolver totais e avisos

## Contrato comum entre as actions

Para evitar cada classe inventar um formato diferente, definir uma base simples.

Opcoes validas:

1. interface `LegacyExportActionInterface`
2. abstract class `BaseLegacyExportAction`

Recomendacao:

- usar `abstract class` pequena com helpers de filtro, validacao e serializacao

Helpers uteis nessa classe base:

- `requiredColumns(): array`
- `headers(): array`
- `baseQuery(array $filters)`
- `mapRow(object $record): array`
- `normalizeDate()`
- `normalizeJson()`
- `normalizeDocument()`

## Ponto de entrada da exportacao

### Comando Artisan

Implementar um comando dedicado.

Classe sugerida:

- `App\Console\Commands\ExportLegacyMigrationCommand`

Exemplo de uso:

```bash
php artisan legacy:export-migration --financial --fiscal --include-deleted
```

Opcoes sugeridas:

- `--financial`
- `--fiscal`
- `--include-deleted`
- `--partner-id=*`
- `--service-order-id=*`
- `--date-from=`
- `--date-to=`
- `--disk=`

Motivo:

- desacopla da interface administrativa
- facilita execucao manual, testes e automacao futura

## Estrategia de validacao antes da exportacao

Antes de escrever qualquer arquivo, executar um preflight.

O preflight deve validar:

1. conexao `legacy` acessivel
2. existencia das tabelas do escopo
3. existencia das colunas obrigatorias
4. divergencias conhecidas
5. permissao de escrita no diretorio de saida

Divergencias conhecidas que ja devem entrar no plano:

- `nota_saida_ordem_servico` usa `natureza_op` no banco atual
- existe historico de migration referindo `natureza_operacao`

Se o preflight falhar:

- bloquear exportacao da tabela afetada
- registrar erro no manifesto
- seguir com as demais apenas se a dependencia permitir

## Ordem de implementacao recomendada

### Fase 1. Infraestrutura minima

1. criar conexao `legacy`
2. criar `LegacyDatabaseService`
3. criar `LegacyXlsxWriterService`
4. criar `ManifestBuilder`
5. criar `LegacyMigrationExportService`
6. criar comando Artisan

Resultado esperado:

- projeto ja consegue executar um lote vazio ou de teste com manifesto

### Fase 2. Exportacoes base

1. `ExportPartnersAction`
2. `ExportEquipmentsAction`
3. `ExportServicesAction`

Motivo:

- sao as tabelas mestre e destravam o restante da cadeia

### Fase 3. Exportacoes operacionais

1. `ExportInvoicesAction`
2. `ExportServiceOrdersAction`
3. `ExportServiceOrderItemsAction`

Motivo:

- cobrem o nucleo da migracao operacional

### Fase 4. Exportacoes opcionais

1. `ExportAccountsReceivableAction`
2. `ExportOutgoingInvoicesAction`
3. `ExportOutgoingInvoiceServiceOrdersAction`
4. `.zip` final do lote, se necessario

## Estrategia de filtro

Os filtros devem ser centralizados no orquestrador e repassados para cada action.

Estrutura minima:

```php
[
    'include_deleted' => false,
    'include_financial' => true,
    'include_fiscal' => false,
    'partner_ids' => [],
    'service_order_ids' => [],
    'date_from' => null,
    'date_to' => null,
    'output_disk' => 'local',
]
```

Regras:

- `partner_ids` deve afetar `parceiros`, `equipamentos`, `faturas`, `ordens_servico`, `contas_receber`, `notas_saida`
- `service_order_ids` deve afetar `ordens_servico`, `itens_ordem_servico` e pivot fiscal
- `include_financial` liga `contas_receber`
- `include_fiscal` liga `notas_saida` e `nota_saida_ordem_servico`

## Estrategia de logs

Seguir o estilo atual do projeto com `Log::debug`, `Log::info` e `Log::error`.

Registrar por tabela:

- inicio
- filtros aplicados
- quantidade lida
- quantidade escrita
- tempo total
- warnings
- erros

## Testes necessarios

### Testes unitarios/integracao

1. cria pasta do lote com timestamp
2. escreve cabecalho correto em `parceiros.xlsx`
3. preserva `legacy_parceiro_id` em `equipamentos`
4. preserva `legacy_fatura_id` nulo em `ordens_servico`
5. preserva relacao N:N em `itens_ordem_servico`
6. ignora `deleted_at` por padrao
7. inclui excluidos quando `include_deleted = true`
8. serializa JSON de `notas_saida`
9. registra divergencia de `natureza_op` no manifesto
10. respeita ordem de execucao do lote

### Abordagem recomendada

- usar base de teste controlada
- mockar ou isolar diretorio de escrita
- validar conteudo estrutural dos arquivos gerados, nao layout visual

## Riscos tecnicos e mitigacoes

### Dependencia transitiva do OpenSpout

Risco:

- exportacao depender de pacote que nao esta declarado explicitamente

Mitigacao:

- adicionar dependencia direta se necessario

### Divergencia entre migrations e banco real

Risco:

- nome de coluna ou tabela diferente do previsto no codigo

Mitigacao:

- preflight de colunas obrigatorias
- warnings no manifesto

### Alto volume de dados

Risco:

- estouro de memoria ou timeout

Mitigacao:

- `chunkById()`
- escrita em streaming
- um arquivo por tabela

### Perda de vinculos de migracao

Risco:

- exportar apenas tabelas principais e perder relacoes reais

Mitigacao:

- tornar `itens_ordem_servico` obrigatoria no lote operacional
- tratar tabelas fiscais e financeiras por flag explicita

## Criterios de pronto

Considerar a base tecnica pronta quando:

1. for possivel rodar `php artisan legacy:export-migration`
2. o comando gerar uma pasta de lote com arquivos `.xlsx`
3. cada arquivo tiver cabecalho fixo e IDs antigos preservados
4. existir `manifesto.json` com totais, filtros, warnings e erros
5. a exportacao usar conexao `legacy`
6. o processamento ocorrer em streaming/chunk
7. os testes essenciais estiverem cobrindo o fluxo principal

## Proxima etapa recomendada

Comecar pela infraestrutura e por 3 exports base:

1. `parceiros`
2. `equipamentos`
3. `servicos`

Essas 3 entregas validam a conexao legada, o writer `.xlsx`, o manifesto e o comando Artisan antes de atacar as tabelas mais sensiveis do processo.
