<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Setiap file yang disebut manifest Vite harus benar-benar ada di disk.
 *
 * Kejadian nyata: `assets/main-*.css` yang dirujuk manifest terhapus, sehingga
 * 404. Karena file itu memuat seluruh `<style scoped>` komponen Vue, semua
 * komponen kehilangan style-nya — tombol kembali ke gaya bawaan browser,
 * jawaban accordion menumpuk keluar kotak — tanpa ada error di log server.
 * Gejalanya sepele tapi penyebabnya sulit dicari tanpa check ini.
 */
class BuildAssetTest extends TestCase
{
    public function test_manifest_file_exists(): void
    {
        $this->assertFileExists(
            public_path('build/manifest.json'),
            'Manifest Vite tidak ada. Jalankan: npm run build',
        );
    }

    public function test_every_manifest_asset_exists_on_disk(): void
    {
        $build = public_path('build');
        $manifest = json_decode(
            (string) file_get_contents($build.'/manifest.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $missing = [];

        foreach ($manifest as $src => $entry) {
            $files = array_filter([$entry['file'] ?? null, ...($entry['css'] ?? [])]);

            foreach ($files as $file) {
                if (! file_exists($build.'/'.$file)) {
                    $missing[] = $file.'  (dirujuk oleh '.$src.')';
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            "Aset hilang (halaman akan 404):\n  ".implode("\n  ", $missing),
        );
    }

    public function test_landing_entrypoints_are_declared(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(public_path('build/manifest.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertArrayHasKey('resources/css/landing.css', $manifest);
        $this->assertArrayHasKey('resources/js/landing/main.js', $manifest);
    }

    /** Bundle admin tidak boleh ikut bringing Vue, dan sebaliknya. */
    public function test_landing_and_admin_bundles_are_separated(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(public_path('build/manifest.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $adminJs = $manifest['resources/js/app.js'] ?? null;
        $this->assertNotNull($adminJs, 'Entri resources/js/app.js hilang dari manifest.');

        $this->assertArrayNotHasKey(
            'imports',
            $adminJs,
            'Bundle admin ikut menarik chunk bersama. Kalau muncul imports, Vue bisa ikut terunduh di halaman dashboard.',
        );
    }
}
