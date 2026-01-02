<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\CompanyUsers;
class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $auth_user = auth()->user();
        $companies = $auth_user->companies;
        $current_company = $companies->first()->company_id ?? null;
        $user_role = CompanyUsers::where('company_id', $current_company)->where('user_id', $auth_user->id)->first()->role ?? null;
        $current_company_data = Company::where('id', $current_company)->first();
        if (!isset($current_company) ) {
            return redirect()->route('companies.create')->with('error', 'Please create a company first');
        }
        session([
            'current_company_id' => $current_company,
            'current_role' => $user_role,
            'current_company_data' => $current_company_data,
            'code' => $current_company_data->code
        ]);

        return view('welcome');
    }
}
