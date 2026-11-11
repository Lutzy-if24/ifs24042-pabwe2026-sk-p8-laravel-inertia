<?php

namespace Tests\Feature;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->member()->create();
    }

    // ── Export ─────────────────────────────────────────────────────────

    public function test_member_can_export_own_posts(): void
    {
        Post::factory()->forUser($this->member)->count(3)->create();
        $response = $this->actingAs($this->member)->get(route('dashboard.posts.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_export_all_posts(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->forUser($this->member)->count(2)->create();
        $response = $this->actingAs($admin)->get(route('admin.posts.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_export_user_posts(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->forUser($this->member)->count(2)->create();
        $response = $this->actingAs($admin)->get(route('admin.users.posts.export', $this->member));
        $response->assertStatus(200);
    }

    // ── Import ─────────────────────────────────────────────────────────

    private function createExcelFile(array $rows, string $filename = 'test.xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Header row
        $sheet->fromArray(['Judul', 'Kategori', 'Ringkasan', 'Konten'], null, 'A1');
        // Data rows
        foreach ($rows as $i => $row) {
            $sheet->fromArray($row, null, 'A' . ($i + 2));
        }
        $path = storage_path('app/private/imports/' . $filename);
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        (new Xlsx($spreadsheet))->save($path);
        return $path;
    }

    public function test_member_can_import_valid_excel(): void
    {
        $filePath = $this->createExcelFile([
            ['Artikel Import Satu', 'Teknologi', 'Ringkasan', '## Konten artikel satu'],
            ['Artikel Import Dua', 'Bisnis', 'Ringkasan dua', '## Konten artikel dua'],
        ]);

        $file = new \Illuminate\Http\UploadedFile($filePath, 'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->member)->post(route('dashboard.posts.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('dashboard.posts.index'));
        $this->assertDatabaseHas('posts', [
            'user_id' => $this->member->id,
            'title'   => 'Artikel Import Satu',
        ]);
    }

    public function test_import_skips_rows_with_empty_title(): void
    {
        $filePath = $this->createExcelFile([
            ['', 'Teknologi', 'Ringkasan', 'Konten valid'],
            ['Valid Title', 'Bisnis', '', 'Konten valid'],
        ]);

        $file = new \Illuminate\Http\UploadedFile($filePath, 'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($this->member)->post(route('dashboard.posts.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        // Only 1 valid row (empty title skipped)
        $this->assertDatabaseHas('posts', ['title' => 'Valid Title', 'user_id' => $this->member->id]);
        $this->assertDatabaseMissing('posts', ['title' => '', 'user_id' => $this->member->id]);
    }

    public function test_import_validates_file_required(): void
    {
        $response = $this->actingAs($this->member)->post(route('dashboard.posts.import'), []);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_invalid_category_falls_back_to_umum(): void
    {
        $filePath = $this->createExcelFile([
            ['Category Fallback Test', 'InvalidCategory', 'Ringkasan', 'Konten artikel'],
        ]);

        $file = new \Illuminate\Http\UploadedFile($filePath, 'test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($this->member)->post(route('dashboard.posts.import'), ['file' => $file]);

        $this->assertDatabaseHas('posts', [
            'title'    => 'Category Fallback Test',
            'category' => PostCategory::Umum->value,
        ]);
    }
}
