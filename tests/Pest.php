<?php

use App\Models\CompanyUsers;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createProject($userId = null, $companyId = null, $status = 1, $priority = 1): Project
{
    return Project::create([
        'name' => 'Personal Project',
        'slug' => 'personal-project',
        'theme' => '#ff0000',
        'status' => $status,
        'priority' => $priority,
        'user_id' => $userId,
        'company_id' => $companyId,
    ]);
}

function createCompanyUser($cid, $uid, $role = 0, $is_approved = true)
{
    return
    CompanyUsers::create([
        'company_id' => $cid,
        'user_id' => $uid,
        'role' => $role,
        'is_approved' => $is_approved,
    ]);
}
