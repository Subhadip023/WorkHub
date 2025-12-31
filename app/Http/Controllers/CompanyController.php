<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
{
    // If user already belongs to a company → go to projects
    if (Auth::user()->company_id) {
        return redirect('/projects');
    }

    // Else show company create / join page
    return view('company.index');
}


    public function store(Request $request)
    {
        
        if (Auth::user()->company_id) {
            abort(403);
        }

        $request->validate(['name' => 'required']);

        $company = Company::create([
            'name' => $request->name,
            'join_code' => Str::upper(Str::random(6)),
            'created_by' => Auth::id(),
        ]);

        Auth::user()->update([
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        return redirect('/projects');
    }

    public function join(Request $request)

    {
        // dd($request->all());
        $request->validate(['join_code' => 'required']);

        $company = Company::where('join_code', $request->join_code)->firstOrFail();

        Auth::user()->update([
            'company_id' => $company->id,
            'role' => 'member',
        ]);

        return redirect('/projects');
    }
}
