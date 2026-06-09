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

    public function __construct($studyProgramId = null)
    {
        $this->studyProgramId = $studyProgramId;
    }

    public function view(): View
    {
        // Load the registrations with student relationship, filtering only approved ones
        $query = SkpiRegistration::with('student')->where('status', 'approved');

        if ($this->studyProgramId) {
            $query->whereHas('student', function ($q) {
                $studyProgram = \App\Models\StudyProgram::find($this->studyProgramId);
                if ($studyProgram) {
                    $q->where('program_studi', $studyProgram->name);
                }
            });
        }

        $registrations = $query->get();
        
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
