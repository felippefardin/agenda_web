<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Minha Agenda') }}
            </h2>
            <div id="live-clock" class="text-lg font-mono bg-gray-800 text-white px-4 py-1 rounded-lg shadow">
                00:00:00
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div id="eventModal"
class="fixed inset-0 hidden items-center justify-center bg-black/40 backdrop-blur-sm z-50">

<div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 animate-modal">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">
            Evento
        </h2>

        <button id="closeModal"
        class="text-gray-400 hover:text-red-500 text-xl">
        ✕
        </button>
    </div>

    <!-- FORM -->
    <div class="space-y-3">

        <input id="eventId" type="hidden">

        <div>
            <label class="text-sm text-gray-500">Título</label>
            <input id="title"
            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500"
            placeholder="Nome do evento">
        </div>

        <div>
            <label class="text-sm text-gray-500">Descrição</label>
            <textarea id="description"
            class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500"
            rows="2"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">

            <div>
                <label class="text-sm text-gray-500">Data</label>
                <input id="date" type="date"
                class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-sm text-gray-500">Hora</label>
                <input id="time" type="time"
                class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500">
            </div>

        </div>

        <div class="grid grid-cols-2 gap-3">

            <div>
                <label class="text-sm text-gray-500">Prioridade</label>
                <select id="priority"
                class="w-full border rounded-lg p-2">

                    <option value="normal">Normal</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente</option>

                </select>
            </div>

            <div>
                <label class="text-sm text-gray-500">Status</label>
                <select id="status"
                class="w-full border rounded-lg p-2">

                    <option>Pendente</option>
                    <option>Concluído</option>

                </select>
            </div>

        </div>

        <div class="flex items-center gap-2">
            <input id="shared" type="checkbox">
            <label class="text-sm text-gray-600">
                Compartilhar evento
            </label>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="flex justify-between mt-6">

        <button id="deleteEvent"
        class="px-4 py-2 text-red-500 border border-red-200 rounded-lg hover:bg-red-50">
            Excluir
        </button>

        <button id="saveEvent"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow">
            Salvar
        </button>

    </div>

</div>
</div>

    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('pt-BR');
            document.getElementById('live-clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</x-app-layout>