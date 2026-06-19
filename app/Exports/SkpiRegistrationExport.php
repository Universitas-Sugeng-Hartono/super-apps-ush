<?php

namespace App\Exports;

use App\Models\SkpiRegistration;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkpiRegistrationExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $studyProgramId;
    protected $status;
    protected $search;

    public function __construct($studyProgramId = null, $status = null, $search = null)
    {
        $this->studyProgramId = $studyProgramId;
        $this->status = $status;
        $this->search = $search;
    }

    public function view(): View
    {
        // Load the registrations with student relationship
        $query = SkpiRegistration::with('student');

        if ($this->studyProgramId) {
            $query->whereHas('student', function ($q) {
                $studyProgram = \App\Models\StudyProgram::find($this->studyProgramId);
                if ($studyProgram) {
                    $q->where('program_studi', $studyProgram->name);
                }
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search !== null && $this->search !== '') {
            $query->where(function ($q) {
                $q->where('nama_lengkap', 'like', "%{$this->search}%")
                    ->orWhere('nim', 'like', "%{$this->search}%")
                    ->orWhereHas('student', function ($studentQuery) {
                        $studentQuery->where('program_studi', 'like', "%{$this->search}%");
                    });
            });
        }

        $registrations = $query->latest('submitted_at')->latest('created_at')->get();
        
        return view('admin.skpi.exports.skpi_registrations', [
            'registrations' => $registrations
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
