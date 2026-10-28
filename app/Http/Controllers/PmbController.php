<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PmbController extends Controller
{
    /**
     * Display PMB UBSI Information Center.
     */
    public function index()
    {
        return view('pmb.index', [
            'title' => 'Informasi PMB UBSI',
            'pmbUrl' => 'https://pmbubsi.id/pmb',
        ]);
    }
}
