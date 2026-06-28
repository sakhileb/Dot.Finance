<?php

use App\Http\Controllers\Auth\EcosystemAuthController;
use Illuminate\Support\Facades\Route;


Route::get('/auth/ecosystem', [EcosystemAuthController::class, 'handle'])->name('ecosystem.auth');
Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $userId = auth()->id();

        $accounts = \App\Models\Account::where('user_id', $userId)->get();
        $totalBalance = $accounts->sum('balance');

        $thisMonth = now()->startOfMonth();
        $monthlyIncome = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->where('date', '>=', $thisMonth)
            ->sum('amount');
        $monthlyExpense = \App\Models\Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('date', '>=', $thisMonth)
            ->sum('amount');

        $recentTransactions = \App\Models\Transaction::where('user_id', $userId)
            ->with(['account', 'category'])
            ->latest('date')
            ->limit(10)
            ->get();

        $budgets = \App\Models\Budget::where('user_id', $userId)
            ->with('category')
            ->get();

        // AiInsight model not yet generated — placeholder until migration runs
        $unreadInsights = 0;

        return view('dashboard', compact(
            'accounts', 'totalBalance', 'monthlyIncome', 'monthlyExpense',
            'recentTransactions', 'budgets', 'unreadInsights'
        ));
    })->name('dashboard');
});
