<?php

use App\Actions\Fiscal\ConsultaNfeAction;
use App\DTO\Fiscal\NfeEstornoDTO;
use App\Models\ItemNotaSaida;
use App\Models\NotaSaida;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Services\BuscaCNPJ;
use App\Services\NfeService;
use CloudDfe\SdkPHP\Nfe;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use HeadlessChromium\BrowserFactory;
use Illuminate\Http\Request;


Route::prefix('nfe')->group(function () {
    Route::get('/{chave}/pdf', function ($chave) {
        $nfe = new Nfe(config('nfe.params'));

        $resp = $nfe->pdf([
            'chave' => $chave,
        ]);

        if ($resp->sucesso) {
            return response(base64_decode($resp->pdf))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="documento.pdf"');
        }
        echo "Erro na geração do PDF da nota fiscal!";
        echo "";
        echo "Aguarde alguns segundos e ATUALIZE (F5) a tela novamente.";
    })->name('nfe.pdf');

    Route::get('/{notaSaida}/preview', function (NotaSaida $notaSaida) {

        $nfe = (new NfeService());
        $resp = $nfe->preview($notaSaida);
        
        if ($resp) {

            $pdf = base64_decode($resp->pdf);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="documento.pdf"');
        }

        echo "Erro na geração do preview da nota fiscal!";
        echo "";
    })->name('nfe.preview');

    Route::get('/{notaSaida}/pdf', function (NotaSaida $notaSaida) {

        $nfe = new Nfe(config('nfe.params'));

        $resp = $nfe->pdf([
            'chave' => $notaSaida->chave_nota,
        ]);

        if ($resp->sucesso) {
            return response(base64_decode($resp->pdf))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="documento.pdf"');
        }
        echo "Erro na geração do PDF da nota fiscal!";
        echo "";
        echo "Aguarde alguns segundos e ATUALIZE (F5) a tela novamente.";
    })->name('nfe.view.pdf');

    Route::get('/webhook/nfe', function () {});
});

Route::post('/api/webhook/nfe', function (Request $request) {
    // $dados = $request->getContent();
    // echo '<pre>';
    // var_dump($dados);
    // echo '</pre>';

    // RetornoNfe::dispatch($request);


    return response('OK', 200);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/cnpj/{cnpj}', function ($cnpj) {
    $resp = (new BuscaCNPJ($cnpj))->getInfo();
    if ($resp) {
        dd($resp, $resp->inscricoes_estaduais[0]->inscricao_estadual);
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

Route::prefix('os')->group(function () {

    Route::get('{id}/html', function ($id) {

        $ordemServico = OrdemServico::with(['itens.servico', 'parceiro.enderecos', 'equipamento'])->findOrFail($id);

        return view('ordem_servico.padrao', [
            'ordem_servico' => $ordemServico,
            'itens' => $ordemServico->itens,
            'cliente' => $ordemServico->parceiro,
            'equipamento' => $ordemServico->equipamento,
        ]);
    })->name('os.html');

    Route::get('{id}/orcamento/html', function ($id) {

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
        "justificativa" => "Correção das informações complementares de transporte. Ajuste realizado para corrigir o responsável pelo pagamento do frete, sendo o correto o emitente da nota fiscal.",
    ];

    // dd($payload);

    $resp = (new NfeService())->correcao($payload);

    sleep(8);

    if ($resp->sucesso) {
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


Route::get('/nf/inutiliza', function (){

    $payload = [
        "numero_inicial" => "1",
        "numero_final" => "1",
        "serie" => "850",
        "justificativa" => "Foi inutilizado devido ao seu salto inadvertido durante emissões iniciais no sistema. Não houve operação fiscal vinculada a este número, e sua inutilização é solicitada para manter a sequência numérica e a conformidade com as normas fiscais vigentes"
    ];

    $resp = (new NfeService())->inutiliza($payload);

    dd($resp);
});

// Route::get('/estorno', function () {

//     $nfe = new Nfe(config('nfe.params'));
//     $dto = NfeEstornoDTO::fromNotaSaida(NotaSaida::find(40));

//     $resp = $nfe->cria($dto->toArray());

//     if (!$resp->sucesso) {
//         dd($resp);
//     }

//     $pdf = base64_decode($resp->pdf);

//     return response($pdf)
//         ->header('Content-Type', 'application/pdf')
//         ->header('Content-Disposition', 'inline; filename="documento.pdf"');

// });

Route::get('/teste', function () {
    ConsultaNfeAction::execute('42250236286501000123558500000000281138822030', 0);
});
