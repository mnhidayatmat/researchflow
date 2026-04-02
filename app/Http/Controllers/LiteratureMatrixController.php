<?php

namespace App\Http\Controllers;

use App\Models\LiteratureEntry;
use App\Models\LiteratureMatrixConfig;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiteratureMatrixController extends Controller
{
    private function getStudent(int $studentId): Student
    {
        $student = Student::findOrFail($studentId);
        $user = Auth::user();

        if ($user->role === 'student') {
            abort_unless($student->user_id === $user->id, 403);
        } elseif (in_array($user->role, ['supervisor', 'cosupervisor'])) {
            abort_unless(
                $student->supervisor_id === $user->id || $student->cosupervisor_id === $user->id,
                403
            );
        }

        return $student;
    }

    private function getConfig(Student $student): LiteratureMatrixConfig
    {
        return LiteratureMatrixConfig::firstOrCreate(
            ['student_id' => $student->id],
            ['columns' => LiteratureMatrixConfig::defaultColumns()]
        );
    }

    private const DEFAULT_FIELDS = [
        'author', 'year', 'title', 'journal', 'doi_url',
        'research_objective', 'methodology', 'dataset',
        'findings', 'limitations', 'relevance', 'keywords', 'notes',
    ];

    public function index(int $student)
    {
        $student = $this->getStudent($student);
        $config  = $this->getConfig($student);
        $entries = LiteratureEntry::where('student_id', $student->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('student.literature-matrix', compact('student', 'config', 'entries'));
    }

    // ── Entry CRUD ──────────────────────────────────────────────────────────

    public function store(Request $request, int $student)
    {
        $student = $this->getStudent($student);

        $data = $request->validate([
            'author'             => 'nullable|string|max:255',
            'year'               => 'nullable|integer|min:1900|max:2100',
            'title'              => 'required|string|max:500',
            'journal'            => 'nullable|string|max:255',
            'doi_url'            => 'nullable|string|max:500',
            'research_objective' => 'nullable|string',
            'methodology'        => 'nullable|string',
            'dataset'            => 'nullable|string',
            'findings'           => 'nullable|string',
            'limitations'        => 'nullable|string',
            'relevance'          => 'nullable|string',
            'keywords'           => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'custom_fields'      => 'nullable|array',
            'custom_fields.*'    => 'nullable|string|max:5000',
        ]);

        $max = LiteratureEntry::where('student_id', $student->id)->max('sort_order') ?? -1;
        $data['student_id'] = $student->id;
        $data['sort_order'] = $max + 1;

        $entry = LiteratureEntry::create($data);

        return response()->json($entry);
    }

    public function update(Request $request, int $student, int $entry)
    {
        $student = $this->getStudent($student);
        $entry   = LiteratureEntry::where('student_id', $student->id)->findOrFail($entry);

        $data = $request->validate([
            'author'             => 'nullable|string|max:255',
            'year'               => 'nullable|integer|min:1900|max:2100',
            'title'              => 'nullable|string|max:500',
            'journal'            => 'nullable|string|max:255',
            'doi_url'            => 'nullable|string|max:500',
            'research_objective' => 'nullable|string',
            'methodology'        => 'nullable|string',
            'dataset'            => 'nullable|string',
            'findings'           => 'nullable|string',
            'limitations'        => 'nullable|string',
            'relevance'          => 'nullable|string',
            'keywords'           => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'custom_fields'      => 'nullable|array',
            'custom_fields.*'    => 'nullable|string|max:5000',
        ]);

        $entry->update($data);

        return response()->json($entry->fresh());
    }

    public function destroy(int $student, int $entry)
    {
        $student = $this->getStudent($student);
        $entry   = LiteratureEntry::where('student_id', $student->id)->findOrFail($entry);
        $entry->delete();

        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, int $student)
    {
        $student = $this->getStudent($student);

        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $sortOrder => $entryId) {
            LiteratureEntry::where('student_id', $student->id)
                ->where('id', $entryId)
                ->update(['sort_order' => $sortOrder]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Column config ────────────────────────────────────────────────────────

    public function updateConfig(Request $request, int $student)
    {
        $student = $this->getStudent($student);

        $request->validate([
            'columns'              => 'required|array',
            'columns.*.key'        => 'required|string',
            'columns.*.label'      => 'required|string|max:100',
            'columns.*.visible'    => 'required|boolean',
            'columns.*.sort_order' => 'required|integer',
            'columns.*.custom'     => 'sometimes|boolean',
        ]);

        $config = $this->getConfig($student);
        $config->update(['columns' => $request->columns]);

        return response()->json(['ok' => true]);
    }

    // ── Import ───────────────────────────────────────────────────────────────

    public function importPreview(Request $request, int $student)
    {
        $student = $this->getStudent($student);
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headers = array_shift($rows) ?: [];
        $preview = array_slice($rows, 0, 5);
        $totalRows = count($rows);

        $path = $request->file('file')->store('literature-imports', 'local');

        return response()->json([
            'headers'   => $headers,
            'preview'   => $preview,
            'totalRows' => $totalRows,
            'filePath'  => $path,
        ]);
    }

    public function import(Request $request, int $student)
    {
        $student = $this->getStudent($student);

        $request->validate([
            'filePath'    => 'required|string',
            'mapping'     => 'required|array',
            'newColumns'  => 'sometimes|array',
            'newColumns.*.key'   => 'required|string',
            'newColumns.*.label' => 'required|string|max:100',
        ]);

        // Auto-create new custom columns if provided
        $newColumns = $request->input('newColumns', []);
        if (!empty($newColumns)) {
            $config = $this->getConfig($student);
            $columns = $config->columns;
            foreach ($newColumns as $nc) {
                // Avoid duplicates
                $exists = collect($columns)->firstWhere('key', $nc['key']);
                if (!$exists) {
                    $columns[] = [
                        'key'        => $nc['key'],
                        'label'      => $nc['label'],
                        'visible'    => true,
                        'sort_order' => count($columns),
                        'custom'     => true,
                    ];
                }
            }
            $config->update(['columns' => $columns]);
        }

        $filePath = $request->input('filePath');
        abort_unless(Storage::disk('local')->exists($filePath), 422, 'Uploaded file not found.');

        $fullPath = Storage::disk('local')->path($filePath);
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $headers = array_shift($rows);
        $mapping = $request->input('mapping');
        $maxSort = LiteratureEntry::where('student_id', $student->id)->max('sort_order') ?? -1;

        $created = [];

        foreach ($rows as $row) {
            $data = ['student_id' => $student->id, 'sort_order' => ++$maxSort];
            $customFields = [];

            foreach ($mapping as $fileCol => $entryField) {
                if (!$entryField || $entryField === 'skip') continue;
                $value = trim((string) ($row[$fileCol] ?? ''));
                if ($value === '') continue;

                if (str_starts_with($entryField, 'custom_')) {
                    $customFields[$entryField] = $value;
                } elseif ($entryField === 'year') {
                    $data['year'] = (int) $value;
                } else {
                    $data[$entryField] = $value;
                }
            }

            if (!empty($customFields)) {
                $data['custom_fields'] = $customFields;
            }

            if (empty($data['title'] ?? '')) continue;

            $created[] = LiteratureEntry::create($data);
        }

        Storage::disk('local')->delete($filePath);

        // Return updated columns so frontend can sync
        $updatedConfig = $this->getConfig($student);

        return response()->json([
            'entries' => $created,
            'count'   => count($created),
            'columns' => $updatedConfig->columns,
        ]);
    }

    // ── Sample template download ────────────────────────────────────────────

    public function template(int $student): StreamedResponse
    {
        $student = $this->getStudent($student);
        $config  = $this->getConfig($student);

        $visibleColumns = collect($config->columns)
            ->where('visible', true)
            ->sortBy('sort_order')
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Literature Template');

        // Header row
        $col = 1;
        foreach ($visibleColumns as $column) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($column['label']);

            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);

            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }

        // Two sample rows for guidance
        $sampleData = [
            ['author' => 'Smith, J. & Lee, K.', 'year' => '2024', 'title' => 'Sample Research Paper Title',
             'journal' => 'Journal of Example Studies', 'doi_url' => 'https://doi.org/10.1000/example',
             'research_objective' => 'To investigate the effect of X on Y',
             'methodology' => 'Quantitative survey (n=200)', 'dataset' => 'Survey responses',
             'findings' => 'Significant positive correlation found', 'limitations' => 'Small sample size',
             'relevance' => 'Directly related to RQ1', 'keywords' => 'keyword1, keyword2',
             'notes' => 'Important reference for literature review'],
            ['author' => 'Brown, A.', 'year' => '2023', 'title' => 'Another Example Study on Topic Z',
             'journal' => 'International Review of Research', 'doi_url' => 'https://doi.org/10.1000/example2',
             'research_objective' => 'To compare methods A and B',
             'methodology' => 'Mixed methods case study', 'dataset' => 'Interview transcripts',
             'findings' => 'Method A outperformed Method B', 'limitations' => 'Single case study',
             'relevance' => 'Supports methodology choice', 'keywords' => 'method A, method B',
             'notes' => 'Referenced by multiple authors'],
        ];

        foreach ($sampleData as $rowIdx => $row) {
            $col = 1;
            foreach ($visibleColumns as $column) {
                $value = $row[$column['key']] ?? ($column['custom'] ?? false ? 'Custom field value' : '');
                $sheet->getCellByColumnAndRow($col, $rowIdx + 2)->setValue($value);

                $sheet->getStyleByColumnAndRow($col, $rowIdx + 2)->applyFromArray([
                    'font'      => ['color' => ['rgb' => '999999'], 'italic' => true],
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5E4']]],
                ]);

                $col++;
            }
        }

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->freezePane('A2');

        $filename = 'literature-matrix-template.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── Excel export ──────────────────────────────────────────────────────────

    public function export(int $student): StreamedResponse
    {
        $student = $this->getStudent($student);
        $config  = $this->getConfig($student);
        $entries = LiteratureEntry::where('student_id', $student->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $visibleColumns = collect($config->columns)
            ->where('visible', true)
            ->sortBy('sort_order')
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Literature Matrix');

        $col = 1;
        foreach ($visibleColumns as $column) {
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($column['label']);

            $sheet->getStyleByColumnAndRow($col, 1)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);

            $col++;
        }

        $row = 2;
        foreach ($entries as $entry) {
            $col = 1;
            foreach ($visibleColumns as $column) {
                $isCustom = !empty($column['custom']);
                $value = $isCustom
                    ? ($entry->custom_fields[$column['key']] ?? '')
                    : ($entry->{$column['key']} ?? '');

                $sheet->getCellByColumnAndRow($col, $row)->setValue($value);

                $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5E4']]],
                ]);

                $col++;
            }
            $row++;
        }

        for ($c = 1; $c <= $visibleColumns->count(); $c++) {
            $sheet->getColumnDimensionByColumn($c)->setWidth(min(50, 20));
        }
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->freezePane('A2');

        $filename = 'literature-matrix-' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
