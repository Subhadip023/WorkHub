<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\CompanyUsers;
use Illuminate\Http\Request;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('companies.join');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        if($request->has('code')) {
            dd($request->all());
        }
        $name = $request->input('name');
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
        } while (Company::where('code', $code)->exists());

        $company = Company::create([
            'name' => $name,
            'code' => $code,
        ]);
        $company_id = $company->id;
        $user_id = auth()->user()->id;
        CompanyUsers::create(['company_id' => $company_id, 'user_id' => $user_id, 'role' => 1,]);
        return redirect()->route('dashboard')->with('success', 'Company created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request)
    {
        dd($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }

    public function join(Request $request)
    {
        $request->validate(['code' => 'required|string|exists:companies,code']);
        $code = $request->input('code');
        $company = Company::where('code', $code)->first();
        if($company) {
            $company_id = $company->id;
            $user_id = auth()->user()->id;

            // Prevent double-joining and gracefully switch company
            $exists = CompanyUsers::where('company_id', $company_id)->where('user_id', $user_id)->exists();
            if ($exists) {
                session(['current_company_id' => $company_id]);
                return redirect()->route('dashboard')->with('info', "You are already a member of {$company->name}. Active company switched.");
            }

            CompanyUsers::create(['company_id' => $company_id, 'user_id' => $user_id, 'role' => 0,]);
            session(['current_company_id' => $company_id]);
            return redirect()->route('dashboard')->with('success', 'Company joined successfully');
        }
        return back()->with('error', 'Company not found');
    }

    /**
     * Switch active company.
     */
    public function switch(Company $company)
    {
        $user_id = auth()->user()->id;
        $belongs = CompanyUsers::where('company_id', $company->id)->where('user_id', $user_id)->exists();
        if (!$belongs) {
            abort(403);
        }

        session(['current_company_id' => $company->id]);
        return redirect()->route('dashboard')->with('success', "Switched to {$company->name}");
    }
}
