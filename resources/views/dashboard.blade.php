<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Agenda
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

<!-- CALENDÁRIO -->

<div class="md:col-span-3 bg-white shadow rounded p-6">
    <div id="calendar"></div>
</div>

<!-- COMPROMISSOS DO DIA -->

<div class="bg-white shadow rounded p-6">

<h3 class="text-lg font-bold mb-4">
Hoje
</h3>

<div id="todayEvents" class="space-y-2 text-sm text-gray-700">

<div class="text-gray-400">
Nenhum compromisso hoje
</div>

</div>

</div>

</div>
</div>
</div>

<!-- MODAL EVENTO -->

<div id="eventModal"
class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">

<div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">

<h3 class="text-lg font-bold mb-4">
Novo Compromisso
</h3>

<input id="title"
placeholder="Título"
class="w-full border p-2 mb-3 rounded">

<textarea id="description"
placeholder="Descrição"
class="w-full border p-2 mb-3 rounded"></textarea>

<input type="time"
id="time"
class="w-full border p-2 mb-3 rounded">

<select id="priority"
class="w-full border p-2 mb-3 rounded">

<option value="normal">Normal</option>
<option value="urgent">Urgente</option>

</select>

<label class="flex items-center mt-3">

<input type="checkbox"
id="shared"
class="mr-2">

<span class="text-sm text-gray-700">
Compartilhar evento
</span>

</label>

<input type="hidden" id="date">
<input type="hidden" id="eventId">

<div class="flex justify-between mt-6">

<button id="saveEvent"
class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
Salvar </button>

<button id="deleteEvent"
class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
Excluir </button>

<button id="closeModal"
class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
Cancelar </button>

</div>

</div>

</div>

@vite(['resources/js/calendar.ts'])

<script>

function loadTodayEvents(){

fetch('/events/today')
.then(res => res.json())
.then(events => {

let container = document.getElementById("todayEvents")

container.innerHTML = ""

if(events.length === 0){

container.innerHTML = '<div class="text-gray-400">Nenhum compromisso hoje</div>'
return

}

events.forEach(event => {

let div = document.createElement("div")

div.className = "border-b pb-2"

let time = event.time ? event.time : ""

div.innerHTML = `
<div class="flex justify-between">
<span>${event.title}</span>
<span class="text-gray-500">${time}</span>
</div>
`

container.appendChild(div)

})

})

}

document.addEventListener("DOMContentLoaded", loadTodayEvents)

</script>

</x-app-layout>
