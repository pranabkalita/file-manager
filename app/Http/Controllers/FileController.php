<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FileController extends Controller
{
    function myFiles()
    {
        return Inertia::render('MyFiles');
    }
}
