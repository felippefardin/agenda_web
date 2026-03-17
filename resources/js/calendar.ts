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

        dateClick: function (info) {
            closeFloatingCards();
            eventIdInput.value = "";
            dateInput.value = info.dateStr;
            (document.getElementById("title") as HTMLInputElement).value = "";
            (document.getElementById("description") as HTMLInputElement).value = "";
            (document.getElementById("time") as HTMLInputElement).value = "";
            if (statusInput) statusInput.value = "Pendente";
            
            modal.classList.remove("hidden");
        },

        eventClick: function (info) {
            closeFloatingCards();
            const event = info.event;
            const color = event.extendedProps.priority === "urgent" ? "#ef4444" : "#3b82f6";
            
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
                    <p><strong>Status:</strong> ${event.extendedProps.status || 'Pendente'}</p>
                    <p><strong>Descrição:</strong> ${event.extendedProps.description || 'Sem descrição'}</p>
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
                (document.getElementById("description") as HTMLInputElement).value = event.extendedProps.description || "";
                (document.getElementById("time") as HTMLInputElement).value = event.extendedProps.time || "";
                (document.getElementById("priority") as HTMLSelectElement).value = event.extendedProps.priority || "normal";
                if (statusInput) statusInput.value = event.extendedProps.status || "Pendente";
                dateInput.value = event.start?.toISOString().slice(0, 10) ?? "";
                modal.classList.remove("hidden");
            });
            info.jsEvent.preventDefault();
        },

        eventDrop: function (info) {
            let date = info.event.start?.toISOString().slice(0, 10)
            fetch('/event/' + info.event.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
                },
                body: JSON.stringify({ date: date })
            })
        },

        eventDidMount: function (info) {
            if (info.event.extendedProps.priority === "urgent") {
                info.el.style.backgroundColor = "#ef4444"
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

    /* SALVAR EVENTO */
    document.getElementById("saveEvent")?.addEventListener("click", () => {
        let id = eventIdInput.value
        let data = {
            title: (document.getElementById("title") as HTMLInputElement).value,
            description: (document.getElementById("description") as HTMLInputElement).value,
            date: dateInput.value,
            time: (document.getElementById("time") as HTMLInputElement).value,
            priority: (document.getElementById("priority") as HTMLSelectElement).value,
            status: statusInput ? statusInput.value : 'Pendente',
            shared: (document.getElementById("shared") as HTMLInputElement).checked
        }

        let url = id && id !== "" ? '/event/' + id : '/event';
        let method = id && id !== "" ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(() => {
            modal.classList.add("hidden")
            calendar.refetchEvents()
            location.reload()
        })
        .catch(error => {
            console.error("Erro ao salvar evento:", error)
        })
    })

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
            location.reload()
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