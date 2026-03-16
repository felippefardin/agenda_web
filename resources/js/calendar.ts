import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

document.addEventListener('DOMContentLoaded', () => {

const calendarEl = document.getElementById('calendar')

if (!calendarEl) return

const modal = document.getElementById('eventModal') as HTMLElement
const dateInput = document.getElementById('date') as HTMLInputElement
const eventIdInput = document.getElementById('eventId') as HTMLInputElement

const calendar = new Calendar(calendarEl, {

plugins:[
dayGridPlugin,
timeGridPlugin,
interactionPlugin
],

initialView:'dayGridMonth',
initialDate:new Date(),

locale:'pt-br',

headerToolbar:{
left:'prev,next',
center:'title',
right:'today dayGridMonth,timeGridWeek'
},

events:'/events',

editable:true,

dateClick:function(info){

eventIdInput.value=""

dateInput.value=info.dateStr

;(document.getElementById("title") as HTMLInputElement).value=""
;(document.getElementById("description") as HTMLInputElement).value=""
;(document.getElementById("time") as HTMLInputElement).value=""

modal.classList.remove("hidden")

},

eventClick:function(info){

eventIdInput.value=String(info.event.id)

;(document.getElementById("title") as HTMLInputElement).value=info.event.title

dateInput.value=info.event.start?.toISOString().slice(0,10) ?? ""

modal.classList.remove("hidden")

},

eventDrop:function(info){

let date=info.event.start?.toISOString().slice(0,10)

fetch('/event/'+info.event.id,{
method:'PUT',
headers:{
'Content-Type':'application/json',
'X-CSRF-TOKEN':(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
},
body:JSON.stringify({
date:date
})
})

},

eventDidMount:function(info){

if(info.event.extendedProps.priority==="urgent"){

info.el.style.backgroundColor="#ef4444"

}else{

info.el.style.backgroundColor="#3b82f6"

}

}

})

calendar.render()

/* SALVAR EVENTO */

document.getElementById("saveEvent")?.addEventListener("click",()=>{

let id=eventIdInput.value

let data={

title:(document.getElementById("title") as HTMLInputElement).value,
description:(document.getElementById("description") as HTMLInputElement).value,
date:dateInput.value,
time:(document.getElementById("time") as HTMLInputElement).value,
priority:(document.getElementById("priority") as HTMLSelectElement).value,
shared:(document.getElementById("shared") as HTMLInputElement).checked

}

let url='/event'
let method='POST'

if(id && id !== ""){
url='/event/'+id
method='PUT'
}

fetch(url,{
method:method,
headers:{
'Content-Type':'application/json',
'X-CSRF-TOKEN':(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
},
body:JSON.stringify(data)
})
.then(response=>response.json())
.then(()=>{

modal.classList.add("hidden")

calendar.refetchEvents()

location.reload()

})
.catch(error=>{
console.error("Erro ao salvar evento:",error)
})

})

/* EXCLUIR EVENTO */

document.getElementById("deleteEvent")?.addEventListener("click",()=>{

let id=eventIdInput.value

if(!id) return

if(!confirm("Deseja excluir este evento?")) return

fetch('/event/'+id,{
method:'DELETE',
headers:{
'X-CSRF-TOKEN':(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
}
})
.then(()=>{

modal.classList.add("hidden")

calendar.refetchEvents()

location.reload()

})

})

/* FECHAR MODAL */

document.getElementById("closeModal")?.addEventListener("click",()=>{
modal.classList.add("hidden")
})

modal.addEventListener("click",(e)=>{

if(e.target===modal){

modal.classList.add("hidden")

}

})

})