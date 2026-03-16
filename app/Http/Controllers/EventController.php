<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

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
'time'=>$event->time
];

}

return response()->json($data);

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

public function update(Request $request,$id)
{

$event = Event::findOrFail($id);

$event->update([
'title'=>$request->title,
'description'=>$request->description,
'date'=>$request->date,
'time'=>$request->time,
'priority'=>$request->priority,
'shared'=>$request->shared ?? false
]);

return response()->json(['success'=>true]);

}

public function destroy($id)
{

Event::destroy($id);

return response()->json(['success'=>true]);

}

}