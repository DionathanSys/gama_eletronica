<?php

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::dropIfExists('ordens_servico');
    Schema::create('ordens_servico', function (Blueprint $table) {
        $table->id();
        $table->text('img_equipamento')->nullable();
    });

    Storage::fake('test-images');
    $this->destination = storage_path('framework/testing/os-images-'.Str::uuid());
});

afterEach(function () {
    File::deleteDirectory($this->destination);
});

it('copies every image reference and writes a manifest', function () {
    Storage::disk('test-images')->put('uploads/front.jpg', 'front image');
    Storage::disk('test-images')->put('uploads/back.jpg', 'back image');

    DB::table('ordens_servico')->insert([
        'id' => 42,
        'img_equipamento' => json_encode([
            'uploads/front.jpg',
            'uploads/back.jpg',
        ]),
    ]);

    $this->artisan('os:collect-images', [
        'destination' => $this->destination,
        '--disk' => 'test-images',
    ])->assertExitCode(Command::SUCCESS);

    $manifest = json_decode(File::get($this->destination.'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
    $files = collect($manifest['files'])->keyBy('reference');

    expect(File::get($this->destination.'/files/uploads/front.jpg'))
        ->toBe('front image')
        ->and(File::get($this->destination.'/files/uploads/back.jpg'))
        ->toBe('back image')
        ->and($files['uploads/front.jpg']['status'])->toBe('copied')
        ->and($files['uploads/front.jpg']['sha256'])->toBe(hash('sha256', 'front image'))
        ->and($manifest['summary']['references_found'])->toBe(2)
        ->and($manifest['summary']['copied'])->toBe(2);
});

it('supports a legacy single path and is idempotent', function () {
    Storage::disk('test-images')->put('legacy/equipment.png', 'legacy image');

    DB::table('ordens_servico')->insert([
        'id' => 7,
        'img_equipamento' => 'legacy/equipment.png',
    ]);

    $arguments = [
        'destination' => $this->destination,
        '--disk' => 'test-images',
    ];

    $this->artisan('os:collect-images', $arguments)->assertExitCode(Command::SUCCESS);
    $this->artisan('os:collect-images', $arguments)->assertExitCode(Command::SUCCESS);

    $manifest = json_decode(File::get($this->destination.'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['files'][0]['status'])->toBe('already_present')
        ->and($manifest['summary']['already_present'])->toBe(1)
        ->and(File::get($this->destination.'/files/legacy/equipment.png'))
        ->toBe('legacy image');
});

it('returns failure and records missing references', function () {
    DB::table('ordens_servico')->insert([
        'id' => 99,
        'img_equipamento' => json_encode(['missing/photo.jpg']),
    ]);

    $this->artisan('os:collect-images', [
        'destination' => $this->destination,
        '--disk' => 'test-images',
    ])->assertExitCode(Command::FAILURE);

    $manifest = json_decode(File::get($this->destination.'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['files'][0]['status'])->toBe('missing')
        ->and($manifest['files'][0]['exists'])->toBeFalse()
        ->and($manifest['summary']['missing'])->toBe(1)
        ->and(File::exists($this->destination.'/files/missing/photo.jpg'))->toBeFalse();
});

it('does not copy files in dry-run mode', function () {
    Storage::disk('test-images')->put('photo.jpg', 'image');

    DB::table('ordens_servico')->insert([
        'id' => 12,
        'img_equipamento' => json_encode(['photo.jpg']),
    ]);

    $this->artisan('os:collect-images', [
        'destination' => $this->destination,
        '--disk' => 'test-images',
        '--dry-run' => true,
    ])->assertExitCode(Command::SUCCESS);

    $manifest = json_decode(File::get($this->destination.'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['files'][0]['status'])->toBe('would_copy')
        ->and(File::exists($this->destination.'/files/photo.jpg'))->toBeFalse();
});
