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
'id'=>$event->id,
'title'=>$event->title,
'start'=>$start,
'priority'=>$event->priority,
'time'=>$event->time,
'date'=>$event->date
];

}

return response()->json($data);

}

public function getTodayEvents()
{

$today = Carbon::today();

$events = Event::where(function($query){
$query->where('user_id',Auth::id())
->orWhere('shared',true);
})
->whereDate('date',$today)
->orderBy('time','asc')
->get();

return response()->json($events);

}

public function store(Request $request)
{

Event::create([
'user_id'=>Auth::id(),
'title'=>$request->title,
'description'=>$request->description,
'date'=>$request->date,
'time'=>$request->time,
'priority'=>$request->priority,
'shared'=>$request->shared ?? false
]);

return response()->json(['success'=>true]);

}

public function update(Request $request, $id)
{
    $event = Event::findOrFail($id);

    // Usamos o operador ?? para manter o valor atual caso o request não traga o novo
    $event->update([
        'title'       => $request->title       ?? $event->title,
        'description' => $request->description ?? $event->description,
        'status'      => $request->status      ?? $event->status,
        'date'        => $request->date        ?? $event->date,
        'time'        => $request->time        ?? $event->time,
        'priority'    => $request->priority    ?? $event->priority,
        'shared'      => $request->has('shared') ? $request->shared : $event->shared
    ]);

    return response()->json(['success' => true]);
}

public function destroy($id)
{

Event::destroy($id);

return response()->json(['success'=>true]);

}



}