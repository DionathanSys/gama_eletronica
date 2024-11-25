<?php

use App\Models\ImpostoServico;
use App\Models\OrdemServico;
use App\Models\Parceiro;
use App\Models\Servico;
use App\Services\BuscaCNPJ;
use App\Services\NfeService;
use Illuminate\Support\Facades\Route;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

use function Spatie\LaravelPdf\Support\pdf;

/* Route::get('/', function () {

    // Servico::insert([
    //     ['nome' => 'Manutenção do sensor de temperatura', 'valor_unitario' => 150.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Reparo do controlador eletrônico', 'valor_unitario' => 300.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Substituição do relé de partida', 'valor_unitario' => 200.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Verificação e ajuste do termostato', 'valor_unitario' => 120.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Instalação de painel de controle digital', 'valor_unitario' => 450.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Troca de cabos e conectores de alta resistência', 'valor_unitario' => 180.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Reparo de fiação elétrica interna', 'valor_unitario' => 220.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Limpeza e recalibração de sensores', 'valor_unitario' => 130.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Diagnóstico completo do sistema eletrônico', 'valor_unitario' => 250.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Atualização do firmware do controlador', 'valor_unitario' => 160.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Verificação de tensão da fonte de alimentação', 'valor_unitario' => 100.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Ajuste de sensibilidade do sensor de temperatura', 'valor_unitario' => 140.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Reparo de sobrecarga no circuito de potência', 'valor_unitario' => 230.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Substituição do fusível principal', 'valor_unitario' => 90.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Verificação da resistência dos cabos de alimentação', 'valor_unitario' => 110.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Teste de continuidade elétrica', 'valor_unitario' => 95.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Instalação de novo sistema de ventilação eletrônica', 'valor_unitario' => 320.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Substituição de resistor queimado', 'valor_unitario' => 105.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Reparo de placa de circuito impresso', 'valor_unitario' => 260.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Verificação de conexões de terra', 'valor_unitario' => 85.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Análise do sistema de controle de umidade', 'valor_unitario' => 175.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Troca de transformador de corrente', 'valor_unitario' => 400.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Instalação de relé de segurança', 'valor_unitario' => 210.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Atualização de software de monitoramento', 'valor_unitario' => 170.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Substituição de termopares danificados', 'valor_unitario' => 190.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Calibração de sensores de temperatura', 'valor_unitario' => 155.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Reparo de curto-circuito no sistema', 'valor_unitario' => 290.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Teste de eficiência de resfriamento', 'valor_unitario' => 135.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Ajuste da velocidade do ventilador', 'valor_unitario' => 145.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Troca de módulo de controle remoto', 'valor_unitario' => 360.00, 'created_by' => 1, 'updated_by' => 1],
    //     ['nome' => 'Instalação de novo sensor de pressão', 'valor_unitario' => 240.00, 'created_by' => 1, 'updated_by' => 1],
    // ]);
}); */


Route::get('/teste', function () {
    $var = OrdemServico::find(2);
    dd($var->notaRemessa->chave_nota);
});

Route::get('nf/{chave}/preview', function ($chave) {

    $resp = (new NfeService())->consulta($chave);

    if ($resp->sucesso) {

        if($resp->pdf == null){sleep(3);}
        
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

Route::get('/pdf', function (){
   
    $ordem = OrdemServico::find(752);
    $param = [
        'ordem' => $ordem,
        'logo' => storage_path('app\public\logo.png')
    ];

    return Pdf::view('ordem_servico.padrao', $param)
        ->withBrosershot(function (Browsershot $browsershot) {
            $browsershot->setCustomTempPath('/tmp')
            ->setChromePath('/usr/bin/google-chrome')
            ->newHeadless();
        })
        ->save(storage_path('app\public\ordem.pdf')); 

    // return pdf()
    //     ->view('ordem_servico.padrao',['logo' => storage_path('app\public\logo.png')])
    //     ->name('teste.pdf')
    //     ->download();

});

Route::get('/teste-pdf', function (){

        return Browsershot::html('<h1>Teste</h1>')
            ->setChromePath('/usr/bin/chromium-browser')
            ->setIncludePath('$PATH:/usr/local/bin')
            ->setNodeBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\node.exe' : '/usr/local/bin/node') 
            ->setNpmBinary(env('AMBIENTE') == 'windows' ? 'C:\Program Files\nodejs\npm.cmd' : '/usr/local/bin/npm')
            ->save(storage_path('app\public\banana.pdf'));

});