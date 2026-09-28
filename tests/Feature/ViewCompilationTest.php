<?php

namespace Tests\Feature;

use Illuminate\View\Compilers\BladeCompiler;
use Tests\TestCase;

/**
 * Mengompilasi seluruh blade lalu memeriksa sintaks PHP hasilnya.
 *
 * Laravel membiarkan beberapaKesalahan Blade lolos kompilasi (misalnya operator
 * `??` di dalam list-destructuring, atau karakter kutip ganda di dalam nilai
 * atribut komponen) dan baru gagal saat halaman benar-benar dirender. Test ini
 * menangkapnya lebih awal.
 */
class ViewCompilationTest extends TestCase
{
    public function test_every_blade_view_compiles_to_valid_php(): void
    {
        $broken = [];

        foreach ($this->bladeFiles() as $path) {
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);

            try {
                $compiled = $this->freshCompiler()->compileString(file_get_contents($path));
            } catch (\Throwable $e) {
                $broken[] = $relative.': gagal dikompilasi - '.$e->getMessage();

                continue;
            }

            $errors = $this->lint($compiled);

            if ($errors !== []) {
                $broken[] = $relative.': '.implode(' | ', $errors);
            }
        }

        $this->assertSame([], $broken, "View bermasalah:\n".implode("\n", $broken));
    }

    /**
     * Tag komponen yang gagal dikompilasi tidak menghasilkan PHP rusak, hanya
     * teks mentah yang bocor ke HTML hasil render.
     *
     * Contohnya kutip ganda di dalam nilai atribut berkolom bind
     * (`:hint="... .' dari " . ..."`): Blade gagal mengenali tag `<x-stat-card>`
     * lalu memunculkannya apa adanya sebagai teks, sehingga kartu statistik
     * hilang dan kode PHP mentah tampil di halaman. Test sintaks PHP di atas
     * tidak menangkapnya, jadi tag mentah diperiksa di sini.
     */
    public function test_no_view_leaves_uncompiled_component_tags(): void
    {
        $leaked = [];

        foreach ($this->bladeFiles() as $path) {
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            $compiled = $this->freshCompiler()->compileString(file_get_contents($path));

            // Tag yang sudah terkompilasi berubah menjadi blok PHP, jadi cukup
            // menyisakan HTML literal. `<x-` yang tersisa di situ berarti tag
            // komponen gagal dikompilasi dan bocor apa adanya ke halaman.
            $html = preg_replace('/<\?php.*?\?>/s', '', $compiled);

            if (preg_match('/<\/?x-[a-z][\w:.-]*/i', $html, $match)) {
                $leaked[] = $relative.': tag komponen tidak terkompilasi - '.$match[0];
            }
        }

        $this->assertSame([], $leaked, "Tag komponen bocor:\n".implode("\n", $leaked));
    }

    /**
     * Compiler baru untuk setiap view.
     *
     * Blade menyimpan state (stack komponen, alias) pada satu instance
     * compiler. Bila instance yang sama dipakai berulang, state dari view
     * sebelumnya dapat bocor dan menghasilkan false positive. Pada aplikasi
     * nyata setiap request mengompilasi view dengan instance sendiri, jadi
     * test ini meniru kondisi tersebut.
     */
    private function freshCompiler(): BladeCompiler
    {
        return new BladeCompiler(
            app('files'),
            sys_get_temp_dir(),
        );
    }

    public function test_no_view_queries_the_database_directly(): void
    {
        // Query di dalam view menutupi N+1, sulit diuji, dan membocorkan
        // struktur data ke presentasi. Semua query harus lewat controller atau
        // service. Method pada Collection yang sudah di-eager-load
        // (count/sum/pluck) bukan query dan tetap boleh dipakai.
        $offenders = [];

        $patterns = [
            'Model::query()' => 'query() model statis',
            'Model::where(' => 'where() model statis',
            'Model::find(' => 'find() model statis',
            'Model::all()' => 'all() model statis',
            'Model::count()' => 'count() model statis',
            'Model::first()' => 'first() model statis',
            'Model::latest(' => 'latest() model statis',
            'Model::orderBy(' => 'orderBy() model statis',
            'Model::with(' => 'with() model statis',
            'DB::table(' => 'DB::table()',
            'DB::select(' => 'DB::select()',
            'DB::raw(' => 'DB::raw()',
        ];

        foreach ($this->bladeFiles() as $path) {
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            $contents = file_get_contents($path);

            // Komponen presentasi tidak boleh melakukan query sama sekali.
            $isComponent = str_contains($relative, 'views'.DIRECTORY_SEPARATOR.'components');

            foreach ($patterns as $needle => $reason) {
                $class = substr($needle, 0, strrpos($needle, '::'));

                if (! $isComponent && ! str_contains($contents, $needle)) {
                    continue;
                }

                // Cocokkan pemanggilan statis pada kelas model tertentu.
                if (preg_match('/\\\\?(?:App\\\\)?(?:Models|Enums)\\\\?\w+::(query|where|find|all|with|count)\s*\(/', $contents, $match)
                    || str_contains($contents, 'DB::table(')
                    || str_contains($contents, 'DB::select(')
                    || str_contains($contents, 'DB::raw(')
                    || str_contains($contents, 'DB::statement(')) {
                    $offenders[] = $relative.' - '.$reason;
                }
            }

            // Komponen:qeuação tidak boleh memanggil builder sama sekali.
            if ($isComponent && preg_match('/\b[A-Z]\w+::(query|where|find|all|with)\(/', $contents, $match)) {
                $offenders[] = $relative.' - '.$match[0].' di dalam komponen';
            }
        }

        $this->assertSame([], $offenders, "Query di dalam view:\n".implode("\n", $offenders));
    }

    /** @return list<string> */
    private function bladeFiles(): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /** @return list<string> */
    private function lint(string $php): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'blade').'.php';
        file_put_contents($tmp, $php);

        $output = [];
        $exitCode = 0;

        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($tmp).' 2>&1', $output, $exitCode);

        @unlink($tmp);

        if ($exitCode === 0) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $output)));
    }
}
