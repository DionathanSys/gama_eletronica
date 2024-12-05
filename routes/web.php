<?php

use App\Enums\StatusProcessoOrdemServicoEnum;
use App\Models\Endereco;
use App\Models\NotaEntrada;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Services\BuscaCNPJ;
use App\Services\NfeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use HeadlessChromium\BrowserFactory;

// Route::get('/', function () {


// }); 


Route::get('/teste', function () {

    dd(
        collect(StatusProcessoOrdemServicoEnum::cases())
            ->mapWithKeys(fn ($status) => [$status->value => dump($status->getStatus())])
            ->toArray()
);



});

Route::get('nf/{chave}/preview', function ($chave) {

    $resp = (new NfeService())->consulta($chave);

    if ($resp->sucesso) {

        $tentativa = 0;
        while ($resp?->pdf) {
            $tentativa++;
            if ($tentativa > 5) {
                break; // Sai do loop após 5 tentativas
            }
            sleep(2); // Aguarda 2 segundos
        }
        
        $pdf = base64_decode($resp->pdf);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="documento.pdf"');
    }
})->name('nfe.preview.pdf');

Route::get('/cnpj/{cnpj}', function($cnpj){
    $resp = (new BuscaCNPJ($cnpj))->getInfo();
    if ($resp){
        dd($resp,$resp->inscricoes_estaduais[0]->inscricao_estadual);
    }
});    

Route::get('/pdf', function () {
    try {
        // Cria a instância do navegador
        $browserFactory = new BrowserFactory('C:/Program Files/Google/Chrome/Application/chrome.exe');
        $browser = $browserFactory->createBrowser();

        // Gera o HTML para o PDF
        $html = view('ordem_servico.padrao')->render();

        // Abre uma nova página no navegador headless
        $page = $browser->createPage();
        $page->navigate('data:text/html,' . $html)->waitForNavigation();

        // Gera o PDF
        $pdf = $page->pdf([
            'printBackground' => true,
            'format' => 'A4',
            'marginTop' => 20,
            'marginRight' => 20,
            'marginBottom' => 40,
            'marginLeft' => 20,
        ]);

        // Fecha o navegador
        $browser->close();

        // Retorna o PDF para o navegador
        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="relatorio.pdf"');

    } catch (\Exception $e) {
        return response('Erro ao gerar o PDF: ' . $e->getMessage(), 500);
    }
});

Route::prefix('os')->group(function() {

    Route::get('{id}/html', function ($id){
    
        $ordemServico = OrdemServico::with(['itens.servico', 'parceiro.enderecos', 'equipamento'])->findOrFail($id);

        return view('ordem_servico.padrao', [
            'ordem_servico' => $ordemServico,
            'itens' => $ordemServico->itens,
            'cliente' => $ordemServico->parceiro,
            'equipamento' => $ordemServico->equipamento,
        ]);

    })->name('os.html');

    Route::get('{id}/orcamento/html', function ($id){
    
        $ordemServico = OrdemServico::with(['itens.servico', 'parceiro.enderecos', 'equipamento'])->findOrFail($id);

        return view('ordem_servico.orcamento', [
            'ordem_servico' => $ordemServico,
            'itens' => $ordemServico->itens_orcamento,
            'cliente' => $ordemServico->parceiro,
            'equipamento' => $ordemServico->equipamento,
        ]);

    })->name('os.orcamento.html');
});

Route::get('/nf/correcao/{chave}', function ($chave) {


    $payload = [
        "chave" => $chave,
        "justificativa" => "Correção de informações complementares de transporte. Ajuste realizado para corrigir a quantidade de volumes e peso informados anteriormente. A quantidade correta é de 1 volume com peso total de 4,96 kg. Não houve alteração nos valores fiscais ou na natureza da operação.",
    ];
    
    $resp = (new NfeService())->correcao($payload);

    sleep(8);

    if ($resp->sucesso){
        // dump('sucesso', $resp);

        $pdfContent = base64_decode($resp->pdf_carta_correcao);

        if ($pdfContent === false) {
            return response('Erro ao decodificar o PDF.', 500);
        }

        // Retorna o PDF para o navegador exibir
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="documento.pdf"');

    } else {
        dump('falha', $resp);
    }

});
