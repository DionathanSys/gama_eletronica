<?php

namespace App\Console\Commands;

use App\Models\OrdemServico;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CollectOrderServiceImages extends Command
{
    protected $signature = 'os:collect-images
        {destination : Directory where the collection and manifest will be created}
        {--disk= : Source filesystem disk (defaults to Filament filesystem disk)}
        {--dry-run : Generate the inventory without copying files}
        {--force : Replace destination files when their content differs}';

    protected $description = 'Collect images referenced by service orders';

    public function handle(): int
    {
        $destination = $this->absolutePath((string) $this->argument('destination'));
        $diskName = (string) ($this->option('disk') ?: config('filament.default_filesystem_disk', 'public'));

        try {
            $sourceDisk = Storage::disk($diskName);
            File::ensureDirectoryExists($destination);
        } catch (Throwable $exception) {
            $this->error("Could not prepare source disk or destination: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $manifest = [
            'version' => 1,
            'generated_at' => now()->toISOString(),
            'source_disk' => $diskName,
            'dry_run' => (bool) $this->option('dry-run'),
            'files' => [],
        ];

        $summary = [
            'orders_scanned' => 0,
            'references_found' => 0,
            'copied' => 0,
            'already_present' => 0,
            'would_copy' => 0,
            'missing' => 0,
            'conflict' => 0,
            'error' => 0,
        ];

        $orders = OrdemServico::query()
            ->select(['id', 'img_equipamento'])
            ->whereNotNull('img_equipamento')
            ->where('img_equipamento', '!=', '')
            ->orderBy('id')
            ->cursor();

        foreach ($orders as $order) {
            $summary['orders_scanned']++;
            $references = $this->referencesFrom($order->getRawOriginal('img_equipamento'));

            foreach ($references as $index => $reference) {
                $summary['references_found']++;
                $record = $this->collectReference(
                    $sourceDisk,
                    $diskName,
                    (int) $order->id,
                    $index + 1,
                    $reference,
                    $destination,
                );

                $manifest['files'][] = $record;
                $summary[$record['status']] = ($summary[$record['status']] ?? 0) + 1;
            }
        }

        $manifest['summary'] = $summary;
        $manifestPath = $destination.DIRECTORY_SEPARATOR.'manifest.json';

        try {
            File::put(
                $manifestPath,
                json_encode(
                    $manifest,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
                ).PHP_EOL,
            );
        } catch (Throwable $exception) {
            $this->error("Could not write manifest: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Count'],
            collect($summary)
                ->map(fn (int $value, string $key) => [$key, $value])
                ->values()
                ->all(),
        );
        $this->line("Manifest: {$manifestPath}");

        return ($summary['missing'] + $summary['conflict'] + $summary['error']) > 0
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function collectReference(
        mixed $sourceDisk,
        string $diskName,
        int $orderId,
        int $index,
        string $reference,
        string $destination,
    ): array {
        $source = $this->inspectSource($sourceDisk, $reference);
        $filename = $this->safeFilename($reference);
        $preservedPath = $source['disk_path'] !== null
            ? $this->safeRelativePath($source['disk_path'])
            : null;
        $collectionPath = $preservedPath !== null
            ? "files/{$preservedPath}"
            : "ordens-servico/{$orderId}/{$index}-{$filename}";
        $target = $destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $collectionPath);

        $record = [
            'order_service_id' => $orderId,
            'field' => 'img_equipamento',
            'reference' => $reference,
            'source_disk' => $diskName,
            'collection_path' => $collectionPath,
            'exists' => $source['exists'],
            'size' => $source['size'],
            'mime_type' => $source['mime_type'],
            'sha256' => $source['sha256'],
            'status' => 'error',
            'error' => $source['error'],
        ];

        if (! $source['exists']) {
            $record['status'] = $source['error'] === 'File not found on source disk.'
                ? 'missing'
                : 'error';

            return $record;
        }

        if ($this->option('dry-run')) {
            $record['status'] = $this->destinationStatus($target, $source['sha256']);

            if ($record['status'] === 'missing') {
                $record['status'] = 'would_copy';
            }

            return $record;
        }

        if (File::isFile($target)) {
            $targetHash = hash_file('sha256', $target);

            if ($targetHash === $source['sha256']) {
                $record['status'] = 'already_present';

                return $record;
            }

            if (! $this->option('force')) {
                $record['status'] = 'conflict';
                $record['error'] = 'Destination file exists with a different hash.';

                return $record;
            }
        }

        $temporaryTarget = null;

        try {
            File::ensureDirectoryExists(dirname($target));
            $temporaryTarget = $target.'.part';
            File::delete($temporaryTarget);
            $this->copySource($sourceDisk, $source, $temporaryTarget);

            if (hash_file('sha256', $temporaryTarget) !== $source['sha256']) {
                File::delete($temporaryTarget);
                throw new \RuntimeException('Copied file hash does not match source hash.');
            }

            if (File::isFile($target)) {
                File::delete($target);
            }

            if (! rename($temporaryTarget, $target)) {
                throw new \RuntimeException('Could not move copied file to its final destination.');
            }

            $record['status'] = 'copied';
            $record['error'] = null;
        } catch (Throwable $exception) {
            if ($temporaryTarget !== null) {
                File::delete($temporaryTarget);
            }

            $record['error'] = $exception->getMessage();
        }

        return $record;
    }

    private function inspectSource(mixed $sourceDisk, string $reference): array
    {
        $path = str_replace('\\', '/', trim($reference));

        if (preg_match('/^https?:\\/\\//i', $path)) {
            return [
                'exists' => false,
                'size' => null,
                'mime_type' => null,
                'sha256' => null,
                'error' => 'URL references are not supported; the original storage path is required.',
                'absolute_path' => null,
                'disk_path' => $path,
            ];
        }

        if (str_starts_with($path, '/') && is_file($path)) {
            return $this->inspectAbsoluteSource($path);
        }

        $diskPath = ltrim($path, '/');

        try {
            if (! $sourceDisk->exists($diskPath)) {
                return [
                    'exists' => false,
                    'size' => null,
                    'mime_type' => null,
                    'sha256' => null,
                    'error' => 'File not found on source disk.',
                    'absolute_path' => null,
                    'disk_path' => $diskPath,
                ];
            }

            $stream = $sourceDisk->readStream($diskPath);

            if (! is_resource($stream)) {
                throw new \RuntimeException('Source disk did not return a readable stream.');
            }

            $hashContext = hash_init('sha256');
            $size = 0;

            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 1024);

                if ($chunk === false) {
                    throw new \RuntimeException('Could not read source file.');
                }

                if ($chunk === '') {
                    continue;
                }

                hash_update($hashContext, $chunk);
                $size += strlen($chunk);
            }

            fclose($stream);

            return [
                'exists' => true,
                'size' => $size,
                'mime_type' => $this->diskMimeType($sourceDisk, $diskPath),
                'sha256' => hash_final($hashContext),
                'error' => null,
                'absolute_path' => null,
                'disk_path' => $diskPath,
            ];
        } catch (Throwable $exception) {
            return [
                'exists' => false,
                'size' => null,
                'mime_type' => null,
                'sha256' => null,
                'error' => $exception->getMessage(),
                'absolute_path' => null,
                'disk_path' => $diskPath,
            ];
        }
    }

    private function inspectAbsoluteSource(string $path): array
    {
        try {
            $hash = hash_file('sha256', $path);

            if ($hash === false) {
                throw new \RuntimeException('Could not calculate source file hash.');
            }

            return [
                'exists' => true,
                'size' => filesize($path),
                'mime_type' => $this->fileMimeType($path),
                'sha256' => $hash,
                'error' => null,
                'absolute_path' => $path,
                'disk_path' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'exists' => false,
                'size' => null,
                'mime_type' => null,
                'sha256' => null,
                'error' => $exception->getMessage(),
                'absolute_path' => $path,
                'disk_path' => null,
            ];
        }
    }

    private function copySource(mixed $sourceDisk, array $source, string $target): void
    {
        $input = $source['absolute_path'] !== null
            ? fopen($source['absolute_path'], 'rb')
            : $sourceDisk->readStream($source['disk_path']);
        $output = fopen($target, 'wb');

        if (! is_resource($input) || ! is_resource($output)) {
            if (is_resource($input)) {
                fclose($input);
            }

            if (is_resource($output)) {
                fclose($output);
            }

            throw new \RuntimeException('Could not open source or destination file.');
        }

        $copied = stream_copy_to_stream($input, $output);
        fclose($input);
        fclose($output);

        if ($copied === false) {
            throw new \RuntimeException('Could not copy source file.');
        }
    }

    private function referencesFrom(mixed $rawValue): array
    {
        if (! is_string($rawValue) || trim($rawValue) === '') {
            return [];
        }

        $decoded = json_decode($rawValue, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $values = is_array($decoded) ? $decoded : [$decoded];
        } else {
            // Older records may contain one plain path instead of JSON.
            $values = [$rawValue];
        }

        return collect($values)
            ->filter(fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(fn (string $value): string => trim($value))
            ->values()
            ->all();
    }

    private function destinationStatus(string $target, ?string $sourceHash): string
    {
        if (! File::isFile($target)) {
            return 'missing';
        }

        return hash_file('sha256', $target) === $sourceHash
            ? 'already_present'
            : 'conflict';
    }

    private function safeFilename(string $reference): string
    {
        $filename = basename(str_replace('\\', '/', trim($reference)));
        $filename = preg_replace('/[^\pL\pN._-]+/u', '_', $filename) ?: 'imagem';

        return trim($filename, '._-') ?: 'imagem';
    }

    private function safeRelativePath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)) {
            return null;
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..' || str_contains($segment, "\0")) {
                return null;
            }

            $segments[] = $segment;
        }

        return $segments === [] ? null : implode('/', $segments);
    }

    private function diskMimeType(mixed $disk, string $path): ?string
    {
        try {
            $mimeType = $disk->mimeType($path);

            return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function fileMimeType(string $path): ?string
    {
        $mimeType = function_exists('mime_content_type') ? mime_content_type($path) : false;

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
    }

    private function absolutePath(string $path): string
    {
        if (Str::startsWith($path, DIRECTORY_SEPARATOR)) {
            return rtrim($path, DIRECTORY_SEPARATOR);
        }

        return base_path($path);
    }
}
