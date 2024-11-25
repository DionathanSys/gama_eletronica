<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/resources/css/os.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
    <!-- <link rel="preconnect" href="https://fonts.googleapis.com"> -->
    <!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet"> -->
    
</head>
<body class="">
  <div class="p-4 mt-10 mx-8">

    <!-- Seção Superior -->
    <div class="flex flex-row items-start border p-4 border-black">

      <!-- Logo e Informações da Empresa -->
      <div class="basis-1/6 mt-2">
        <img src="{{storage_path('app\public\logo.png')}}" alt="Logo" class="h-18 w-h-18 px-3">
      </div>
      <div class="basis-2/6 mt-2 ms-3 text-xs">
        <p class="font-semibold">THERMO KING & CARRIER TRANSICOLD</p>
        <p class="font-thin">Rua Guimorvam Moura, Travessa E, 98</p>
        <p class="font-thin">Efapi 89809-562 - Chapecó - SC</p>
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
          <p class="text-xl font-bold my-2">{{$ordem_servico['id']}}</p>
          <p class="font-semibold text-[7px]">TIPO: CORRETIVA</p>
          <p class="font-semibold text-[7px]">{{$ordem_servico['status']}}</p>
        </div>
      </div>
    </div>
  
  <div class="flex justify-between mx-10">
    <!-- Seção de Dados do Cliente -->
    <div class="p-4">
      <h2 class="text-sm font-semibold mb-2">CLIENTE</h2>
      <p class="text-xs font-medium">AS REFRIGERAÇÃO LTDA</p>
      <p class="text-xs font-thin">Rua Exemplo, 456 - Cidade, Estado</p>
      <p class="text-xs font-thin">Telefone: (11) 9876-5432</p>
    </div>
  
    <!-- Seção de Dados do Equipamento -->
    <div class="border-t p-4">
      <h2 class="text-sm font-semibold mb-2">EQUIPAMENTO</h2>
      <p class="text-xs font-medium">PAINEL HM THERMO KING</p>
      <p class="text-xs font-thin">Tipo: Compressor</p>
      <p class="text-xs font-thin">Marca: ABC</p>
      <p class="text-xs font-thin">Modelo: X123</p>
    </div>
  </div>


  <div class="mt-2 border border-black">
    <h2 class="text-sm font-semibold m-2">
      RELATO CLIENTE
    </h2>
    <p class="m-2 text-xs font-normal">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Consequuntur, ratione labore quisquam debitis qui aspernatur libero maxime, veniam cum quibusdam consequatur repellat aliquid earum quod natus? Praesentium cupiditate voluptas maxime!</p>
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
      <li class="flex flex-row font-normal text-sm">
        <p class="basis-3/4">Substituição de display</p>
        <p class="basis-1/4">01 Un.(s)</p>
        <p class="basis-1/4">R$ 1.900,00</p>
        <p class="basis-1/4">R$ 1.900,00</p>
      </li>
      <li class="flex flex-row font-normal text-sm">
        <p class="basis-3/4">Remanufatura de Carcaça</p>
        <p class="basis-1/4">01 Un.(s)</p>
        <p class="basis-1/4">R$ 400,00</p>
        <p class="basis-1/4">R$ 400,00</p>
      </li>
      <li class="flex flex-row font-normal text-sm">
        <p class="basis-3/4">Desconto</p>
        <p class="basis-1/4">-</p>
        <p class="basis-1/4">R$ 300,00</p>
        <p class="basis-1/4">R$ 300,00</p>
      </li>
    </ul>

    <div class="px-2">
      <hr class="h-[1px] bg-black border-0">
    </div>
    
    <ul class="m-2">
      <li class="flex flex-row font-semibold text-sm">
        <p class="basis-3/4"></p>
        <p class="basis-2/4">Total em Serviços</p>
        <p class="basis-1/4">R$ 2.000,00</p>
      </li>
    </ul>
  </div>

  <div class="grid justify-end mt-3">
    <h3 class="font-bold">VALOR DESTA FATURA: R$ 2.000,00</h3>
    <p class="text-xs font-thin">Dinheiro - à Vista</p>
  </div>

  <div class="grid justify-items-center my-10">
    <p class="text-center m-0 text-xs font-normal">
      Autorizo a execução dos serviços discriminados e assumo a responsabilidade pelos dados fornecidos, bem como a responsabilidade da cobrança e/ou pagamento em caso de inadimplência por parte do referido cliente.
    </p>
    <h2 class="font-semibold mt-4">
      CHAPECÓ, 19 DE NOVEMBRO DE 2024
    </h2>
  </div>

  <div class="grid grid-cols-2 gap-8 mt-20">
    <!-- Assinatura da Empresa -->
    <div class="flex flex-col items-center">
      <!-- Linha para Assinatura -->
      <div class="w-64 border-t-2 border-gray-800 mb-2"></div>
      <!-- Nome da Empresa -->
      <p class="text-center font-semibold text-sm">THERMO KING & CARRIER TRANSICOLD</p>
    </div>

    <!-- Assinatura do Cliente -->
    <div class="flex flex-col items-center">
      <!-- Linha para Assinatura -->
      <div class="w-64 border-t-2 border-gray-800 mb-2"></div>
      <!-- Nome do Cliente -->
      <p class="text-center font-semibold text-sm">AS REFRIGERAÇÃO LTDA</p>
    </div>
  </div>

  <div class="mt-10">
      <!-- Seção Superior -->
      <div class="flex flex-row items-start border p-4 border-black">

        <!-- Logo e Informações da Empresa -->
        <div class="basis-1/6 mt-2">
          <img src="{{storage_path('app\public\logo.png')}}" alt="Logo" class="h-18 w-h-18 px-3">
        </div>
        <div class="basis-2/6 mt-2 ms-3 text-xs">
          <p class="font-semibold">THERMO KING & CARRIER TRANSICOLD</p>
          <p class="font-thin">Rua Guimorvam Moura, Travessa E, 98</p>
          <p class="font-thin">Efapi 89809-562 - Chapecó - SC</p>
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
            <p class="text-xl font-bold my-2">0744</p>
            <p class="font-semibold text-[7px]">TIPO: CORRETIVA</p>
            <p class="font-semibold text-[7px]">STATUS: PENDENTE</p>
          </div>
        </div>
      </div>
    </div>
</div>
</body>
</html>