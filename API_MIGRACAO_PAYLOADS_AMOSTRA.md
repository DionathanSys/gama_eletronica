# API de Migracao - Payloads de Amostra

## Endpoints revisados

Os endpoints de coleta de dados via API estao definidos em `routes/api.php`:

1. `GET /api/migracao/parceiros`
2. `GET /api/migracao/contatos`
3. `GET /api/migracao/enderecos`
4. `GET /api/migracao/equipamentos`
5. `GET /api/migracao/ordens-servico`
6. `GET /api/migracao/servicos`

## Padrao geral das responses

Todos os endpoints seguem o mesmo envelope de resposta:

```json
{
  "data": [],
  "meta": {
    "resource": "nome_do_recurso",
    "count": 0,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {}
  }
}
```

## Autenticacao

A autenticacao pode ser enviada por:

- query string `key`
- header `X-Migration-Key`

Quando a chave for invalida, o retorno e:

```json
{
  "message": "Unauthorized"
}
```

Status HTTP: `401`

## Observacoes da revisao

1. O padrao `data` + `meta` esta consistente entre todos os endpoints.
2. A maior parte dos IDs exportados usa o prefixo `legacy_`.
3. Existem pequenas inconsistencias de nomenclatura:
4. Em `servicos`, o campo `imposto_servico_id` nao usa `legacy_`.
5. Em `ordens-servico`, `nota_entrada_id` e `nota_retorno_id` nao usam `legacy_`.
6. `ordens-servico` e o endpoint mais rico, com `itens` aninhados e dados do `servico` embutidos.

---

## 1. Parceiros

Referencia: `app/Http/Controllers/Api/Migracao/ParceirosMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 101,
      "nome": "Gama Eletronica Ltda",
      "tipo_vinculo": "cliente",
      "tipo_documento": "cnpj",
      "nro_documento": "12345678000199",
      "ativo": true,
      "inscricao_estadual": "123456789",
      "created_at": "2026-06-01T10:00:00Z",
      "updated_at": "2026-06-15T14:30:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "resource": "parceiros",
    "count": 1,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "include_deleted": false
    }
  }
}
```

## 2. Contatos

Referencia: `app/Http/Controllers/Api/Migracao/ContatosMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 55,
      "legacy_parceiro_id": 101,
      "nome_contato": "Maria Souza",
      "email": "maria@gama.com.br",
      "telefone_fixo": "1133334444",
      "telefone_cel": "11999998888",
      "envio_ordem": true,
      "created_at": "2026-06-01T10:10:00Z",
      "updated_at": "2026-06-15T14:35:00Z"
    }
  ],
  "meta": {
    "resource": "contatos",
    "count": 1,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "parceiro_id": 101
    }
  }
}
```

## 3. Enderecos

Referencia: `app/Http/Controllers/Api/Migracao/EnderecosMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 88,
      "legacy_parceiro_id": 101,
      "rua": "Rua das Flores",
      "numero": "123",
      "complemento": "Sala 4",
      "bairro": "Centro",
      "codigo_municipio": "3550308",
      "cidade": "Sao Paulo",
      "estado": "SP",
      "cep": "01001000",
      "pais": "Brasil",
      "created_at": "2026-06-01T10:20:00Z",
      "updated_at": "2026-06-15T14:40:00Z"
    }
  ],
  "meta": {
    "resource": "enderecos",
    "count": 1,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "parceiro_id": 101
    }
  }
}
```

## 4. Equipamentos

Referencia: `app/Http/Controllers/Api/Migracao/EquipamentosMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 203,
      "legacy_parceiro_id": 101,
      "descricao": "Inversor de frequencia",
      "nro_serie": "INV-2026-0001",
      "modelo": "CFW500",
      "marca": "WEG",
      "created_at": "2026-06-01T10:30:00Z",
      "updated_at": "2026-06-15T14:45:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "resource": "equipamentos",
    "count": 1,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "include_deleted": false,
      "parceiro_id": 101
    }
  }
}
```

## 5. Ordens de Servico

Referencia: `app/Http/Controllers/Api/Migracao/OrdensServicoMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 9001,
      "legacy_parceiro_id": 101,
      "legacy_equipamento_id": 203,
      "legacy_fatura_id": 77,
      "placa": "ABC1D23",
      "data_ordem": "2026-06-01",
      "data_encerrado": "2026-06-03",
      "valor_total": 1250.5,
      "desconto": 50,
      "prioridade": "alta",
      "tipo_manutencao": "corretiva",
      "status": "fechada",
      "status_processo": "finalizado",
      "relato_cliente": "Equipamento nao liga",
      "itens_recebidos": "fonte, cabos e painel",
      "path_pdf": "ordens-servico/9001.pdf",
      "img_equipamento": "equipamentos/203.jpg",
      "nota_entrada_id": 12,
      "nota_retorno_id": 19,
      "observacao_geral": "Cliente solicitou urgencia",
      "observacao_interna": "Troca de componente realizada",
      "created_at": "2026-06-01T11:00:00Z",
      "updated_at": "2026-06-15T15:00:00Z",
      "itens": [
        {
          "legacy_id": 501,
          "legacy_ordem_servico_id": 9001,
          "legacy_servico_id": 301,
          "servico": {
            "legacy_id": 301,
            "nome": "Troca de componente",
            "descricao": "Substituicao de capacitor",
            "valor_unitario": 350,
            "ativo": true
          },
          "quantidade": 2,
          "valor_unitario": 350,
          "valor_total": 700,
          "desconto": 0,
          "observacao": "Aplicado em bancada",
          "garantia": true
        }
      ]
    }
  ],
  "meta": {
    "resource": "ordens_servico",
    "count": 1,
    "limit": 200,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "parceiro_id": 101,
      "equipamento_id": 203,
      "fatura_id": 77,
      "status": "fechada"
    }
  }
}
```

## 6. Servicos

Referencia: `app/Http/Controllers/Api/Migracao/ServicosMigrationController.php`

```json
{
  "data": [
    {
      "legacy_id": 301,
      "nome": "Troca de componente",
      "descricao": "Substituicao de capacitor e testes",
      "valor_unitario": 350,
      "ativo": true,
      "imposto_servico_id": 5,
      "created_at": "2026-06-01T09:00:00Z",
      "updated_at": "2026-06-15T13:00:00Z",
      "deleted_at": null
    }
  ],
  "meta": {
    "resource": "servicos",
    "count": 1,
    "limit": 500,
    "has_more": false,
    "next_after_id": null,
    "filters": {
      "after_id": 0,
      "updated_from": null,
      "include_deleted": false,
      "ativo": true
    }
  }
}
```
