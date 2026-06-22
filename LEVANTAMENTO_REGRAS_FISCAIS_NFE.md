# Levantamento de regras/configuracoes fiscais para emissao de NF-e

## Visao geral

O projeto centraliza as configuracoes fiscais principais em `config/nfe.php` e usa esses valores durante a montagem do payload de emissao em DTOs e actions relacionados a `NotaSaida`.

Os pontos principais encontrados foram:

- selecao de ambiente e token do emissor
- serie por tipo de nota
- CFOP por operacao e por destino intraestadual/interestadual
- CST/CSOSN simplificados de ICMS, PIS e COFINS
- defaults operacionais de finalidade, tipo de operacao, consumidor final, presenca do comprador, frete e pagamento
- controle de numeracao por serie

## Fonte principal de configuracao

Arquivo: `config/nfe.php`

### Ambiente e autenticacao

| Regra | Valor encontrado | Onde e usada |
|---|---|---|
| Ambiente do emissor | `env('AMBIENTE_NFE')` | `config/nfe.php:7`, consumido em `app/Services/NfeService.php:33`, `routes/web.php:46` |
| Token em producao | `env('TOKEN_NFE_PRODUCAO')` quando `AMBIENTE_NFE == '1'` | `config/nfe.php:6`, repassado ao SDK em `app/Services/NfeService.php:33` |
| Token em homologacao | `env('TOKEN_NFE_HOMOLOGACAO')` quando ambiente diferente de `1` | `config/nfe.php:6`, repassado ao SDK em `app/Services/NfeService.php:33` |
| Confirmacao extra ao emitir em producao | `requiresConfirmation(fn() => env('AMBIENTE_NFE') == '1')` | `app/Filament/Resources/NotaSaidaResource/Pages/EditNotaSaida.php:51` |
| Opcoes HTTP do emissor | `debug=false`, `timeout=60`, `port=443`, `http_version=CURL_HTTP_VERSION_NONE` | `config/nfe.php:8-13`, usadas no `new Nfe(config('nfe.params'))` em `app/Services/NfeService.php:33` e `routes/web.php:46` |

### Serie por tipo de NF-e

| Tipo | Producao (`AMBIENTE_NFE == '1'`) | Homologacao | Onde e usada |
|---|---:|---:|---|
| `nfe_retorno` | `5` | `850` | `config/nfe.php:16`, consumido em `app/DTO/Fiscal/NfeRetornoDTO.php:41` |
| `nfe_estorno` | `849` | `851` | `config/nfe.php:17`, consumido em `app/DTO/Fiscal/NfeEstornoDTO.php:42` |
| `nfe_remessa` | `700` | `852` | `config/nfe.php:18`, consumido em `app/DTO/Fiscal/NfeRemessaDTO.php:38` |
| `nfe_retorno_demo` | `701` | `853` | `config/nfe.php:19`, consumido apenas em `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:39` |

### CFOP por operacao e UF do destinatario

Regra de selecao:

- se `parceiro->endereco->estado == 'SC'`, considera `intraestadual`
- caso contrario, considera `interestadual`

Implementacao: `app/Traits/DefineCfop.php:11-15`

| Operacao | Intraestadual | Interestadual | Onde e usada |
|---|---:|---:|---|
| `nfe_retorno` | `5916` | `6916` | `config/nfe.php:36,42`; `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:58` |
| `nfe_estorno` | `1949` | `2949` | `config/nfe.php:37,43` |
| `nfe_remessa` | `5915` | `6915` | `config/nfe.php:38,44`; `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:159-163` |
| `nfe_retorno_demo` | `5913` | `6913` | `config/nfe.php:39,45`; `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:160-163` |

Persistencia do CFOP no item:

- campo `cfop` em `itens_nota_saida`: `database/migrations/2025_02_28_071554_create_item_nota_saidas_table.php:24`
- exibido no cadastro/consulta de itens: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:137`

### Situacao tributaria de impostos

| Imposto | Valor encontrado | Onde e usada |
|---|---|---|
| ICMS para `nfe_remessa` | `400` | `config/nfe.php:50`; aplicado em `app/DTO/Fiscal/NfeRemessaDTO.php:84` |
| ICMS para `nfe_retorno` | `900` | `config/nfe.php:51`; aplicado em `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:60` |
| PIS | `08` | `config/nfe.php:55`; aplicado em `app/Traits/DefineImposto.php:19`, `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:61`, DTOs de emissao |
| COFINS | `08` | `config/nfe.php:58`; aplicado em `app/Traits/DefineImposto.php:22`, `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:62`, DTOs de emissao |

Observacao importante sobre persistencia intermediaria:

- `app/Traits/DefineImposto.php:12-24` grava no item um default com `icms.situacao_tributaria = config('nfe.icms.situacao_tributaria')`, ou seja, um array com chaves `nfe_remessa` e `nfe_retorno`
- em `NfeRemessaDTO`, o valor efetivo usado no payload e `['nfe_remessa']` (`app/DTO/Fiscal/NfeRemessaDTO.php:84`)
- em `RegistrarNfeRetornoAction`, o retorno ja grava o valor final `900` diretamente no item (`app/Actions/Fiscal/RegistrarNfeRetornoAction.php:60`)

### Origem da mercadoria

Arquivo: `config/nfe.php:21-32`

Mapa encontrado:

| Codigo | Descricao |
|---:|---|
| `0` | Nacional |
| `1` | Estrangeira - Importacao direta |
| `2` | Estrangeira - Adquirida no mercado interno |
| `3` | Nacional com mais de 40% de conteudo estrangeiro |
| `4` | Nacional produzida atraves de processos produtivos basicos |
| `5` | Nacional com menos de 40% de conteudo estrangeiro |
| `6` | Estrangeira - Importacao direta, sem produto nacional similar |
| `7` | Estrangeira - Adquirida no mercado interno, sem produto nacional similar |
| `8` | Nacional, mercadoria ou bem com Conteudo de Importacao superior a 70% |

Uso efetivo encontrado no payload atual:

- os DTOs de emissao enviam `origem => 0` fixo nos itens, sem consultar esse mapa: `app/DTO/Fiscal/NfeRemessaDTO.php:74`, `app/DTO/Fiscal/NfeRetornoDTO.php:83`, `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:80`, `app/DTO/Fiscal/NfeEstornoDTO.php:69`

## Regras fiscais e operacionais por tipo de nota

### 1. Remessa

Arquivo principal: `app/DTO/Fiscal/NfeRemessaDTO.php`

| Regra | Valor encontrado | Onde e usada |
|---|---|---|
| Tipo operacao | `1` (saida) | `app/DTO/Fiscal/NfeRemessaDTO.php:37` |
| Serie | `config('nfe.serie.nfe_remessa')` | `app/DTO/Fiscal/NfeRemessaDTO.php:38` |
| Finalidade emissao | `1` (nota normal) | `app/DTO/Fiscal/NfeRemessaDTO.php:40` |
| Consumidor final | `0` | `app/DTO/Fiscal/NfeRemessaDTO.php:41` |
| Presenca comprador | `0` | `app/DTO/Fiscal/NfeRemessaDTO.php:42` |
| Frete sem dados | `modalidade_frete = 9` | `app/DTO/Fiscal/NfeRemessaDTO.php:60-62` |
| Meio de pagamento | `90` | `app/DTO/Fiscal/NfeRemessaDTO.php:64` |
| Valor do pagamento | `0` | `app/DTO/Fiscal/NfeRemessaDTO.php:64` |
| ICMS item | `400` via `impostos['icms']['situacao_tributaria']['nfe_remessa']` | `app/DTO/Fiscal/NfeRemessaDTO.php:84` |

Cadastro de itens de remessa:

- permitido para natureza `REMESSA_CONSIGNACAO`: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:151`
- CFOP calculado como `nfe_remessa`: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:159,163`
- unidade forcada para `UN`: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:165`
- impostos default aplicados no item: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:166`

### 2. Retorno de mercadoria

Arquivos principais: `app/Actions/Fiscal/RegistrarNfeRetornoAction.php` e `app/DTO/Fiscal/NfeRetornoDTO.php`

| Regra | Valor encontrado | Onde e usada |
|---|---|---|
| Natureza operacao | `RETORNO DE MERCADORIA` | `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:28`, `app/DTO/Fiscal/NfeRetornoDTO.php:39` |
| Tipo operacao | `1` | `app/DTO/Fiscal/NfeRetornoDTO.php:40` |
| Serie | `config('nfe.serie.nfe_retorno')` | `app/DTO/Fiscal/NfeRetornoDTO.php:41` |
| Finalidade emissao | `1` | `app/DTO/Fiscal/NfeRetornoDTO.php:43` |
| Consumidor final | `1` | `app/DTO/Fiscal/NfeRetornoDTO.php:44` |
| Presenca comprador | `0` | `app/DTO/Fiscal/NfeRetornoDTO.php:45` |
| Informacao adicional contribuinte | `Retorno de mercadoria ref. nota(s) ...` | `app/DTO/Fiscal/NfeRetornoDTO.php:48` |
| Frete sem dados | `modalidade_frete = 9` | `app/DTO/Fiscal/NfeRetornoDTO.php:69-71` |
| Meio de pagamento | `90` | `app/DTO/Fiscal/NfeRetornoDTO.php:73` |
| Valor do pagamento | `0` | `app/DTO/Fiscal/NfeRetornoDTO.php:73` |
| CFOP item | `5916` ou `6916` conforme UF | `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:58` |
| ICMS item | `900` | `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:60` |
| PIS item | `08` | `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:61` |
| COFINS item | `08` | `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:62` |

Formacao das notas referenciadas:

- a `NotaSaida` guarda `notas_referenciadas` como array: `app/Models/NotaSaida.php:17-24`
- no registro do retorno, as chaves sao montadas a partir das notas de entrada vinculadas: `app/Actions/Fiscal/RegistrarNfeRetornoAction.php:70-84`
- no DTO, cada chave vira `notas_referenciadas[].nfe.chave`: `app/DTO/Fiscal/NfeRetornoDTO.php:52-54`

### 3. Retorno de mercadoria para demonstracao

Arquivos encontrados:

- DTO especifico: `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php`
- cadastro de item usando CFOP `nfe_retorno_demo`: `app/Filament/Resources/NotaSaidaResource/RelationManagers/ItensRelationManager.php:160,163`

Valores previstos no DTO especifico:

| Regra | Valor encontrado | Onde esta |
|---|---|---|
| Serie prevista | `config('nfe.serie.nfe_retorno_demo')` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:39` |
| Finalidade emissao | `1` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:41` |
| Consumidor final | `0` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:42` |
| Presenca comprador | `0` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:43` |
| Frete sem dados | `modalidade_frete = 9` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:66-68` |
| Meio de pagamento | `90` | `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:70` |

Uso efetivo atual no sistema:

- a enum `NaturezaOperacaoEnum` mapeia `RETORNO_MERCADORIA_DEMO` para `NfeRemessaDTO`, nao para `NfeRetornoDemonstracaoDTO`: `app/Enums/NaturezaOperacaoEnum.php:57-59`
- por isso, na emissao atual, essa natureza usa a serie de remessa (`nfe_remessa`) e a regra de ICMS de remessa (`400`), mesmo com o item sendo salvo com CFOP de retorno demonstracao

### 4. Estorno de NF-e nao cancelada no prazo legal

Arquivo principal: `app/DTO/Fiscal/NfeEstornoDTO.php`

| Regra | Valor encontrado | Onde e usada |
|---|---|---|
| Natureza operacao | `ESTORNO NFE NAO CANCELADA NO PRAZO LEGAL` | `app/DTO/Fiscal/NfeEstornoDTO.php:40` |
| Tipo operacao | `0` (entrada) | `app/DTO/Fiscal/NfeEstornoDTO.php:41` |
| Serie | `config('nfe.serie.nfe_estorno')` | `app/DTO/Fiscal/NfeEstornoDTO.php:42` |
| Finalidade emissao | `3` (NFe de ajuste) | `app/DTO/Fiscal/NfeEstornoDTO.php:44` |
| Consumidor final | `0` | `app/DTO/Fiscal/NfeEstornoDTO.php:45` |
| Presenca comprador | `0` | `app/DTO/Fiscal/NfeEstornoDTO.php:46` |
| Info adicional contribuinte | `NFe 42250245790457000185550050000000321821022581 Nro. 32 Serie 5` | `app/DTO/Fiscal/NfeEstornoDTO.php:49` |
| Info adicional fisco | `NFe estornada devido valor/quantidade incorretos...` | `app/DTO/Fiscal/NfeEstornoDTO.php:50` |
| Nota referenciada fixa | `42250245790457000185550050000000321821022581` | `app/DTO/Fiscal/NfeEstornoDTO.php:53` |
| Frete | `modalidade_frete = 9` | `app/DTO/Fiscal/NfeEstornoDTO.php:55-57` |
| Meio de pagamento | `90` | `app/DTO/Fiscal/NfeEstornoDTO.php:59` |
| CFOP item | `1949` fixo | `app/DTO/Fiscal/NfeEstornoDTO.php:72` |
| ICMS item | `900` fixo | `app/DTO/Fiscal/NfeEstornoDTO.php:79` |
| PIS item | `08` fixo | `app/DTO/Fiscal/NfeEstornoDTO.php:80` |
| COFINS item | `08` fixo | `app/DTO/Fiscal/NfeEstornoDTO.php:81` |

Observacao:

- neste DTO ha regras fiscais hardcoded, inclusive destinatario fixo (`Parceiro::find(46)`) e chave de nota fixa: `app/DTO/Fiscal/NfeEstornoDTO.php:51-53`

## Regras de frete e transporte

Arquivo de cadastro: `app/Filament/Resources/NotaSaidaResource.php`

| Regra | Valor encontrado | Onde e usada |
|---|---|---|
| Modalidade 0 | `por conta do emitente` | `app/Filament/Resources/NotaSaidaResource.php:300` |
| Modalidade 1 | `por conta do destinatario` | `app/Filament/Resources/NotaSaidaResource.php:301` |
| Modalidade 2 | `por conta de terceiros` | `app/Filament/Resources/NotaSaidaResource.php:302` |
| Modalidade 3 | `Transporte Proprio por conta do Remetente` | `app/Filament/Resources/NotaSaidaResource.php:303` |
| Modalidade 4 | `Transporte Proprio por conta do Destinatario` | `app/Filament/Resources/NotaSaidaResource.php:304` |
| Modalidade default no formulario | `1` | `app/Filament/Resources/NotaSaidaResource.php:307` |
| Modalidade automatica quando sem frete | `9` | DTOs em `app/DTO/Fiscal/NfeRemessaDTO.php:60-62`, `app/DTO/Fiscal/NfeRetornoDTO.php:69-71`, `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:66-68`, `app/DTO/Fiscal/NfeEstornoDTO.php:55-57` |
| Especie default do volume | `CAIXA` | `app/Filament/Resources/NotaSaidaResource.php:368` |
| Quantidade default do volume | `1` | `app/Filament/Resources/NotaSaidaResource.php:314` |
| Peso liquido default | `1` | `app/Filament/Resources/NotaSaidaResource.php:323` |
| Peso bruto default | `1` | `app/Filament/Resources/NotaSaidaResource.php:332` |

## Regras de pagamento

Regra comum encontrada nos DTOs:

- `formas_pagamento[] = ['meio_pagamento' => 90, 'valor' => 0]`

Onde aparece:

- `app/DTO/Fiscal/NfeRemessaDTO.php:64`
- `app/DTO/Fiscal/NfeRetornoDTO.php:73`
- `app/DTO/Fiscal/NfeRetornoDemonstracaoDTO.php:70`
- `app/DTO/Fiscal/NfeEstornoDTO.php:59`

## Controle de numeracao fiscal

Arquivos:

- `app/Traits/ControleNumeracaoNf.php`
- `app/Models/NumeroNotaSaida.php`

Regras:

| Regra | Valor/comportamento | Onde e usada |
|---|---|---|
| Proximo numero da serie | `max(nro_nota) + 1`, ou `1` se nao existir | `app/Traits/ControleNumeracaoNf.php:10-17` |
| Persistencia da ultima numeracao usada | cria registro em `NumeroNotaSaida` com `nro_nota` e `serie_nota` | `app/Traits/ControleNumeracaoNf.php:19-24` |
| Uso na emissao | numero gerado no DTO e salvo apos `cria()` com sucesso | DTOs de fiscal e `app/Services/NfeService.php:62` |

## Onde a emissao acontece

| Fluxo | Onde |
|---|---|
| Montagem do payload por natureza da operacao | `app/Services/NfeService.php:43-47`, `app/Enums/NaturezaOperacaoEnum.php:52-59` |
| Envio para o emissor | `app/Services/NfeService.php:47`, `app/Services/NfeService.php:83`, `app/Services/NfeService.php:112`, `app/Services/NfeService.php:140`, `app/Services/NfeService.php:161`, `app/Services/NfeService.php:223` |
| Preview da nota | `routes/web.php:19-42` |
| Geracao do PDF/DANFE | `routes/web.php:44-76` |
| Cancelamento | `app/Services/NfeService.php:143-218`, acao disparada por `app/Filament/Resources/NotaSaidaResource/Pages/EditNotaSaida.php:67-126` |
| Consulta de status | `app/Services/NfeService.php:101-136`, botao em `app/Filament/Resources/NotaSaidaResource/Pages/EditNotaSaida.php:127-132` |
| Webhook de retorno do emissor | `routes/web.php:220-250` |

## Resumo executivo

As regras fiscais atualmente encontradas no projeto estao concentradas principalmente nestes pontos:

- `config/nfe.php`: ambiente, token, series, CFOP e CST/CSOSN simplificados
- `app/Traits/DefineCfop.php`: define se o CFOP e intraestadual ou interestadual com base no estado do parceiro
- `app/Traits/DefineImposto.php`: aplica defaults de ICMS/PIS/COFINS nos itens criados manualmente
- `app/Actions/Fiscal/RegistrarNfeRetornoAction.php`: grava CFOP e impostos efetivos para notas de retorno
- `app/DTO/Fiscal/*DTO.php`: convertem a `NotaSaida` em payload final de emissao, com varias regras adicionais de finalidade, consumidor final, frete e pagamento

Pontos de atencao encontrados durante a investigacao:

- existe um DTO especifico para `nfe_retorno_demo`, mas o mapeamento efetivo da enum usa `NfeRemessaDTO`
- o DTO de estorno contem varios valores hardcoded que parecem servir a um caso especifico
- o mapa `item.origem` existe em configuracao, mas o payload atual envia `origem = 0` fixo para todos os itens
