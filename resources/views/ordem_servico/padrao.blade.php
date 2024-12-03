<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/resources/css/app.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Ordem {{$ordem_servico->id}}</title>
    <script>
      // Função para abrir a caixa de diálogo de impressão assim que a página carregar
      window.onload = function() {
          window.print();
      };
  </script>
</head>
<body class="">
  <div class="p-4 mt-10 mx-8">

    <!-- Seção Superior -->
    <div class="flex flex-row items-start border p-4 border-black">

      <!-- Logo e Informações da Empresa -->
      <div class="basis-1/6 mt-2">
        <img src="{{ asset('storage/logo.png') }}" alt="Logo" class="h-18 w-h-18 px-3">
      </div>
      <div class="basis-2/6 mt-2 ms-3 text-xs">
        <p class="font-semibold">Gamma Eletrônica LTDA</p>
        <p class="font-thin">Rua Maravilha, 679 E APT 102</p>
        <p class="font-thin">Efapi 89809-540 - Chapecó - SC</p>
        <p class="font-thin">Telefone: (49) 98821-2687</p>
        <p class="font-thin">CNPJ: 45.790.457/0001-85</p>
      </div>

      {{-- Espaço em Branco --}}
      <div class="basis-1/6 mt-2">
      </div>
     
      <!-- Ordem de Serviço e Detalhes -->
      <div class="basis-2/6 text-right mt-2">
        <div class="text-left">
          <p class="text-xs font-bold uppercase">Ordem de Serviço</p>
          <p class="text-xl font-bold my-2">{{str_pad($ordem_servico->id, 5, '0', STR_PAD_LEFT)}}</p>
          <p class="font-semibold text-[7px]">TIPO: CORRETIVA</p>
          <p class="font-semibold text-[7px]">{{$ordem_servico->status}}</p>
        </div>
      </div>
    </div>
  
  <div class="flex justify-between mx-10">
    <!-- Seção de Dados do Cliente -->
    <div class="p-4 w-42">
      <h2 class="text-sm font-semibold mb-2">CLIENTE</h2>
      <p class="text-xs font-medium break-words" style="text-transform: uppercase;">{{$cliente->nome}}</p>
      <p class="text-xs font-thin whitespace-pre-line">{{$cliente->enderecos->first()}}</p>
      <p class="text-xs font-thin">Telefone: {{$contato->telefone_cel ?? ($contato->telefone_fixo ?? '')}}</p>
    </div>
  
    <!-- Seção de Dados do Equipamento -->
    <div class="border-t p-4">
      <h2 class="text-sm font-semibold mb-2">EQUIPAMENTO</h2>
      <p class="text-xs font-medium">{{$equipamento->descricao}}</p>
      <p class="text-xs font-thin">Nro. Série: {{$equipamento->nro_serie}}</p>
      <p class="text-xs font-thin">Marca: {{$equipamento->marca}}</p>
      <p class="text-xs font-thin">Modelo: {{$equipamento->modelo}}</p>
    </div>
  </div>


  <div class="mt-2 border border-black">
    <h2 class="text-sm font-semibold m-2">
      RELATO CLIENTE
    </h2>
    <p class="m-2 text-xs font-normal">{{$ordem_servico->relato_cliente}}</p>
  </div>

  <div class="mt-2 border border-black ">
    <h2 class="text-sm font-semibold m-2">
      SERVIÇOS
    </h2>
    <ul class="m-2">
      <li class="flex flex-row text-xs font-semibold mb-2">
        <p class="basis-3/4">Descrição</p>
        <p class="basis-1/4">Quantidade</p>
        <p class="basis-1/4">Valor Unitário</p>
        <p class="basis-1/4">Valor Total</p>
      </li>

      @foreach ($itens as $item)
        <li class="flex flex-row font-normal text-sm">
          <p class="basis-3/4">{{$item->servico->nome}}</p>
          <p class="basis-1/4">{{$item->quantidade}} Un(s)</p>
          <p class="basis-1/4">{{'R$ '.number_format($item->valor_unitario, 2, '.', ',')}}</p>
          <p class="basis-1/4">{{'R$ '.number_format($item->valor_total, 2, '.', ',')}}</p>
        </li>
      @endforeach
    </ul>

    <div class="px-2">
      <hr class="h-[1px] bg-black border-0">
    </div>
    
    <ul class="m-2">
      <li class="flex flex-row font-semibold text-sm">
        <p class="basis-3/4"></p>
        <p class="basis-2/4">Total em Serviços</p>
        <p class="basis-1/4">{{'R$ '.number_format($ordem_servico->valor_total, 2, '.', ',')}}</p>
      </li>
      @if ($ordem_servico->desconto > 0)
        <li class="flex flex-row font-semibold text-sm">
          <p class="basis-3/4"></p>
          <p class="basis-2/4">Desconto</p>
          <p class="basis-1/4">{{'R$ '.number_format($ordem_servico->desconto, 2, '.', ',')}}</p>
        </li>
      @endif
    </ul>
  </div>

  <div class="grid justify-end mt-3">
    <h3 class="font-bold">VALOR DESTA FATURA {{'R$ '.number_format($ordem_servico->valor_total - $ordem_servico->desconto, 2, '.', ',')}}</h3>
  </div>

  <div class="grid justify-items-center my-10">
    <p class="text-center m-0 text-xs font-normal">
      Autorizo a execução dos serviços discriminados e assumo a responsabilidade pelos dados fornecidos, bem como a responsabilidade da cobrança e/ou pagamento em caso de inadimplência por parte do referido cliente.
    </p>
    <h2 class="font-semibold mt-4" style="text-transform: uppercase;">
      {{$ordem_servico->getDataFormated()}}
    </h2>
  </div>

  <div class="grid grid-cols-2 gap-8 mt-20">
    <!-- Assinatura da Empresa -->
    <div class="flex flex-col items-center">
      <!-- Linha para Assinatura -->
      <div class="w-64 border-t-2 border-gray-800 mb-2"></div>
      <!-- Nome da Empresa -->
      <p class="text-center font-semibold text-sm" style="text-transform: uppercase;">GAMMA ELETRÔNICA LTDA</p>
    </div>

    <!-- Assinatura do Cliente -->
    <div class="flex flex-col items-center">
      <!-- Linha para Assinatura -->
      <div class="w-64 border-t-2 border-gray-800 mb-2"></div>
      <!-- Nome do Cliente -->
      <p class="text-center font-semibold text-sm" style="text-transform: uppercase;">{{$cliente->nome}}</p>
    </div>
  </div>

  
</div>
</body>
</html>