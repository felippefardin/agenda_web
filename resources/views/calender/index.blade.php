<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Minha Agenda') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">Compromisso</h3>
            
            <input id="title" placeholder="Título" class="w-full border p-2 mb-3 rounded">
            <textarea id="description" placeholder="Descrição" class="w-full border p-2 mb-3 rounded"></textarea>
            <input type="time" id="time" class="w-full border p-2 mb-3 rounded">
            
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                <select id="priority" class="w-full border p-2 rounded">
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" class="w-full border p-2 rounded">
                    <option value="Pendente">Pendente ⏳</option>
                    <option value="Em Andamento">Em Andamento 🚀</option>
                    <option value="Concluído">Concluído ✅</option>
                </select>
            </div>

            <label class="flex items-center mt-3">
                <input type="checkbox" id="shared" class="mr-2">
                <span class="text-sm text-gray-700">Compartilhar evento</span>
            </label>

            <input type="hidden" id="date">
            <input type="hidden" id="eventId">

            <div class="flex justify-between mt-6">
                <button id="saveEvent" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Salvar</button>
                <button id="deleteEvent" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Excluir</button>
                <button id="closeModal" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
            </div>
        </div>
    </div>
</x-app-layout>