<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function preview()
    {
        // Load JSON file
        $json = file_get_contents(storage_path('app/interview_questions.json'));
        $data = json_decode($json, true);

        // Load view
        $pdf = Pdf::loadView('pdf.interview_questions', [
            'data' => $data
        ]);

        // Stream to browser (preview)
        return $pdf->stream('interview_questions.pdf');
    }
}
