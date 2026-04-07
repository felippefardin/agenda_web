import { Calendar } from '@fullcalendar/core'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'

document.addEventListener('DOMContentLoaded', () => {
    // CAPTURA A URL BASE DO PROJETO DA META TAG (Essencial para subpastas no XAMPP)
    const APP_URL = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || '';

    /* --- FLASHCARD SYSTEM --- */
    const showFlash = (message: string, type: string = "success") => {
        const container = document.getElementById("flash-container");
        if (!container) return;

        const flash = document.createElement("div");
        const bg = type === "error" ? "#ef4444" : "#10b981";
        const icon = type === "error" ? "🗑" : "📅";

        flash.style.cssText = `
            background:${bg};
            color:white;
            padding:12px 18px;
            border-radius:8px;
            font-size:14px;
            box-shadow:0 5px 20px rgba(0,0,0,0.2);
            display:flex;
            align-items:center;
            gap:10px;
            animation:slideIn .3s ease;
        `;

        flash.innerHTML = `${icon} ${message}`;
        container.appendChild(flash);

        setTimeout(() => {
            flash.style.opacity = "0";
            flash.style.transform = "translateY(-10px)";
            flash.style.transition = "all .3s";
            setTimeout(() => flash.remove(), 300);
        }, 3000);
    }

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

    /* --- CONFIGURAÇÃO DO CALENDÁRIO --- */
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
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today dayGridMonth,timeGridWeek'
        },
        events: `${APP_URL}/events`, 

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
                (document.getElementById("shared") as HTMLInputElement).checked = props.shared == 1;
                dateInput.value = event.startStr.split('T')[0];
                modal.classList.remove("hidden");
            });

            info.jsEvent.preventDefault();
        },

        eventDrop: function (info) {
            const event = info.event;
            fetch(`${APP_URL}/event/${event.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
                },
                body: JSON.stringify({ 
                    date: event.startStr.split('T')[0],
                    time: event.startStr.includes('T') ? event.startStr.split('T')[1].substring(0, 5) : event.extendedProps.time
                })
            })
            .then(res => { if (!res.ok) throw new Error(); })
            .catch(() => info.revert());
        },

        eventDidMount: function (info) {
            const p = info.event.extendedProps.priority;
            info.el.style.backgroundColor = p === "urgent" ? "#ef4444" : (p === "high" ? "#f59e0b" : "#3b82f6");
        }
    })

    calendar.render()

    /* SALVAR EVENTO (Edição e Criação) */
    document.getElementById("saveEvent")?.addEventListener("click", () => {
        let id = eventIdInput.value;
        let data = {
            title: (document.getElementById("title") as HTMLInputElement).value,
            description: (document.getElementById("description") as HTMLInputElement).value,
            date: dateInput.value,
            time: (document.getElementById("time") as HTMLInputElement).value,
            priority: (document.getElementById("priority") as HTMLSelectElement).value,
            shared: (document.getElementById("shared") as HTMLInputElement).checked ? 1 : 0 
        };

        let url = id && id !== "" ? `${APP_URL}/event/${id}` : `${APP_URL}/event`;
        let method = id && id !== "" ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || "Erro");
            
            modal.classList.add("hidden");
            calendar.refetchEvents();
            showFlash(id ? "Compromisso editado" : "Compromisso adicionado");
        })
        .catch(err => alert("Erro ao salvar: " + err.message));
    });

    /* EXCLUIR EVENTO */
    document.getElementById("deleteEvent")?.addEventListener("click", () => {
        let id = eventIdInput.value;
        if (!id || !confirm("Deseja realmente excluir?")) return;

        fetch(`${APP_URL}/event/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement).content,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (res.ok) {
                modal.classList.add("hidden");
                calendar.refetchEvents();
                showFlash('Compromisso excluído', 'error');
            }
        });
    });

    document.querySelectorAll(".closeModal").forEach(btn => {
        btn.addEventListener("click", () => modal.classList.add("hidden"));
    });
});