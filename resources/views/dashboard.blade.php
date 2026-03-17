<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

    <div id="flash-container" class="fixed top-5 right-5 z-[2000] flex flex-col gap-3"></div>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agenda
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <div class="md:col-span-3 bg-white shadow rounded-xl p-6">
                    <div id="calendar"></div>
                </div>

                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-clock text-blue-600"></i> Hoje
                    </h3>
                    <div id="todayEvents" class="space-y-3">
                        <div class="text-gray-400 text-sm italic">Carregando compromissos...</div>
                    </div>
                </div>

                <div id="eventModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-modal">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Compromisso</h3>
                            <button class="closeModal text-gray-400 hover:text-red-500 text-xl">✕</button>
                        </div>

                        <div class="space-y-3">
                            <input id="eventId" type="hidden">
                            <input id="date" type="hidden">
                            <div>
                                <label class="text-sm text-gray-500 font-medium">Título</label>
                                <input id="title" placeholder="Nome do compromisso" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 border-gray-300">
                            </div>
                            <div>
                                <label class="text-sm text-gray-500 font-medium">Descrição</label>
                                <textarea id="description" placeholder="Descrição do evento" rows="2" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 border-gray-300"></textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm text-gray-500 font-medium">Hora</label>
                                    <input type="time" id="time" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 border-gray-300">
                                </div>
                                <div>
                                    <label class="text-sm text-gray-500 font-medium">Prioridade</label>
                                    <select id="priority" class="w-full border rounded-lg p-2 border-gray-300">
                                        <option value="normal">Normal</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" id="shared" class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-600">Compartilhar evento</span>
                            </label>
                        </div>

                        <div class="flex justify-between mt-6">
                            <button id="deleteEvent" class="px-4 py-2 border border-red-300 text-red-500 rounded-lg hover:bg-red-50 flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-trash-can"></i> Excluir
                            </button>
                            <div class="flex gap-2">
                                <button class="closeModal px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">Cancelar</button>
                                <button id="saveEvent" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition-colors">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @vite(['resources/js/calendar.ts'])

    <style>
        /* Cards Flutuantes da Barra Lateral */
        .sidebar-event-card {
            background: white;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-left: 5px solid #3b82f6;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
        }
        .sidebar-event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .priority-urgent { border-left-color: #ef4444 !important; }
        .priority-high { border-left-color: #f59e0b !important; }

        /* Animações */
        @keyframes modalPop {
            0% { opacity: 0; transform: scale(.9) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-modal { animation: modalPop .2s ease; }

        @keyframes bounce-in {
            0% { transform: translateX(100%); opacity: 0; }
            70% { transform: translateX(-10%); }
            100% { transform: translateX(0); opacity: 1; }
        }
        .animate-bounce-in { animation: bounce-in 0.5s ease-out; }
    </style>

    <script>
        const modal = document.getElementById("eventModal");
        function closeModal() { modal.classList.add("hidden"); }
        document.querySelectorAll(".closeModal").forEach(btn => btn.addEventListener("click", closeModal));

        /* ATUALIZAR BARRA LATERAL COM CARDS */
        function loadTodayEvents() {
            fetch('/events/today')
                .then(res => res.json())
                .then(events => {
                    let container = document.getElementById("todayEvents");
                    container.innerHTML = "";
                    if (events.length === 0) {
                        container.innerHTML = '<div class="text-gray-400 text-sm italic">Nenhum compromisso hoje</div>';
                        return;
                    }
                    events.forEach(event => {
                        let div = document.createElement("div");
                        div.className = `sidebar-event-card priority-${event.priority}`;
                        div.innerHTML = `
                            <div class="flex justify-between items-start">
                                <span class="font-bold text-gray-800 text-sm">${event.title}</span>
                                <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded text-gray-600">${event.time || '--:--'}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 line-clamp-2">${event.description || 'Sem descrição'}</p>
                        `;
                        container.appendChild(div);
                    });
                });
        }
        document.addEventListener("DOMContentLoaded", loadTodayEvents);
    </script>
</x-app-layout>