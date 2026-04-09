<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('public/landing');
    }

    public function pricing(): string
    {
        return view('public/pricing');
    }

    public function transparency(): string
    {
        return view('public/transparency');
    }

    public function faq(): string
    {
        return view('public/faq');
    }

    public function contact(): string
    {
        return view('public/contact');
    }

    public function imprint(): string
    {
        return view('public/imprint');
    }

    public function privacy(): string
    {
        return view('public/privacy');
    }
}
