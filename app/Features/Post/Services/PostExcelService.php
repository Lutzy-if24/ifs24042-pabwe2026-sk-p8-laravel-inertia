<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelService
{
    public function __construct(
        private readonly PostRepositoryInterface $repository
    ) {}

    /**
     * Export posts for a single user.
     */
    public function exportForUser(int $userId): StreamedResponse
    {
        $posts = $this->repository->getForExport($userId);
        return $this->buildExcelResponse($posts, false, "artikel-saya.xlsx");
    }

    /**
     * Export all posts (admin).
     */
    public function exportAll(): StreamedResponse
    {
        $posts = $this->repository->getAllForExport();
        return $this->buildExcelResponse($posts, true, "semua-artikel.xlsx");
    }

    /**
     * Export posts for a specific user (admin).
     */
    public function exportForUserAdmin(int $userId): StreamedResponse
    {
        $posts = $this->repository->getAllForUserExport($userId);
        return $this->buildExcelResponse($posts, true, "artikel-user-{$userId}.xlsx");
    }

    private function buildExcelResponse(Collection $posts, bool $includeAuthor, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        // Headers
        $headers = ['No', 'Judul', 'Slug', 'Kategori', 'Ringkasan', 'Konten', 'Status', 'Tanggal Publish'];
        if ($includeAuthor) {
            $headers[] = 'Penulis';
        }
        $sheet->fromArray($headers, null, 'A1');

        // Style header row
        $headerStyle = $sheet->getStyle('A1:' . ($includeAuthor ? 'I1' : 'H1'));
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF7C3AED');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');

        // Data rows
        $row = 2;
        foreach ($posts as $index => $post) {
            $rowData = [
                $index + 1,
                $post->title,
                $post->slug,
                $post->category instanceof PostCategory ? $post->category->value : $post->category,
                $post->excerpt,
                $post->content,
                $post->is_published ? 'Published' : 'Draft',
                $post->published_at ? $post->published_at->format('Y-m-d H:i') : '',
            ];
            if ($includeAuthor) {
                $rowData[] = $post->user->name ?? '';
            }
            $sheet->fromArray($rowData, null, "A{$row}");
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $includeAuthor ? 'I' : 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import posts from Excel file.
     * Returns ['imported' => int, 'errors' => array].
     */
    public function importFromFile(string $filePath, int $userId): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Skip header row
        $dataRows  = array_slice($rows, 1);
        $imported  = 0;
        $errors    = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we skipped header (row 1)

            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $title    = trim((string) ($row[0] ?? ''));
            $category = trim((string) ($row[1] ?? ''));
            $excerpt  = trim((string) ($row[2] ?? ''));
            $content  = trim((string) ($row[3] ?? ''));

            // Validate required fields
            if (empty($title)) {
                $errors[] = "Baris {$rowNumber}: Judul tidak boleh kosong.";
                continue;
            }
            if (empty($content)) {
                $errors[] = "Baris {$rowNumber}: Konten tidak boleh kosong.";
                continue;
            }

            // Validate category
            $validCategory = PostCategory::fromValueOrDefault($category);

            // Create post
            Post::create([
                'user_id'      => $userId,
                'title'        => $title,
                'slug'         => '',
                'category'     => $validCategory->value,
                'excerpt'      => $excerpt ?: null,
                'content'      => $content,
                'is_published' => false,
                'published_at' => null,
            ]);

            $imported++;
        }

        return ['imported' => $imported, 'errors' => $errors];
    }
}
