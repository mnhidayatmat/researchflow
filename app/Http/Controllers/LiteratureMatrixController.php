<?php

namespace App\Http\Controllers;

use App\Models\LiteratureEntry;
use App\Models\LiteratureMatrixConfig;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'columns'             => 'required|array',
            'columns.*.key'       => 'required|string',
            'columns.*.label'     => 'required|string|max:100',
            'columns.*.visible'   => 'required|boolean',
            'columns.*.sort_order'=> 'required|integer',
        ]);

        $config = $this->getConfig($student);
        $config->update(['columns' => $request->columns]);

        return response()->json(['ok' => true]);
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

            $col++;
        }

        // Data rows
        $row = 2;
        foreach ($entries as $entry) {
            $col = 1;
            foreach ($visibleColumns as $column) {
                $value = $entry->{$column['key']} ?? '';
                $sheet->getCellByColumnAndRow($col, $row)->setValue($value);

                $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E5E4']]],
                ]);

                $col++;
            }
            $row++;
        }

        // Auto-size columns (cap at 50)
        for ($c = 1; $c <= $visibleColumns->count(); $c++) {
            $sheet->getColumnDimensionByColumn($c)->setWidth(min(50, 20));
        }
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Freeze header row
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
