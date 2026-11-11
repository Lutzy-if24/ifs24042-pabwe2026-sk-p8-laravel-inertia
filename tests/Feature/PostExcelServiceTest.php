<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class PostExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostExcelService $service;

    /** @var string[] */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PostExcelService::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    private function tempXlsxPath(): string
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('postexcel_', true) . '.xlsx';
        $this->tempFiles[] = $path;

        return $path;
    }

    /** Buat file xlsx sungguhan dari array baris (baris pertama = header). */
    private function makeXlsx(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = $this->tempXlsxPath();
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    /** Jalankan callback stream (ini yang menulis xlsx ke php://output) lalu baca hasilnya. */
    private function readStreamedSheet(StreamedResponse $response): array
    {
        ob_start();
        $response->sendContent();
        $binary = ob_get_clean();

        $this->assertNotEmpty($binary);

        $path = $this->tempXlsxPath();
        file_put_contents($path, $binary);

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
    }

    // ── Export: isi stream (baris 95-96) ─────────────────────────────

    public function test_export_for_user_streams_a_valid_xlsx_with_the_users_posts(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        Post::factory()->create(['user_id' => $user->id, 'title' => 'Artikel Milik Saya']);
        Post::factory()->create(['user_id' => $other->id, 'title' => 'Artikel Orang Lain']);

        $response = $this->service->exportForUser($user->id);

        $this->assertStringContainsString('artikel-saya.xlsx', $response->headers->get('Content-Disposition'));
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('Content-Type')
        );

        $rows = $this->readStreamedSheet($response);

        $this->assertSame('Judul', $rows[0][1]);
        $this->assertSame('Tanggal Publish', $rows[0][7]);
        $this->assertCount(8, $rows[0]);

        $titles = array_column($rows, 1);
        $this->assertContains('Artikel Milik Saya', $titles);
        $this->assertNotContains('Artikel Orang Lain', $titles);
    }

    public function test_export_all_streams_a_valid_xlsx_with_author_column(): void
    {
        $author = User::factory()->create(['name' => 'Penulis Uji']);
        Post::factory()->create(['user_id' => $author->id, 'title' => 'Artikel Admin Export']);

        $response = $this->service->exportAll();

        $this->assertStringContainsString('semua-artikel.xlsx', $response->headers->get('Content-Disposition'));

        $rows = $this->readStreamedSheet($response);

        $this->assertCount(9, $rows[0]);
        $this->assertSame('Penulis', $rows[0][8]);
        $this->assertContains('Artikel Admin Export', array_column($rows, 1));
        $this->assertContains('Penulis Uji', array_column($rows, 8));
    }

    // ── Import: baris kosong (baris 122) ─────────────────────────────

    public function test_import_silently_skips_rows_where_title_and_category_are_both_empty(): void
    {
        $user     = User::factory()->create();
        $category = PostCategory::values()[0];

        $path = $this->makeXlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Konten'],
            ['Artikel A', $category, 'Ringkasan A', 'Isi A'],
            [null, null, null, null],
            ['Artikel B', $category, '', 'Isi B'],
        ]);

        $result = $this->service->importFromFile($path, $user->id);

        $this->assertSame(2, $result['imported']);
        $this->assertSame([], $result['errors']);
        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseHas('posts', ['user_id' => $user->id, 'title' => 'Artikel A']);
        $this->assertDatabaseHas('posts', ['user_id' => $user->id, 'title' => 'Artikel B']);
    }

    // ── Import: konten kosong (baris 136-137) ────────────────────────

    public function test_import_reports_an_error_for_rows_with_empty_content(): void
    {
        $user     = User::factory()->create();
        $category = PostCategory::values()[0];

        $path = $this->makeXlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Konten'],
            ['Artikel Valid', $category, '', 'Isi valid'],
            ['Artikel Tanpa Konten', $category, 'Ringkasan', ''],
        ]);

        $result = $this->service->importFromFile($path, $user->id);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(['Baris 3: Konten tidak boleh kosong.'], $result['errors']);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel Valid']);
        $this->assertDatabaseMissing('posts', ['title' => 'Artikel Tanpa Konten']);
    }
}