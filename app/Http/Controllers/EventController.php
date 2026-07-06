<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventController extends Controller
{
    public function getEvents()
    {
        $events = Event::where('user_id', Auth::id())
            ->orWhere('shared', true)
            ->get();

        $data = [];

        foreach($events as $event){
            $start = $event->time
                ? $event->date.'T'.$event->time
                : $event->date;

            $data[] = [
                'id'          => $event->id,
                'title'       => $event->title,
                'start'       => $start,
                'description' => $event->description, // Essencial para o modal de edição
                'priority'    => $event->priority,
                'status'      => $event->status,      // Essencial para o modal de edição
                'time'        => $event->time,
                'date'        => $event->date,
                'shared'      => $event->shared       // Essencial para o modal de edição
            ];
        }

        return response()->json($data);
    }
    

    public function getTodayEvents()
    {
        $today = Carbon::today();

        $events = Event::where(function($query){
            $query->where('user_id', Auth::id())
                  ->orWhere('shared', true);
        })
        ->whereDate('date', $today)
        ->orderBy('time', 'asc')
        ->get();

        return response()->json($events);
    }

    public function store(Request $request)
    {
        Event::create([
            'user_id'     => Auth::id(),
            'title'       => $request->title,
            'description' => $request->description,
            'date'        => $request->date,
            'time'        => $request->time,
            'priority'    => $request->priority,
            'shared'      => $request->shared ?? false
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
{
    $event = Event::where('user_id', Auth::id())->findOrFail($id);

    $data = $request->only([
        'title',
        'description',
        'status',
        'date',
        'time',
        'priority',
        'shared'
    ]);

    $event->update(array_filter($data, function ($value) {
        return !is_null($value);
    }));

    return response()->json([
        'success' => true
    ]);
}

    public function destroy($id)
    {
        // Adicionada verificação de dono para segurança
        $event = Event::where('user_id', Auth::id())->findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    }
}