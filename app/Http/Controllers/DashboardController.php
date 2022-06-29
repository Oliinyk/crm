<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Workspace $workspace)
    {
        return Inertia::render('Dashboard/Index');
    }
}
