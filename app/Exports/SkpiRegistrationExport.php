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
    public function view(): View
    {
        // Load the registrations with student relationship
        $registrations = SkpiRegistration::with('student')->get();
        
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
