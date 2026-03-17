<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Agenda
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

<!-- CALENDÁRIO -->

<div class="md:col-span-3 bg-white shadow rounded-xl p-6">
    <div id="calendar"></div>
</div>

<!-- COMPROMISSOS DO DIA -->
<div class="bg-white shadow rounded-xl p-6">

<h3 class="text-lg font-bold mb-4">
Hoje
</h3>

<div id="todayEvents" class="grid grid-cols-1 gap-3">

<div class="text-gray-400 text-sm">
Nenhum compromisso hoje
</div>

</div>

</div>

<!-- MODAL EVENTO -->

<div id="eventModal"
class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-modal">

<!-- HEADER -->
<div class="flex justify-between items-center mb-4">

<h3 class="text-lg font-semibold text-gray-800">
Compromisso
</h3>

<button class="closeModal text-gray-400 hover:text-red-500 text-xl">
✕
</button>

</div>

<!-- FORM -->

<div class="space-y-3">

<input id="eventId" type="hidden">
<input id="date" type="hidden">

<div>
<label class="text-sm text-gray-500">Título</label>
<input id="title"
placeholder="Nome do compromisso"
class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
</div>

<div>
<label class="text-sm text-gray-500">Descrição</label>
<textarea id="description"
placeholder="Descrição do evento"
rows="2"
class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500"></textarea>
</div>

<div class="grid grid-cols-2 gap-3">

<div>
<label class="text-sm text-gray-500">Hora</label>
<input type="time"
id="time"
class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
</div>

<div>
<label class="text-sm text-gray-500">Prioridade</label>
<select id="priority"
class="w-full border rounded-lg p-2">

<option value="normal">Normal</option>
<option value="urgent">Urgente</option>

</select>
</div>

</div>

<label class="flex items-center gap-2 mt-2">

<input type="checkbox"
id="shared">

<span class="text-sm text-gray-600">
Compartilhar evento
</span>

</label>

</div>

<!-- BOTÕES -->

<div class="flex justify-between mt-6">

<button id="deleteEvent"
class="px-4 py-2 border border-red-300 text-red-500 rounded-lg hover:bg-red-50">
Excluir
</button>

<div class="flex gap-2">

<button class="closeModal px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
Cancelar
</button>

<button id="saveEvent"
class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
Salvar
</button>

</div>

</div>

</div>
</div>

</div>
</div>

@vite(['resources/js/calendar.ts'])

<style>

@keyframes modalPop {

0%{
opacity:0;
transform:scale(.9) translateY(20px);
}

100%{
opacity:1;
transform:scale(1) translateY(0);
}

}

.animate-modal{
animation:modalPop .2s ease;
}

</style>

<script>

const modal = document.getElementById("eventModal")

/* FECHAR MODAL */

function closeModal(){
modal.classList.add("hidden")
}

/* BOTÕES FECHAR */

document.querySelectorAll(".closeModal").forEach(btn=>{
btn.addEventListener("click",closeModal)
})

/* CLICAR FORA DO MODAL */

modal.addEventListener("click",(e)=>{
if(e.target === modal){
closeModal()
}
})

/* TECLA ESC */

document.addEventListener("keydown",(e)=>{
if(e.key === "Escape"){
closeModal()
}
})

/* EVENTOS DO DIA */

function loadTodayEvents(){

fetch('/events/today')
.then(res => res.json())
.then(events => {

let container = document.getElementById("todayEvents")

container.innerHTML = ""

if(events.length === 0){

container.innerHTML = '<div class="text-gray-400 text-sm">Nenhum compromisso hoje</div>'
return

}

events.forEach(event => {

let div = document.createElement("div")

div.className = "border-b pb-2 text-sm"

let time = event.time ? event.time : ""

div.innerHTML = `
<div class="flex justify-between">
<span class="font-medium">${event.title}</span>
<span class="text-gray-500">${time}</span>
</div>
`

container.appendChild(div)

})

})

}

document.addEventListener("DOMContentLoaded", loadTodayEvents)

/* PERMISSÃO DE NOTIFICAÇÃO */

if ("Notification" in window) {

Notification.requestPermission()

}

/* ALERTA DE EVENTOS */

function checkEventNotifications(){

fetch('/events')
.then(res=>res.json())
.then(events=>{

let now = new Date()

events.forEach(event=>{

if(!event.time) return

let eventDateTime = new Date(event.start)

let diff = (eventDateTime - now) / 60000

if(diff > 0 && diff <= 10){

new Notification("Compromisso em breve 🔔",{

body: event.title + " começa às " + event.time

})

}

})

})

}

setInterval(checkEventNotifications,60000)

</script>

</x-app-layout>