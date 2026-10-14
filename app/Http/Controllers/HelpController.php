<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpController extends Controller
{
    /**
     * Display the contextual help and documentation page.
     */
    public function index(Request $request): View
    {
        return view('help.index', [
            'user' => $request->user(),
        ]);
    }
}
