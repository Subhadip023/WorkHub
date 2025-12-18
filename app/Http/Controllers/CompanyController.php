<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    // LIST COMPANIES WITH PAGINATION
    public function index()
    {
        $companies = Company::orderBy('id','desc')->paginate(5); // 5 per page
        return view('companies.index', compact('companies'));
    }

    // SHOW CREATE FORM
    public function create()
    {
        return view('companies.create');
    }

    // STORE COMPANY
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:companies',
            'phone' => 'nullable',
            'address' => 'nullable',
        ]);

        Company::create($request->all());

        return redirect()->route('companies.index')->with('success', 'Company added successfully');
    }

    // SHOW EDIT FORM
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.edit', compact('company'));
    }

    // UPDATE COMPANY
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:companies,email,' . $id,
        ]);

        $company->update($request->all());

        return redirect()->route('companies.index')->with('success', 'Company updated successfully');
    }

    // DELETE COMPANY (POST method)
    public function destroy(Request $request, $id)
    {
        Company::findOrFail($id)->delete();
        return redirect()->route('companies.index')->with('success', 'Company deleted successfully');
    }
}
