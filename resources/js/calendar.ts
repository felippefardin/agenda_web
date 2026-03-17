import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

document.addEventListener('DOMContentLoaded', () => {

    

    /* --- LÓGICA DO RELÓGIO EM TEMPO REAL --- */
    const headerTitle = document.querySelector('.font-semibold.text-xl');
    if (headerTitle && !document.getElementById('live-clock')) {
        const clockDiv = document.createElement('div');
        clockDiv.id = 'live-clock';
        clockDiv.style.cssText = 'font-family: monospace; font-size: 1.2rem; background: #1f2937; color: white; padding: 4px 12px; border-radius: 8px; margin-left: 15px; display: inline-block; vertical-align: middle;';
        headerTitle.appendChild(clockDiv);

        const updateClock = () => {
            const now = new Date();
            clockDiv.textContent = now.toLocaleTimeString('pt-BR');
        };
        setInterval(updateClock, 1000);
        updateClock();
    }

    const calendarEl = document.getElementById('calendar')
    if (!calendarEl) return

    const modal = document.getElementById('eventModal') as HTMLElement
    const dateInput = document.getElementById('date') as HTMLInputElement
    const eventIdInput = document.getElementById('eventId') as HTMLInputElement
    const statusInput = document.getElementById('status') as HTMLSelectElement

    const closeFloatingCards = () => {
        document.querySelectorAll('.event-floating-card').forEach(el => el.remove());
    };

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        initialDate: new Date(),
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today dayGridMonth,timeGridWeek'
        },
        events: '/events',

        editable: true,
        eventStartEditable: true,
        eventDurationEditable: true,

        dateClick: function (info) {
            closeFloatingCards();
            eventIdInput.value = "";
            dateInput.value = info.dateStr;
            (document.getElementById("title") as HTMLInputElement).value = "";
            (document.getElementById("description") as HTMLInputElement).value = "";
            (document.getElementById("time") as HTMLInputElement).value = "";
            (document.getElementById("priority") as HTMLSelectElement).value = "normal";
            (document.getElementById("shared") as HTMLInputElement).checked = false;
            if (statusInput) statusInput.value = "Pendente";
            
            modal.classList.remove("hidden");
        },

        eventClick: function (info) {
            closeFloatingCards();
            const event = info.event;
            const props = event.extendedProps;
            const color = props.priority === "urgent" ? "#ef4444" : "#3b82f6";
            
            const card = document.createElement('div');
            card.className = 'event-floating-card';
            card.style.cssText = `
                position: absolute; z-index: 1000; background: white; padding: 15px;
                border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                width: 250px; border-left: 5px solid ${color};
            `;
            
            card.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <strong style="color:#374151;">${event.title}</strong>
                    <button class="close-card" style="cursor:pointer; color:#9ca3af; font-size:18px;">&times;</button>
                </div>
                <div style="font-size:13px; color:#4b5563; margin-bottom:12px;">
                    <p><strong>Status:</strong> ${props.status || 'Pendente'}</p>
                    <p><strong>Descrição:</strong> ${props.description || 'Sem descrição'}</p>
                </div>
                <button id="openEditModal" style="width:100%; background:#f3f4f6; border:none; padding:8px; border-radius:5px; cursor:pointer; font-size:12px; font-weight:bold;">
                    Editar Detalhes
                </button>
            `;

            document.body.appendChild(card);
            card.style.top = (info.jsEvent.pageY + 10) + 'px';
            card.style.left = (info.jsEvent.pageX + 10) + 'px';

            card.querySelector('.close-card')?.addEventListener('click', () => card.remove());

            card.querySelector('#openEditModal')?.addEventListener('click', () => {
                card.remove();
                eventIdInput.value = String(event.id);
                (document.getElementById("title") as HTMLInputElement).value = event.title;
                (document.getElementById("description") as HTMLInputElement).value = props.description || "";
                (document.getElementById("time") as HTMLInputElement).value = props.time || "";
                (document.getElementById("priority") as HTMLSelectElement).value = props.priority || "normal";
                if (statusInput) statusInput.value = props.status || "Pendente";
                (document.getElementById("shared") as HTMLInputElement).checked = props.shared == 1;
                
                dateInput.value = event.startStr.split('T')[0];
                modal.classList.remove("hidden");
            });

            info.jsEvent.preventDefault();
        },

        /* --- AJUSTE: ATUALIZAÇÃO AO ARRASTAR --- */
        eventDrop: function (info) {
            const event = info.event;
            const newDate = event.startStr.split('T')[0];
            const newTime = event.startStr.includes('T') ? event.startStr.split('T')[1].substring(0, 5) : event.extendedProps.time;

            fetch('/event/' + event.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
                },
                body: JSON.stringify({ 
                    date: newDate,
                    time: newTime
                })
            })
            .then(response => {
                if (!response.ok) throw new Error();
            })
            .catch(() => {
                alert("Erro ao mover evento");
                info.revert();
            });
        },

        /* --- AJUSTE: ATUALIZAÇÃO AO REDIMENSIONAR --- */
        eventResize: function(info) {
            const event = info.event;
            const newDate = event.startStr.split('T')[0];

            fetch('/event/' + event.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
                },
                body: JSON.stringify({ 
                    date: newDate 
                })
            })
            .then(response => {
                if (!response.ok) throw new Error();
            })
            .catch(() => {
                alert("Erro ao atualizar duração");
                info.revert();
            });
        },

        eventDidMount: function (info) {
            if (info.event.extendedProps.priority === "urgent") {
                info.el.style.backgroundColor = "#ef4444"
            } else if (info.event.extendedProps.priority === "high") {
                info.el.style.backgroundColor = "#f59e0b"
            } else {
                info.el.style.backgroundColor = "#3b82f6"
            }
        }
    })

    calendar.render()

    /* FECHAR CARDS AO CLICAR FORA */
    document.addEventListener('click', (e) => {
        if (!(e.target as HTMLElement).closest('.event-floating-card') && !(e.target as HTMLElement).closest('.fc-event')) {
            closeFloatingCards();
        }
    });

    /* SALVAR EVENTO (Edição e Criação) */
document.getElementById("saveEvent")?.addEventListener("click", () => {
    let id = eventIdInput.value;
    
    let data = {
        title: (document.getElementById("title") as HTMLInputElement).value,
        description: (document.getElementById("description") as HTMLInputElement).value,
        date: dateInput.value,
        time: (document.getElementById("time") as HTMLInputElement).value,
        priority: (document.getElementById("priority") as HTMLSelectElement).value,
        status: statusInput ? statusInput.value : 'Pendente',
        // Converter booleano para 1 ou 0 para evitar problemas no PHP/MySQL
        shared: (document.getElementById("shared") as HTMLInputElement).checked ? 1 : 0 
    };

    let url = id && id !== "" ? '/event/' + id : '/event';
    let method = id && id !== "" ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json', // Força o Laravel a responder JSON em caso de erro
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const result = await response.json();
        if (!response.ok) {
            console.error("Erro do Servidor:", result);
            throw new Error(result.message || "Erro desconhecido");
        }
        return result;
    })
    .then(() => {
        modal.classList.add("hidden");
        calendar.refetchEvents();
    })
    .catch(error => {
        console.error("Erro na requisição:", error);
        alert("Erro ao salvar: " + error.message);
    });
});
    

    /* EXCLUIR EVENTO */
    document.getElementById("deleteEvent")?.addEventListener("click", () => {
        let id = eventIdInput.value
        if (!id) return
        if (!confirm("Deseja excluir este evento?")) return

        fetch('/event/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
            }
        })
        .then(() => {
            modal.classList.add("hidden")
            calendar.refetchEvents()
        })
    })

    /* FECHAR MODAL */
    document.getElementById("closeModal")?.addEventListener("click", () => {
        modal.classList.add("hidden")
    })

    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.classList.add("hidden")
        }
    })

    
});
