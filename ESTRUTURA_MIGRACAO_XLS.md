# Estrutura do banco para migracao via XLS

Este documento foi montado com base na estrutura atual do banco validada no ambiente e cruzada com as migrations/models do projeto.

Objetivo: identificar as tabelas principais, suas chaves e os relacionamentos necessarios para exportar os dados para um novo sistema com estrutura diferente.

## Tabelas analisadas

- `parceiros`
- `equipamentos`
- `ordens_servico`
- `servicos`
- `faturas`

## Visao geral das relacoes

- `parceiros` 1:N `equipamentos`
- `parceiros` 1:N `ordens_servico`
- `parceiros` 1:N `faturas`
- `equipamentos` 1:N `ordens_servico`
- `faturas` 1:N `ordens_servico`
- `ordens_servico` N:N `servicos` via `itens_ordem_servico`

## 1. Tabela `parceiros`

Representa a entidade principal de cadastro de cliente/fornecedor/outro vinculo comercial.

### Colunas

- `id` PK
- `nome`
- `tipo_vinculo`
- `tipo_documento`
- `nro_documento` UNIQUE
- `ativo`
- `inscricao_estadual` nullable
- `created_by` FK `users.id`
- `updated_by` FK `users.id`
- `created_at`
- `updated_at`
- `deleted_at` soft delete

### Relacoes

- 1:N com `equipamentos` por `equipamentos.parceiro_id`
- 1:N com `ordens_servico` por `ordens_servico.parceiro_id`
- 1:N com `faturas` por `faturas.parceiro_id`
- 1:N com `contas_receber` por `contas_receber.parceiro_id`
- 1:N com `enderecos` por `enderecos.parceiro_id`
- 1:N com `contatos` por `contatos.parceiro_id`

### Observacoes para migracao

- `nro_documento` e a chave natural mais forte para conciliacao entre sistemas.
- `tipo_vinculo` diferencia o papel do parceiro no processo. Nas ordens de servico ele funciona como cliente.
- Registros com `deleted_at` preenchido estao excluidos logicamente.

## 2. Tabela `equipamentos`

Armazena os equipamentos vinculados a um parceiro.

### Colunas

- `id` PK
- `parceiro_id` FK `parceiros.id`
- `descricao` nullable
- `nro_serie` nullable
- `descricao_nro_serie` coluna virtual gerada a partir de `descricao + nro_serie`
- `modelo` nullable
- `marca` nullable
- `created_by` FK `users.id`
- `updated_by` FK `users.id`
- `created_at`
- `updated_at`
- `deleted_at` soft delete

### Relacoes

- N:1 com `parceiros` por `parceiro_id`
- 1:N com `ordens_servico` por `ordens_servico.equipamento_id`

### Observacoes para migracao

- `descricao_nro_serie` nao precisa ser exportada como dado mestre, porque e calculada.
- Se o novo sistema exigir identificador unico de equipamento, provavelmente sera necessario combinar `parceiro_id`, `descricao`, `marca`, `modelo` e `nro_serie`.
- Equipamentos dependem da migracao previa de `parceiros`.

## 3. Tabela `ordens_servico`

E a tabela central do processo operacional. Liga cliente, equipamento, faturamento e os servicos executados.

### Colunas

- `id` PK
- `parceiro_id` FK `parceiros.id`
- `equipamento_id` FK `equipamentos.id`
- `placa` nullable
- `fatura_id` FK nullable `faturas.id`
- `data_ordem`
- `data_encerrado` nullable
- `valor_total`
- `desconto`
- `prioridade`
- `tipo_manutencao` nullable
- `status`
- `status_processo` nullable
- `relato_cliente` nullable
- `itens_recebidos` nullable
- `path_pdf` nullable
- `img_equipamento` nullable
- `nota_entrada_id` FK nullable `notas_entrada.id`
- `nota_retorno_id` FK nullable `notas_entrada.id`
- `observacao_geral` nullable
- `observacao_interna` nullable
- `created_by` FK `users.id`
- `updated_by` FK `users.id`
- `created_at`
- `updated_at`

### Relacoes diretas

- N:1 com `parceiros` por `parceiro_id`
- N:1 com `equipamentos` por `equipamento_id`
- N:1 com `faturas` por `fatura_id`
- N:1 com `notas_entrada` por `nota_entrada_id`
- N:1 com `notas_entrada` por `nota_retorno_id`

### Relacoes funcionais

- 1:N com `itens_ordem_servico`
- N:N com `servicos` via `itens_ordem_servico`
- N:N com `notas_saida` via `nota_saida_ordem_servico`
- 1:N com `itens_nota_remessa`

### Como a relacao com servicos acontece

`ordens_servico` nao possui `servico_id` direto.

O vinculo e feito pela tabela intermediaria `itens_ordem_servico`, com esta estrutura relevante:

- `id`
- `ordem_servico_id` FK `ordens_servico.id`
- `servico_id` FK `servicos.id`
- `quantidade`
- `valor_unitario`
- `valor_total`
- `desconto`
- `observacao`
- `garantia` nullable

Isso significa que uma ordem pode ter varios servicos, e um mesmo servico pode aparecer em varias ordens.

### Observacoes para migracao

- `fatura_id` pode ser nulo, entao existem ordens ainda nao faturadas.
- `valor_total` e `desconto` da ordem parecem ser consolidados a partir dos itens/servicos executados.
- Para manter historico fiel, e importante exportar tambem `itens_ordem_servico`.
- `nota_entrada_id` e `nota_retorno_id` sao relacionamentos adicionais do processo fiscal/logistico e podem ser relevantes se o novo sistema tambem controlar remessa/retorno.

## 4. Tabela `servicos`

Cadastro mestre dos tipos de servico que podem ser usados nas ordens.

### Colunas

- `id` PK
- `nome`
- `descricao` nullable
- `valor_unitario`
- `ativo`
- `imposto_servico_id` FK nullable `imposto_servicos.id`
- `created_by` FK `users.id`
- `updated_by` FK `users.id`
- `created_at`
- `updated_at`
- `deleted_at` soft delete

### Relacoes

- 1:N com `itens_ordem_servico` por `itens_ordem_servico.servico_id`
- N:N com `ordens_servico` via `itens_ordem_servico`
- N:1 com `imposto_servicos` por `imposto_servico_id`

### Observacoes para migracao

- `servicos` e tabela de catalogo. Sozinha ela nao informa em quais ordens foi usada; isso depende de `itens_ordem_servico`.
- Se o novo sistema tiver outro cadastro de servicos, sera necessario mapear esse `id` antigo para o novo cadastro.

## 5. Tabela `faturas`

Agrupa ordens de servico faturadas para um mesmo parceiro.

### Colunas

- `id` PK
- `parceiro_id` FK `parceiros.id`
- `valor_total`
- `desconto`
- `status`
- `path_pdf` nullable
- `created_by` FK `users.id`
- `updated_by` FK `users.id`
- `created_at`
- `updated_at`

### Relacoes

- N:1 com `parceiros` por `parceiro_id`
- 1:N com `ordens_servico` por `ordens_servico.fatura_id`
- 1:N com `contas_receber` por `contas_receber.fatura_id`
- 1:N com `notas_saida` por `notas_saida.fatura_id`

### Observacoes para migracao

- Uma `fatura` consolida varias `ordens_servico`, desde que sejam do mesmo parceiro.
- O proprio sistema valida isso na criacao da fatura.
- `contas_receber` depende de `fatura`, entao se o novo sistema possuir financeiro, esta tabela tambem deve ser considerada.

## Tabelas auxiliares criticas para a migracao

Mesmo que o foco principal sejam as 5 tabelas acima, a exportacao por XLS nao deve ignorar estas tabelas auxiliares:

### `itens_ordem_servico`

Necessaria para reconstruir quais servicos existem em cada ordem.

### `contas_receber`

Necessaria se o novo sistema controlar financeiro por titulo/parcela.

Campos principais:

- `id`
- `parceiro_id`
- `fatura_id`
- `data_vencimento`
- `valor`
- `desdobramento`
- `desdobramentos`
- `descricao`
- `metodo_pagamento`
- `status`

### `nota_saida_ordem_servico`

Necessaria se o novo sistema precisar manter a ligacao entre ordens e notas de saida.

Campos principais:

- `id`
- `ordem_servico_id`
- `nota_saida_id`
- `natureza_op`

Observacao: no banco atual a coluna existente e `natureza_op`. Em uma migration do projeto aparece `natureza_operacao`, entao ha divergencia entre codigo historico e estrutura atual do banco.

### `notas_saida`

Relaciona `parceiros`, `faturas` e ordens via tabela pivot `nota_saida_ordem_servico`.

Campos principais atuais no banco:

- `id`
- `status`
- `parceiro_id`
- `fatura_id`
- `natureza_operacao`
- `chave_nota`
- `nro_nota`
- `serie`
- `data_emissao`
- `data_entrada_saida`
- `frete` JSON nullable
- `notas_referenciadas` JSON nullable
- `observacoes_contribuinte` JSON nullable
- `eventos` JSON nullable

## Ordem sugerida de exportacao

Para evitar perda de referencia entre planilhas XLS, a ordem recomendada e:

1. `parceiros`
2. `equipamentos`
3. `servicos`
4. `faturas`
5. `ordens_servico`
6. `itens_ordem_servico`
7. `contas_receber` se houver migracao financeira
8. `notas_saida` e `nota_saida_ordem_servico` se houver migracao fiscal

## Chaves de amarracao recomendadas no XLS

- `parceiros`: manter `id` antigo e `nro_documento`
- `equipamentos`: manter `id` antigo, `parceiro_id` antigo e `nro_serie`
- `servicos`: manter `id` antigo e `nome`
- `faturas`: manter `id` antigo e `parceiro_id` antigo
- `ordens_servico`: manter `id` antigo, `parceiro_id` antigo, `equipamento_id` antigo e `fatura_id` antigo
- `itens_ordem_servico`: manter `id` antigo, `ordem_servico_id` antigo e `servico_id` antigo

## Resumo pratico

- `parceiros` e a raiz comercial do processo.
- `equipamentos` pertencem a `parceiros`.
- `ordens_servico` ligam `parceiros` e `equipamentos`.
- `servicos` entram nas ordens por meio de `itens_ordem_servico`.
- `faturas` agrupam varias `ordens_servico` de um mesmo `parceiro`.
- Para uma migracao correta, exportar apenas as 5 tabelas principais nao basta se voce quiser preservar os itens de servico e os vinculos de faturamento/fiscal.
