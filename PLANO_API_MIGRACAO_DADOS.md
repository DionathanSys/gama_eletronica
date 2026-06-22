# Plano para expor dados de migracao via rotas HTTP

Este plano substitui a estrategia de exportacao por Excel por uma integracao via API, para que o outro sistema consuma os dados diretamente deste sistema.

## Objetivo

Criar rotas seguras e estaveis para o sistema novo consultar os dados necessarios da migracao, preservando IDs antigos, relacoes e filtros de sincronizacao.

## Situacao atual do projeto

Com base no codigo atual:

- o projeto so registra `routes/web.php` em `bootstrap/app.php`
- nao existe `routes/api.php` ativo hoje
- nao ha autenticacao API dedicada configurada
- as rotas existentes sao majoritariamente web/Filament e endpoints tecnicos

Conclusao:

- para viabilizar a integracao com o outro sistema, sera necessario criar uma camada HTTP propria para migracao

## Diretriz principal

Em vez de gerar arquivos, o sistema deve fornecer endpoints JSON para leitura paginada das tabelas de migracao.

Recomendacao para a primeira versao:

- uma rota por recurso
- resposta JSON paginada
- filtros por `updated_at`, `id` e escopo
- autenticacao por API key simples
- endpoints somente leitura

## Recursos que precisam ser expostos

### Obrigatorios

1. `parceiros`
2. `equipamentos`
3. `servicos`
4. `faturas`
5. `ordens_servico`
6. `itens_ordem_servico`

### Opcionais por escopo

1. `contas_receber`
2. `notas_saida`
3. `nota_saida_ordem_servico`

## Estrategia de API

### Base URL sugerida

```text
/api/migracao
```

### Endpoints sugeridos

```text
GET /api/migracao/health
GET /api/migracao/manifesto
GET /api/migracao/parceiros
GET /api/migracao/equipamentos
GET /api/migracao/servicos
GET /api/migracao/faturas
GET /api/migracao/ordens-servico
GET /api/migracao/itens-ordem-servico
GET /api/migracao/contas-receber
GET /api/migracao/notas-saida
GET /api/migracao/nota-saida-ordens-servico
```

## Modelo de resposta

Cada endpoint deve responder com payload consistente.

Exemplo:

```json
{
  "data": [
    {
      "legacy_id": 10,
      "nome": "CLIENTE EXEMPLO"
    }
  ],
  "meta": {
    "resource": "parceiros",
    "count": 1,
    "has_more": true,
    "next_cursor": "eyJsYXN0X2lkIjoxMH0=",
    "filters": {
      "updated_from": "2026-06-01 00:00:00"
    }
  }
}
```

## Estrategia de paginacao

Nao usar paginacao por `offset` como estrategia principal.

Usar preferencialmente:

- cursor pagination, ou
- `chunk` baseado em `id`

Recomendacao objetiva:

- ordenar por `id`
- aceitar `limit`
- aceitar `cursor` ou `after_id`

Exemplo de parametros:

- `limit=1000`
- `after_id=5000`
- `updated_from=2026-06-01 00:00:00`
- `include_deleted=1`

Motivo:

- facilita consumo por lote
- evita degradacao com volume alto
- simplifica retomada em caso de falha no sistema consumidor

## Estrategia de autenticacao

Como o objetivo e integracao sistema a sistema, a primeira versao deve evitar sessao e CSRF.

### Recomendacao

Implementar autenticacao por API key em header.

Exemplo:

```text
Authorization: Bearer <token>
```

ou header dedicado:

```text
X-Migration-Key: <token>
```

### Implementacao sugerida

Criar middleware proprio, por exemplo:

- `App\Http\Middleware\EnsureMigrationApiKey`

Responsabilidade:

- validar token contra valor em `.env`
- negar acesso com `401` quando invalido
- registrar tentativa indevida em log

Variavel sugerida:

- `MIGRATION_API_KEY=`

Motivo:

- implementacao pequena
- adequada para rota temporaria/tecnica de migracao
- nao exige Sanctum agora

## Estrutura de codigo sugerida

```text
routes/api.php
app/Http/Controllers/Api/Migracao/
app/Http/Requests/Api/Migracao/
app/Http/Resources/Migracao/
app/Http/Middleware/
app/Services/MigracaoApi/
app/Services/MigracaoApi/Queries/
```

## Ajustes necessarios no bootstrap

Hoje o projeto nao registra `api.php`.

Sera necessario ajustar `bootstrap/app.php` para incluir:

- `api: __DIR__.'/../routes/api.php'`

Objetivo:

- separar rotas web das rotas tecnicas de integracao
- permitir middleware proprio para API

## Organizacao recomendada por camada

### 1. Rotas

Arquivo:

- `routes/api.php`

Responsabilidade:

- declarar endpoints de migracao
- aplicar middleware de autenticacao
- aplicar prefixo `migracao`

### 2. Controllers

Controladores sugeridos:

- `MigrationHealthController`
- `MigrationManifestController`
- `PartnersMigrationController`
- `EquipmentsMigrationController`
- `ServicesMigrationController`
- `InvoicesMigrationController`
- `ServiceOrdersMigrationController`
- `ServiceOrderItemsMigrationController`
- `AccountsReceivableMigrationController`
- `OutgoingInvoicesMigrationController`
- `OutgoingInvoiceServiceOrdersMigrationController`

Responsabilidade:

- receber parametros
- chamar service/query apropriado
- devolver JSON consistente

### 3. Requests

Criar Form Requests para validar filtros.

Exemplos:

- `MigrationListRequest`
- `MigrationManifestRequest`

Campos a validar:

- `limit`
- `after_id`
- `updated_from`
- `updated_to`
- `partner_ids`
- `service_order_ids`
- `include_deleted`

### 4. Services de consulta

Classes sugeridas:

- `App\Services\MigracaoApi\MigrationDataService`
- queries por recurso em `Queries/`

Exemplos:

- `PartnersMigrationQuery`
- `EquipmentsMigrationQuery`
- `ServiceOrdersMigrationQuery`

Responsabilidades:

- montar query
- aplicar filtros
- ordenar por `id`
- limitar lote
- devolver dados prontos para serializacao

### 5. Resources

Criar API Resources para padronizar os campos de saida.

Exemplos:

- `PartnerMigrationResource`
- `EquipmentMigrationResource`
- `ServiceOrderMigrationResource`

Motivo:

- garante contrato claro para o outro sistema
- evita expor colunas acidentais
- facilita evolucao controlada da API

## Contrato de campos por recurso

Os campos devem seguir a mesma regra do plano de exportacao, com nomes pensados para migracao.

### `parceiros`

Campos minimos:

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

### `equipamentos`

Campos minimos:

- `legacy_id`
- `legacy_parceiro_id`
- `descricao`
- `nro_serie`
- `modelo`
- `marca`
- `created_at`
- `updated_at`
- `deleted_at`

### `servicos`

Campos minimos:

- `legacy_id`
- `nome`
- `descricao`
- `valor_unitario`
- `ativo`
- `imposto_servico_id`
- `created_at`
- `updated_at`
- `deleted_at`

### `faturas`

Campos minimos:

- `legacy_id`
- `legacy_parceiro_id`
- `valor_total`
- `desconto`
- `status`
- `path_pdf`
- `created_at`
- `updated_at`

### `ordens_servico`

Campos minimos:

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

### `itens_ordem_servico`

Campos minimos:

- `legacy_id`
- `legacy_ordem_servico_id`
- `legacy_servico_id`
- `quantidade`
- `valor_unitario`
- `valor_total`
- `desconto`
- `observacao`
- `garantia`

### `contas_receber`

Campos minimos:

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

### `notas_saida`

Campos minimos:

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

### `nota_saida_ordem_servico`

Campos minimos:

- `legacy_id`
- `legacy_ordem_servico_id`
- `legacy_nota_saida_id`
- `natureza_op`

## Endpoint de manifesto

Criar um endpoint tecnico para ajudar o sistema consumidor a entender o contrato.

Exemplo:

```text
GET /api/migracao/manifesto
```

Esse endpoint deve informar:

- versao da API de migracao
- recursos disponiveis
- campos por recurso
- filtros suportados
- limites maximos por lote
- divergencias conhecidas

Motivo:

- ajuda o outro sistema a integrar sem depender de leitura manual de codigo

## Endpoint de health

Criar um endpoint simples:

```text
GET /api/migracao/health
```

Resposta sugerida:

- `status`
- `app_name`
- `timestamp`
- `api_version`

Motivo:

- validar conectividade e autenticacao antes de consumir lotes reais

## Regras de negocio da API

### Leitura somente

- nenhuma rota deve alterar dados
- todos os endpoints devem ser `GET`

### Preservacao de IDs antigos

- sempre expor `legacy_id`
- sempre expor FKs antigas como `legacy_*`

### Soft delete

- por padrao, nao retornar excluidos logicamente
- permitir `include_deleted=1`
- quando incluido, retornar `deleted_at`

### Dados volumosos

- limitar `limit` maximo por request, por exemplo `1000`
- rejeitar valores acima do maximo com `422`

### Campos JSON

- serializar explicitamente como objeto/array JSON ou texto JSON padronizado
- manter consistencia entre todos os endpoints

Recomendacao:

- se o outro sistema for HTTP/JSON nativo, melhor devolver JSON estruturado real, nao string JSON

## Estrategia de sincronizacao

Para migracao inicial e retomadas, suportar dois modos:

### 1. Carga completa

Uso:

- consumir tudo por lotes usando `after_id`

### 2. Carga incremental

Uso:

- consumir alteracoes por `updated_from`

Parametros sugeridos:

- `updated_from`
- `updated_to`

Observacao:

- como algumas tabelas usam soft delete, a sincronizacao incremental deve considerar tambem `deleted_at`

## Logs e auditoria

Registrar em log:

- recurso acessado
- filtros recebidos
- quantidade retornada
- token invalido
- duracao da consulta

Nao registrar em log:

- payload completo com dados sensiveis em volume alto
- token bruto

## Seguranca

Medidas minimas:

1. middleware de API key
2. rotas separadas em `api.php`
3. throttle/rate limit
4. somente `GET`
5. whitelist de campos retornados

Opcional se quiser endurecer mais:

1. restringir IP de origem
2. expirar token por ambiente
3. colocar prefixo de versao, ex. `/api/v1/migracao`

## Ordem de implementacao recomendada

### Fase 1. Fundacao da API

1. ativar `routes/api.php` no `bootstrap/app.php`
2. criar middleware `EnsureMigrationApiKey`
3. criar grupo de rotas `/api/migracao`
4. criar endpoint `health`
5. criar endpoint `manifesto`

### Fase 2. Recursos mestres

1. `parceiros`
2. `equipamentos`
3. `servicos`

### Fase 3. Recursos operacionais

1. `faturas`
2. `ordens_servico`
3. `itens_ordem_servico`

### Fase 4. Recursos opcionais

1. `contas_receber`
2. `notas_saida`
3. `nota_saida_ordem_servico`

## Estrategia de testes

Cobertura minima:

1. bloqueia acesso sem API key
2. permite acesso com API key valida
3. retorna `parceiros` com campos esperados
4. retorna `equipamentos` preservando `legacy_parceiro_id`
5. retorna `ordens_servico` com `legacy_fatura_id` nulo sem erro
6. retorna `itens_ordem_servico` preservando os vinculos
7. ignora soft deleted por padrao
8. inclui soft deleted com flag
9. respeita `limit` maximo
10. suporta `after_id`
11. suporta `updated_from`
12. expõe divergencia conhecida no manifesto

## Criterios de aceite

Considerar a primeira versao pronta quando:

1. o outro sistema conseguir autenticar via API key
2. os dados puderem ser lidos por lotes paginados
3. todas as chaves antigas estiverem presentes no payload
4. a API separar recursos mestres e operacionais
5. a carga completa puder ser retomada via `after_id`
6. a carga incremental puder ser feita via `updated_from`

## Recomendacao pratica final

Para a primeira entrega, manter simples:

1. `api.php`
2. middleware proprio por API key
3. um controller por recurso ou um controller generico com queries separadas
4. JSON paginado por `after_id`
5. `health` + `manifesto`

Evitar nesta fase:

1. acoplar ao Filament
2. usar sessao/autenticacao web
3. expor models diretamente sem resource
4. tentar transformar dados no formato do sistema novo dentro desta API

Primeiro objetivo: disponibilizar leitura segura, previsivel e retomavel dos dados legados para o outro sistema.
