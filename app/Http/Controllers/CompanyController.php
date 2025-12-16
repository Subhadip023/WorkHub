<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $owner = User::create([
            'name' => $request->owner_name,
            'email' => $request->email,
            'password' => bcrypt('password'),
            'role' => 'owner'
        ]);

        Company::create([
            'name' => $request->company_name,
            'owner_id' => $owner->id
        ]);

        return redirect()->route('company.create')->with('success', 'Company Created');
    }
}

