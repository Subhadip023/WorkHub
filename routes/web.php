<! <?php
// use App\Http\Controllers\CompanyController;

// Route::get('/', function () { return redirect('/companies'); });

// Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
// Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
// Route::post('/companies/store', [CompanyController::class, 'store'])->name('companies.store');
// Route::get('/companies/edit/{id}', [CompanyController::class, 'edit'])->name('companies.edit');
// Route::post('/companies/update/{id}', [CompanyController::class, 'update'])->name('companies.update');
// Route::post('/companies/delete/{id}', [CompanyController::class, 'destroy'])->name('companies.delete'); -->
use App\Http\Controllers\TaskController;

Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
