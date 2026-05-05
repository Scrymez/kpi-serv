<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\KpiScore;
use App\Models\OlympiadRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherReportExport;
use App\Exports\OlympiadReportExport;

class ReportController extends Controller
{
    public function teachers(Request $request)
    {
        return Excel::download(new TeacherReportExport($request->all()), 'отчёт_учителя.xlsx');
    }

    public function olympiads(Request $request)
    {
        return Excel::download(new OlympiadReportExport($request->all()), 'отчёт_олимпиады.xlsx');
    }
}
