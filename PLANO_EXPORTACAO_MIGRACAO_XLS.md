# Plano Base para Exportacao XLS de Migracao

## Objetivo

Criar uma base clara para a IA implementar `actions` e `services` responsaveis por exportar dados do sistema legado para planilhas Excel, preservando chaves de amarracao e a ordem necessaria para migracao para o sistema atual.

Este plano parte do documento `ESTRUTURA_MIGRACAO_XLS.md` e dos padroes ja existentes neste projeto.

## Premissas tecnicas

- O projeto atual ja possui padrao de exportacao em `.xlsx` com OpenSpout.
- Para implementacao, tratar `xls` como `xlsx`, pois este e o formato real hoje suportado no projeto.
- A exportacao de migracao nao deve depender diretamente de tela Filament.
- A regra principal e gerar arquivos consistentes para importacao posterior, e nao apenas relatorios visuais.
- Sempre manter os IDs antigos e chaves naturais relevantes nas planilhas.

## Resultado esperado

Gerar uma rotina capaz de exportar, no minimo, as seguintes planilhas:

1. `parceiros`
2. `equipamentos`
3. `servicos`
4. `faturas`
5. `ordens_servico`
6. `itens_ordem_servico`

Planilhas opcionais por escopo de migracao:

1. `contas_receber`
2. `notas_saida`
3. `nota_saida_ordem_servico`

## Diretriz de arquitetura

Usar separacao simples entre extracao, montagem de linhas e escrita do arquivo.

### Camadas sugeridas

#### 1. `Action` por entidade exportada

Responsabilidade:

- montar a query no banco legado
- aplicar filtros
- carregar relacoes necessarias
- normalizar cada registro para uma linha pronta para exportacao

Sugestao de classes:

- `App\Services\LegacyMigration\Actions\ExportPartnersAction`
- `App\Services\LegacyMigration\Actions\ExportEquipmentsAction`
- `App\Services\LegacyMigration\Actions\ExportServicesAction`
- `App\Services\LegacyMigration\Actions\ExportInvoicesAction`
- `App\Services\LegacyMigration\Actions\ExportServiceOrdersAction`
- `App\Services\LegacyMigration\Actions\ExportServiceOrderItemsAction`
- `App\Services\LegacyMigration\Actions\ExportAccountsReceivableAction`
- `App\Services\LegacyMigration\Actions\ExportOutgoingInvoicesAction`
- `App\Services\LegacyMigration\Actions\ExportOutgoingInvoiceServiceOrdersAction`

#### 2. Service orquestrador

Responsabilidade:

- definir quais exports serao executados
- respeitar a ordem de execucao
- chamar cada `Action`
- delegar a escrita do arquivo XLSX
- consolidar retorno final da exportacao

Sugestao de classe:

- `App\Services\LegacyMigration\LegacyMigrationExportService`

#### 3. Service de escrita do workbook/arquivo

Responsabilidade:

- receber cabecalhos e linhas
- escrever arquivo `.xlsx`
- salvar em disco temporario ou storage configurado
- retornar caminho, nome do arquivo e totais exportados

Sugestao de classe:

- `App\Services\LegacyMigration\LegacyXlsxWriterService`

#### 4. Service de acesso ao banco legado

Responsabilidade:

- centralizar a conexao legada
- evitar repeticao de `DB::connection(...)`
- permitir troca futura de origem sem reescrever actions

Sugestao de classe:

- `App\Services\LegacyMigration\LegacyDatabaseService`

## Estrutura de pastas sugerida

```text
app/Services/LegacyMigration/
app/Services/LegacyMigration/Actions/
app/Services/LegacyMigration/Support/
```

Se for necessario manter responsabilidades pequenas, os formatadores de linha podem ficar em `Support`.

## Modelo de execucao

Fluxo sugerido:

1. receber parametros da exportacao
2. validar escopo: completo, financeiro, fiscal ou customizado
3. executar actions na ordem correta
4. gerar um arquivo por tabela
5. opcionalmente consolidar tudo em um `.zip`
6. retornar manifesto final com arquivos gerados, totais e erros

## Ordem obrigatoria de exportacao

Seguir esta ordem para preservar referencias cruzadas:

1. `parceiros`
2. `equipamentos`
3. `servicos`
4. `faturas`
5. `ordens_servico`
6. `itens_ordem_servico`
7. `contas_receber` se houver migracao financeira
8. `notas_saida`
9. `nota_saida_ordem_servico`

## Estrategia de arquivos

Preferencia recomendada:

- um arquivo `.xlsx` por tabela
- nome padrao com timestamp
- pasta unica por lote de exportacao

Exemplo:

```text
storage/app/exports/legacy-migration/2026-06-21_14-30-00/
  parceiros.xlsx
  equipamentos.xlsx
  servicos.xlsx
  faturas.xlsx
  ordens_servico.xlsx
  itens_ordem_servico.xlsx
  manifesto.json
```

Motivo:

- simplifica reprocessamento por tabela
- reduz impacto de falha em uma unica etapa
- facilita conferencia manual

## Manifesto de exportacao

Gerar um `manifesto.json` junto dos arquivos com:

- data/hora da exportacao
- conexao usada
- tabelas exportadas
- quantidade de linhas por arquivo
- filtros aplicados
- flags de inclusao de excluidos logicamente
- lista de avisos e inconsistencias

## Parametros que o service principal deve aceitar

Sugestao minima:

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

## Regras por tabela

### 1. `parceiros`

Finalidade:

- tabela raiz de conciliacao comercial

Colunas minimas no XLS:

- `legacy_id`
- `nome`
- `tipo_vinculo`
- `tipo_documento`
- `nro_documento`
- `ativo`
- `inscricao_estadual`
- `created_at`
- `updated_at`
- `deleted_at`

Regras:

- `nro_documento` deve ser sempre exportado como texto
- se houver `deleted_at`, manter a coluna para indicar exclusao logica
- se houver multiplos parceiros com documento inconsistente, registrar aviso no manifesto

### 2. `equipamentos`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_parceiro_id`
- `descricao`
- `nro_serie`
- `modelo`
- `marca`
- `created_at`
- `updated_at`
- `deleted_at`

Regras:

- nao exportar `descricao_nro_serie`, pois e calculada
- preservar `legacy_parceiro_id` obrigatoriamente
- ideal registrar uma coluna derivada opcional `chave_conciliacao_equipamento` com composicao de campos, sem substituir os dados originais

### 3. `servicos`

Colunas minimas no XLS:

- `legacy_id`
- `nome`
- `descricao`
- `valor_unitario`
- `ativo`
- `imposto_servico_id`
- `created_at`
- `updated_at`
- `deleted_at`

Regras:

- exportar `valor_unitario` em formato numerico
- manter `legacy_id` porque o vinculo real com ordem passa por `itens_ordem_servico`

### 4. `faturas`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_parceiro_id`
- `valor_total`
- `desconto`
- `status`
- `path_pdf`
- `created_at`
- `updated_at`

Regras:

- exportar `legacy_parceiro_id`
- manter `status` textual original
- nao excluir registros sem ordens associadas sem regra explicita

### 5. `ordens_servico`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_parceiro_id`
- `legacy_equipamento_id`
- `legacy_fatura_id`
- `placa`
- `data_ordem`
- `data_encerrado`
- `valor_total`
- `desconto`
- `prioridade`
- `tipo_manutencao`
- `status`
- `status_processo`
- `relato_cliente`
- `itens_recebidos`
- `path_pdf`
- `img_equipamento`
- `nota_entrada_id`
- `nota_retorno_id`
- `observacao_geral`
- `observacao_interna`
- `created_at`
- `updated_at`

Regras:

- `legacy_fatura_id` pode ser nulo
- valores monetarios devem sair como numericos
- datas devem manter granularidade suficiente para auditoria
- evitar joins que removam ordens sem equipamento ou sem fatura

### 6. `itens_ordem_servico`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_ordem_servico_id`
- `legacy_servico_id`
- `quantidade`
- `valor_unitario`
- `valor_total`
- `desconto`
- `observacao`
- `garantia`

Regras:

- esta planilha e obrigatoria para preservar o historico real das ordens
- sem ela, a relacao N:N entre ordens e servicos se perde

### 7. `contas_receber`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_parceiro_id`
- `legacy_fatura_id`
- `data_vencimento`
- `valor`
- `desdobramento`
- `desdobramentos`
- `descricao`
- `metodo_pagamento`
- `status`

Regras:

- exportar apenas quando a migracao incluir financeiro
- manter vinculo com `legacy_fatura_id`

### 8. `notas_saida`

Colunas minimas no XLS:

- `legacy_id`
- `status`
- `legacy_parceiro_id`
- `legacy_fatura_id`
- `natureza_operacao`
- `chave_nota`
- `nro_nota`
- `serie`
- `data_emissao`
- `data_entrada_saida`
- `frete_json`
- `notas_referenciadas_json`
- `observacoes_contribuinte_json`
- `eventos_json`

Regras:

- campos JSON devem sair serializados em texto JSON valido
- exportar apenas quando a migracao incluir fiscal

### 9. `nota_saida_ordem_servico`

Colunas minimas no XLS:

- `legacy_id`
- `legacy_ordem_servico_id`
- `legacy_nota_saida_id`
- `natureza_op`

Regras:

- manter o nome real do banco legado se a coluna atual for `natureza_op`
- registrar no manifesto a divergencia historica com `natureza_operacao`

## Padrao de implementacao das actions

Cada action deve seguir este fluxo:

1. receber filtros e destino
2. montar query base no banco legado
3. aplicar ordenacao estavel por `id`
4. processar em `chunk` para evitar uso excessivo de memoria
5. converter cada registro em array simples
6. enviar linhas para o writer service
7. retornar quantidade exportada e avisos

Assinatura sugerida:

```php
public function execute(array $filters = []): ExportTableResult
```

Se o projeto ainda nao tiver DTO especifico, um array estruturado ja resolve na primeira versao.

## Padrao de implementacao do writer service

Responsabilidades minimas:

- criar arquivo `.xlsx`
- escrever cabecalho fixo
- escrever linhas em streaming
- aplicar formato numerico em valores monetarios e quantidades quando fizer sentido
- fechar arquivo com seguranca mesmo em caso de excecao

Assinatura sugerida:

```php
public function write(string $fileName, array $headers, iterable $rows): array
```

Retorno esperado:

```php
[
    'file_name' => 'parceiros.xlsx',
    'file_path' => 'exports/legacy-migration/.../parceiros.xlsx',
    'rows' => 1520,
]
```

## Banco legado

Implementar a leitura do legado de forma explicita.

Sugestao:

- adicionar uma conexao dedicada em `config/database.php`, por exemplo `legacy`
- todas as actions de migracao devem ler dessa conexao
- nao misturar models do sistema atual com queries do legado sem necessidade

Abordagem recomendada para primeira versao:

- usar `DB::connection('legacy')->table(...)`
- evitar Eloquent se a estrutura do legado nao estiver toda modelada

Motivo:

- reduz esforco inicial
- evita side effects de observers, casts e regras do dominio atual

## Tratamento de exclusao logica

Padrao recomendado:

- por padrao, nao exportar registros com `deleted_at`
- permitir `include_deleted = true` quando a migracao exigir historico completo
- quando exportado, manter a coluna `deleted_at`

## Validacoes importantes

Antes de gerar cada arquivo, validar:

- existencia da tabela na conexao legada
- existencia das colunas obrigatorias esperadas
- divergencias conhecidas, como `natureza_op`
- volume estimado de linhas

Se houver ausencia de coluna obrigatoria:

- falhar a exportacao daquela tabela
- registrar erro claro no manifesto
- permitir continuar nas demais tabelas apenas se isso fizer sentido para o lote

## Logs e rastreabilidade

Registrar em log:

- inicio e fim de cada exportacao
- filtros usados
- quantidade de registros processados
- tempo total por tabela
- erros por tabela

O padrao do repositorio ja usa logs detalhados em `Actions` e `Services`; seguir o mesmo estilo.

## Pontos de atencao de negocio

### `parceiros`

- `nro_documento` e a principal chave de conciliacao

### `equipamentos`

- dependem de `parceiros`

### `ordens_servico`

- tabela central da migracao
- pode existir sem `fatura_id`

### `itens_ordem_servico`

- sem esta tabela o historico operacional fica incompleto

### `faturas` e `contas_receber`

- se a migracao incluir financeiro, as duas precisam caminhar juntas

### `notas_saida` e pivot

- so incluir no escopo se o destino realmente for absorver historico fiscal

## Estrategia de testes

Criar testes focados em comportamento, nao em layout visual da planilha.

Cobertura minima sugerida:

1. exporta `parceiros` com cabecalho esperado
2. exporta `equipamentos` preservando `legacy_parceiro_id`
3. exporta `ordens_servico` com `legacy_fatura_id` nulo sem quebrar
4. exporta `itens_ordem_servico` preservando `legacy_ordem_servico_id` e `legacy_servico_id`
5. respeita ordem de execucao do lote completo
6. ignora soft deleted por padrao
7. inclui soft deleted quando `include_deleted = true`
8. serializa campos JSON de `notas_saida`
9. registra divergencia de `natureza_op` no manifesto

## Roadmap sugerido para implementacao

### Fase 1

- criar conexao `legacy`
- criar `LegacyXlsxWriterService`
- criar `LegacyMigrationExportService`
- implementar exportacao de `parceiros`
- implementar exportacao de `equipamentos`
- implementar exportacao de `servicos`

### Fase 2

- implementar exportacao de `faturas`
- implementar exportacao de `ordens_servico`
- implementar exportacao de `itens_ordem_servico`

### Fase 3

- implementar `contas_receber`
- implementar `notas_saida`
- implementar `nota_saida_ordem_servico`
- gerar `.zip` final do lote, se necessario

## Criterios de aceite

Considerar a primeira versao pronta quando:

1. for possivel executar uma exportacao completa sem UI
2. cada tabela gerar seu proprio `.xlsx`
3. todas as chaves antigas estiverem presentes
4. o manifesto registrar totais e inconsistencias
5. o lote respeitar a ordem de dependencia entre tabelas
6. a exportacao funcionar com volume alto via processamento em `chunk`

## Recomendacao final para a IA implementadora

Na primeira entrega, priorizar simplicidade:

- `DB::connection('legacy')->table(...)`
- uma `Action` por tabela
- um `Service` orquestrador
- um `Service` writer para `.xlsx`
- um arquivo por tabela

Evitar nesta fase:

- acoplar a exportacao ao Filament
- criar muitos DTOs sem necessidade
- tentar converter dados para o modelo novo durante a exportacao

Primeiro objetivo: extrair com fidelidade, consistencia e rastreabilidade.
