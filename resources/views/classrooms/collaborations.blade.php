<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Collaborated Classrooms') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($classrooms->isEmpty())
                        <p class="text-gray-700 dark:text-gray-300">You don't have any classroom associated yet. Please ask your lecturer to be added</p>
                    @else
                        <div class="table-responsive">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr class="text-left text-gray-600 dark:text-gray-400">
                                        <th class="px-6 py-3 text-sm font-medium">Classroom Name</th>
                                        <th class="px-6 py-3 text-sm font-medium">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($classrooms as $classroom)
                                        <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
                                        onclick="window.location='{{ route('classrooms.show', $classroom->id) }}';">
                                            <td class="px-6 py-4 cursor-pointer">{{ $classroom->name }}</td>
                                            <td class="px-6 py-4 cursor-pointer">{{ $classroom->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
