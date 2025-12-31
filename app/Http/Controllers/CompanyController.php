<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'join_code' => strtoupper(uniqid()),
            'created_by' => Auth::id(),
        ]);

        Auth::user()->update([
            'company_id' => $company->id,
        ]);

        return redirect()->route('projects.index');
    }

    public function join(Request $request)
    {
        $request->validate([
            'join_code' => 'required',
        ]);

        $company = Company::where('join_code', $request->join_code)->firstOrFail();

        Auth::user()->update([
            'company_id' => $company->id,
        ]);

        return redirect()->route('projects.index');
    }
}
