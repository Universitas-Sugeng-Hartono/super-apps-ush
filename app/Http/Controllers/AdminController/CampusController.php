<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\StudentInternship;
class CampusController extends Controller
{
    // belum terpakai
   public function index()
    {
        $menu = 'Campus';
        $faqs = Faq::count();
        $students = StudentInternship::count();
        return view('admin.campus.index', compact('menu', 'faqs', 'students'));
    }
}