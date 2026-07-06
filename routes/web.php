<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth','verified'])->group(function () {

    Route::get('/dashboard', function () {
    $userId = Auth::id();

    // Contagem apenas dos seus eventos
    $totalEvents = Event::where('user_id', $userId)->count();
    
    $eventsThisMonth = Event::where('user_id', $userId)
        ->whereMonth('date', Carbon::now()->month)
        ->count();
        
    $pendingEvents = Event::where('user_id', $userId)
        ->where('status', 'pending') // Ajuste 'pending' se no seu banco for 'pendente'
        ->count();

    return view('dashboard', compact('totalEvents', 'eventsThisMonth', 'pendingEvents'));
})->middleware(['auth', 'verified'])->name('dashboard');

    // EVENTOS
    Route::get('/events',[EventController::class,'getEvents']);
    Route::post('/event',[EventController::class,'store']);
    Route::put('/event/{id}',[EventController::class,'update']);
    Route::delete('/event/{id}',[EventController::class,'destroy']);
    Route::get('/events/today',[EventController::class,'getTodayEvents']);
    
    // PREVISÃO DO TEMPO
    Route::get('/weather', function () {
        $apiKey = config('services.openweather.key', env('OPENWEATHER_API_KEY'));
        // Alterado para buscar Vitória, BR como padrão
        $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
            'q' => 'Vitoria,BR',
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => 'pt_br'
        ]);
        return $response->json();
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';