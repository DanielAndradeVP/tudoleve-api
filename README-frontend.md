## Guia de Integração do Frontend com a API

Este documento descreve como um frontend (por exemplo, uma SPA em Vue/React) deve consumir a API da loja online implementada neste projeto (`tudoleve-api`).

Todas as rotas descritas abaixo estão sob o prefixo **`/api/v1`**.

---

## 1. Padrão de respostas e autenticação

### 1.1. Padrão de resposta

Todas as respostas seguem o padrão genérico:

```json
{
  "success": true,
  "data": ...,
  "message": null
}
```

Em casos de erro:

```json
{
  "success": false,
  "data": null,
  "message": "Descrição do erro"
}
```

O código HTTP também é significativo (ex.: 400, 401, 404, 422, 500).

### 1.2. Autenticação (Sanctum)

Após login/registro, a API retorna um **token** de acesso:

- O token deve ser enviado pelo frontend em todas as rotas protegidas com o header:

```http
Authorization: Bearer SEU_TOKEN_AQUI
```

---

## 2. Rotas de Autenticação

### 2.1. Registrar usuário/cliente

**POST** `/api/v1/auth/register`

- **Body (exemplo)**:

```json
{
  "name": "João da Silva",
  "email": "joao@example.com",
  "password": "secret123",
  "phone": "11999999999"
}
```

- **Resposta (simplificada)**:

```json
{
  "success": true,
  "data": {
    "user": { "...": "..." },
    "customer": { "...": "..." },
    "token": "SANCTUM_TOKEN_AQUI"
  },
  "message": null
}
```

O frontend deve:

- Guardar o `token` (ex.: `localStorage` ou cookie HTTP-only).
- Configurar globalmente o header `Authorization: Bearer TOKEN`.

### 2.2. Login

**POST** `/api/v1/auth/login`

- **Body**:

```json
{
  "email": "joao@example.com",
  "password": "secret123"
}
```

- **Resposta**: mesmo formato de `/auth/register` (retorna `user`, `customer`, `token`).

### 2.3. Logout (autenticado)

**POST** `/api/v1/auth/logout`

- Requer header `Authorization`.
- Invalida o token atual.

### 2.4. Refresh de token (autenticado)

**POST** `/api/v1/auth/refresh`

- Requer header `Authorization`.
- Retorna um novo token e invalida o anterior:

```json
{
  "success": true,
  "data": {
    "token": "NOVO_TOKEN"
  },
  "message": null
}
```

---

## 3. Clientes e Endereços (Área Logada)

Todas as rotas abaixo exigem **token válido**.

### 3.1. Listar clientes

**GET** `/api/v1/customers`

- **Query params**:
  - `search` (opcional): busca em `name`, `email`, `phone`.
  - `per_page` (opcional, default: 15).

### 3.2. Detalhar cliente

**GET** `/api/v1/customers/{publicId}`

Retorna:

- Dados do cliente.
- Relacionamentos: `user`, `addresses`.

### 3.3. Criar cliente

**POST** `/api/v1/customers`

- **Body (exemplo)**:

```json
{
  "name": "João",
  "email": "joao@example.com",
  "phone": "11999999999",
  "password": "opcional"
}
```

### 3.4. Atualizar cliente

**PUT/PATCH** `/api/v1/customers/{customer}`

- Atualiza dados básicos do cliente e sincroniza com o `user` vinculado.

### 3.5. Remover cliente

**DELETE** `/api/v1/customers/{customer}`

- Soft delete do cliente e do usuário associado.

### 3.6. Endereços do cliente

Base: `/api/v1/customers/{customer}`

- **GET** `/addresses` – lista endereços.
- **POST** `/addresses` – cria endereço.
- **GET** `/addresses/{publicId}` – detalhe.
- **PUT/PATCH** `/addresses/{publicId}` – atualiza.
- **DELETE** `/addresses/{publicId}` – remove.

**Body para criar/atualizar**:

```json
{
  "label": "Casa",
  "recipient_name": "João",
  "street": "Rua X",
  "number": "123",
  "complement": "Apto 12",
  "district": "Centro",
  "city": "São Paulo",
  "state": "SP",
  "postal_code": "01000-000",
  "country": "BR",
  "is_default": true
}
```

---

## 4. Catálogo (Produtos, Categorias, Marcas)

Rotas abertas, não exigem autenticação.

### 4.1. Listar produtos

**GET** `/api/v1/catalog/products`

- **Query params**:
  - `q` – busca por nome/descrição/sku.
  - `category_id` – filtra por categoria.
  - `brand_id` – filtra por marca.
  - `min_price` / `max_price` – filtro de preço.
  - `sort` – `price_asc`, `price_desc`, `newest` (default).
  - `per_page` – itens por página (default 15).

Usar esta rota para:

- Vitrines, listagens, busca do catálogo.

### 4.2. Detalhar produto

**GET** `/api/v1/catalog/products/{publicId}`

- Retorna dados completos do produto, incluindo:
  - Categoria, marca.
  - Variantes (`product_variants`).
  - Imagens (`product_images`).

### 4.3. Listar categorias

**GET** `/api/v1/catalog/categories`

- **Query params**:
  - `q` (opcional): filtra por nome/slug.
  - `per_page` (opcional, default 50).

### 4.4. Listar marcas

**GET** `/api/v1/catalog/brands`

- Mesmo padrão de `/categories`.

---

## 5. Sessão de Carrinho (Anônimo)

O carrinho pode ser:

- **Autenticado**: vinculado ao `customer_id` do usuário logado.
- **Anônimo**: vinculado a um `session_id` gerado pela API.

### 5.1. Gerar `session_id` para carrinho anônimo

**POST** `/api/v1/cart/session`

- **Resposta**:

```json
{
  "success": true,
  "data": {
    "session_id": "UUID-GERADO-PELA-API"
  },
  "message": null
}
```

### 5.2. Como o frontend deve usar o `session_id`

- No primeiro acesso anônimo:
  - Chamar `POST /api/v1/cart/session`.
  - Salvar o `session_id` (por exemplo, em `localStorage` ou cookie).
- Em todas as requisições de carrinho:
  - Enviar **um** dos seguintes:
    - Header `X-Cart-Session: SEU_SESSION_ID`, ou
    - Campo `session_id` no body nas rotas que possuem body.

Se o usuário estiver logado (com token), o backend usa o `customer_id` do token e o `session_id` passa a ser opcional.

---

## 6. Carrinho

Todas as rotas abaixo exigem **ou**:

- Usuário autenticado (token) **ou**
- `session_id` válido (header ou body).

Caso nenhum dos dois seja enviado, a API retorna **422** com:

```json
{
  "success": false,
  "data": null,
  "message": "Customer or session identifier is required."
}
```

### 6.1. Exibir carrinho atual

**GET** `/api/v1/cart`

- Se não existir carrinho, um novo é criado automaticamente.
- **Resposta (exemplo)**:

```json
{
  "success": true,
  "data": {
    "public_id": "UUID-DO-CARRINHO",
    "items": [
      {
        "id": 1,
        "product": { "...": "..." },
        "variant": { "...": "..." },
        "quantity": 2,
        "unit_price": 99.9,
        "total_price": 199.8
      }
    ],
    "subtotal": 199.8,
    "discount_total": 0,
    "shipping_total": 0,
    "grand_total": 199.8
  },
  "message": null
}
```

### 6.2. Adicionar item ao carrinho

**POST** `/api/v1/cart/items`

- **Headers**:
  - `X-Cart-Session: SEU_SESSION_ID` (se anônimo).

- **Body**:

```json
{
  "product_id": 1,
  "product_variant_id": 10,
  "quantity": 2,
  "session_id": "opcional se header já foi enviado"
}
```

### 6.3. Atualizar quantidade de item

**PUT** `/api/v1/cart/items/{id}`

- **Body**:

```json
{
  "quantity": 3,
  "session_id": "opcional se header já foi enviado"
}
```

### 6.4. Remover item

**DELETE** `/api/v1/cart/items/{id}`

- Usa `X-Cart-Session` ou `session_id` para identificar o carrinho.

### 6.5. Aplicar cupom

**POST** `/api/v1/cart/apply-coupon`

- **Body**:

```json
{
  "coupon_code": "PROMO10",
  "session_id": "opcional se header já foi enviado"
}
```

### 6.6. Remover cupom

**DELETE** `/api/v1/cart/coupon`

### 6.7. Limpar carrinho

**DELETE** `/api/v1/cart`

---

## 7. Logística (Métodos de Envio, Cotação de Frete)

### 7.1. Listar métodos de envio

**GET** `/api/v1/shipping-methods`

- **Resposta (exemplo)**:

```json
[
  {
    "id": 1,
    "name": "PAC",
    "price": 19.9,
    "estimated_days": { "min": 5, "max": 9 },
    "active": true
  },
  {
    "id": 2,
    "name": "SEDEX",
    "price": 29.9,
    "estimated_days": { "min": 1, "max": 3 },
    "active": true
  }
]
```

Uso típico:

- Preencher dropdown de métodos de envio na etapa de checkout.

### 7.2. Cotação de frete

**POST** `/api/v1/logistics/quote`

- **Body (exemplo)**:

```json
{
  "zipcode": "01000-000",
  "items": [
    { "quantity": 2, "weight_kg": 0.5, "volume_m3": 0.01 },
    { "quantity": 1 }
  ],
  "declared_value": 199.9
}
```

- Se `weight_kg` ou `volume_m3` não forem enviados, o backend usa valores padrão com base na quantidade.

- **Resposta (exemplo)**:

```json
{
  "success": true,
  "data": {
    "total": 23.5,
    "currency": "BRL",
    "estimated_delivery_date": "2026-03-17T12:00:00+00:00",
    "breakdown": {
      "base": 10,
      "weight": 8,
      "volume": 5.5
    }
  },
  "message": null
}
```

Uso típico:

- Na tela de produto ou carrinho, após o usuário informar CEP, chamar esta rota para exibir uma prévia de frete.

---

## 8. Checkout

### 8.1. Checkout padrão (carrinho → pedido)

**POST** `/api/v1/checkout`

- **Body (exemplo)**:

```json
{
  "cart_public_id": "UUID-DO-CARRINHO",
  "shipping_address_id": 1,
  "billing_address_id": 1,
  "shipping_method_id": 1,
  "payment_method": "pix",
  "payment_provider": "mercadopago",
  "coupon_code": "PROMO10"
}
```

- `payment_method` ∈ `pix`, `credit_card`, `boleto`.
- `payment_provider` ∈ `local`, `mercadopago`, `stripe`, `pagarme`, `asaas`.

- **Resposta (simplificada)**:

```json
{
  "success": true,
  "data": {
    "order": { "...": "..." },
    "payment": { "...": "..." },
    "shipping": {
      "total": 23.5,
      "currency": "BRL",
      "estimated_delivery_date": "...",
      "breakdown": { "...": "..." },
      "shipping_method": {
        "id": 1,
        "name": "PAC",
        "code": "pac"
      }
    },
    "gateway": {
      "provider": "mercadopago",
      "method": "pix",
      "external_reference": "...",
      "metadata": {
        "qr_code": "0002010102...",
        "expires_at": "..."
      }
    }
  },
  "message": null
}
```

O frontend deve:

- Usar `gateway.metadata` para exibir:
  - PIX: QR Code (`metadata.qr_code`) e validade.
  - Cartão: `client_secret` para SDK do provedor.
  - Boleto: `boleto_url` e data de vencimento.

### 8.2. Checkout rápido (Quick Checkout)

**POST** `/api/v1/checkout/quick`

- Exige usuário autenticado com:
  - `customer` vinculado ao `user`.
  - Pelo menos **um endereço cadastrado**.

- **Body (exemplo)**:

```json
{
  "product_variant_id": 10,
  "quantity": 1,
  "shipping_method_id": 1,
  "payment_method": "pix",
  "payment_provider": "local"
}
```

- Internamente:
  - Cria um carrinho temporário.
  - Adiciona o item.
  - Usa o último endereço atualizado como billing/shipping.
  - Executa o mesmo fluxo de checkout padrão.

---

## 9. Pedidos e Rastreamento

### 9.1. Listar pedidos

**GET** `/api/v1/orders`

- **Query params**:
  - `status` (opcional): `pending`, `processing`, `completed`, `cancelled`.

Retorna pedidos com:

- Itens (`items`).
- Pagamentos (`payments`).
- Remessas (`shipments`).

### 9.2. Detalhe do pedido

**GET** `/api/v1/orders/{publicId}`

- Retorna um pedido por `public_id` com relacionamentos.

### 9.3. Rastreamento de pedido

**GET** `/api/v1/orders/{order}/tracking`

- `{order}` é o `public_id` do pedido.

- **Resposta (exemplo)**:

```json
{
  "success": true,
  "data": {
    "tracking_code": "TRK-AB12CD34",
    "status": "in_transit",
    "history": [
      {
        "status": "created",
        "occurred_at": "2026-03-16T10:00:00+00:00"
      },
      {
        "status": "in_transit",
        "occurred_at": "2026-03-16T22:00:00+00:00"
      }
    ],
    "carrier": "pac"
  },
  "message": null
}
```

Se não houver remessa ou `tracking_code`, a API retorna `tracking_code: null`, `status: null`, `history: []`, `carrier: null`.

Uso típico:

- Tela "Meus pedidos" → botão "Rastrear" chama essa rota.

---

## 10. Pagamentos

### 10.1. Fluxo principal (lado cliente)

O frontend **não** chama diretamente gateways como Mercado Pago, Stripe etc.:

- O backend cria o pagamento dentro do checkout.
- O backend chama o gateway configurado.
- O backend devolve as informações necessárias em `gateway.metadata`.

O frontend deve:

- Exibir as informações de pagamento de acordo com o método/provedor:
  - **PIX**: QR Code + expiração.
  - **Cartão**: `client_secret` para usar com SDK do gateway.
  - **Boleto**: URL do boleto + expiração.

### 10.2. Webhooks de pagamento (servidor → servidor)

**POST** `/api/v1/payments/webhooks/{provider}`

- `{provider}` deve ser **um dos**:
  - `local`
  - `mercadopago`
  - `stripe`
  - `pagarme`
  - `asaas`

- Se o provider for inválido, a API retorna:

```json
{
  "error": "Invalid provider"
}
```

com HTTP 400.

Esta rota é destinada aos provedores de pagamento (backend-backend). O frontend normalmente **não** consome este endpoint.

### 10.3. Ações administrativas (opcionais)

Pensadas para painel admin (não para o cliente final).

- **POST** `/api/v1/payments/{id}/capture`
- **POST** `/api/v1/payments/{id}/cancel`
- **POST** `/api/v1/payments/{id}/refund`

Onde `{id}` é o `public_id` do pagamento.

Cada uma:

- Atualiza o status do pagamento.
- Atualiza o status do pedido.
- Registra uma transação (`transactions`).

---

## 11. Fluxo recomendado do frontend

### 11.1. Visitante anônimo

1. Chamar `POST /api/v1/cart/session` e salvar `session_id`.
2. Listar produtos com `GET /api/v1/catalog/products`.
3. Adicionar itens ao carrinho com `POST /api/v1/cart/items` enviando:
   - Header `X-Cart-Session: session_id`.
4. Exibir carrinho com `GET /api/v1/cart`.
5. Simular frete:
   - `GET /api/v1/shipping-methods` para popular opções.
   - `POST /api/v1/logistics/quote` com CEP + itens agregados.

### 11.2. Usuário logado

1. Registrar ou logar:
   - `POST /api/v1/auth/register` ou `POST /api/v1/auth/login`.
   - Salvar token e configurar `Authorization: Bearer`.
2. Gerenciar endereços:
   - Rotas `/api/v1/customers/{customer}/addresses`.
3. Carrinho:
   - Pode continuar usando o mesmo `session_id` ou deixar o backend criar carrinho vinculado ao `customer`.
4. Checkout:
   - `POST /api/v1/checkout` com:
     - `cart_public_id`
     - `shipping_address_id`, `billing_address_id`
     - `shipping_method_id`
     - `payment_method`, `payment_provider`
5. Exibir dados de pagamento:
   - Usar o campo `gateway` da resposta do checkout.
6. Pós-venda:
   - `GET /api/v1/orders` / `GET /api/v1/orders/{id}`.
   - `GET /api/v1/orders/{id}/tracking` para rastreio.

---

## 12. Considerações finais para o time de frontend

- **Erros de validação**: use o código HTTP (ex.: 422) e o campo `message` para feedback ao usuário.
- **Carrinho anônimo**:
  - Sempre garanta que `session_id` esteja presente antes de chamar rotas de carrinho.
  - Se perder o `session_id` (limpeza de storage), chame `POST /cart/session` novamente.
- **Autenticação**:
  - Após login/registro, sempre substitua o token em memória/storage.
  - Após logout, remova o token e (se fizer sentido) um novo `session_id` pode ser criado para um carrinho anônimo.
- **Integração com gateways**:
  - Por enquanto é tudo simulado via `LocalPaymentGateway` e providers fake.
  - A interface de `gateway.metadata` foi pensada para facilitar migração para integrações reais sem quebrar o frontend.

Em caso de dúvidas sobre algum campo específico retornado pela API, consulte os controllers em `app/Http/Controllers/Api` e os services/domínios em `app/Domain`.

