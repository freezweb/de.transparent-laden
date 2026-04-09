<?php

namespace App\Controllers;

class AdminWebController extends BaseController
{
    public function index(): string
    {
        return view('admin/app');
    }
}
