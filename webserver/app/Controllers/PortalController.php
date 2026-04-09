<?php

namespace App\Controllers;

class PortalController extends BaseController
{
    public function index(): string
    {
        return view('portal/app');
    }
}
