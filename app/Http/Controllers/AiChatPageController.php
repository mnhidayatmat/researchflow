<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AiChatPageController extends Controller
{
    public function index()
    {
        return view('ai.chat', [
            'userName' => Auth::user()->name,
        ]);
    }
}
