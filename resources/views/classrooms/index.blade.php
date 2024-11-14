<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Classroom List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Success and error alerts -->
                    @if(session('success'))
                        <div class="alert alert-success" id="successAlert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" id="errorAlert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Table to show classrooms -->
                    <div class="table-responsive">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="text-left text-gray-600 dark:text-gray-400">
                                    <th class="px-6 py-3 text-sm font-medium">Classroom Name</th>
                                    <th class="px-6 py-3 text-sm font-medium">Description</th>
                                    <th class="px-6 py-3 text-sm font-medium center-icon">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="classroom-list" class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($classrooms as $classroom)
                                    <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 cursor-pointer" onclick="window.location='{{ route('classrooms.show', $classroom->id) }}';"><b>{{ $classroom->name }}</b></td>
                                        <td class="px-6 py-4 cursor-pointer" onclick="window.location='{{ route('classrooms.show', $classroom->id) }}';">{{ $classroom->description }}</td>
                                        <td class="px-6 py-4 center-icon flex space-x-2">
                                            <!-- Edit button -->
                                            <button class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" onclick="openEditForm('{{ $classroom->id }}', '{{ $classroom->name }}', '{{ $classroom->description }}')">
                                                <span class="material-icons">edit</span>
                                            </button>
                                            {{-- delete --}}
                                            <form action="{{ route('classrooms.destroy', ['id' => $classroom->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this classroom?')">
                                                    <span class="material-icons">delete_forever</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Button to open the popup form for creating a new classroom -->
                    <div class="mt-4 flex justify-start space-x-2">
                        <a id="openFormButton" href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create New Classroom</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form for creating a new classroom -->
    <div id="popupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form action="{{ route('classrooms.store') }}" method="POST" class="p-10">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-lg font-medium text-gray-700">Classroom Name:</label>
                    <input type="text" name="name" id="name" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-lg font-medium text-gray-700">Description:</label>
                    <textarea name="description" id="description" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Classroom</button>
                    <button id="closeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Form for editing a classroom -->
    <div id="editPopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form id="editClassroomForm" method="POST" class="p-10">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="editName" class="block text-lg font-medium text-gray-700">Classroom Name:</label>
                    <input type="text" name="name" id="editName" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label for="editDescription" class="block text-lg font-medium text-gray-700">Description:</label>
                    <textarea name="description" id="editDescription" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Update Classroom</button>
                    <button id="closeEditFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
//     let currentMessages = []; // Store current state of messages

// function fetchMessages() {
//     $.get('/groups/messages', function(groups) {
//         // Only update if there are changes
//         if (JSON.stringify(currentMessages) !== JSON.stringify(groups)) {
//             updateMessagesTable(groups);
//             currentMessages = groups;
//         }
//     });
// }

// function updateMessagesTable(groups) {
//     const tbody = $('#messages-list');
//     tbody.empty(); // Clear the existing content first

//     groups.forEach(function(group) {
//         const groupItem = `
//             <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
//                 <td class="px-6 py-4">${escapeHtml(group.name)}</td>
//                 <td class="px-6 py-4">
//                     <ul>
//                         ${group.assignments.map(assignment => `
//                             <li>${escapeHtml(assignment.title)} - Assigned to: ${escapeHtml(assignment.user.name)}</li>
//                         `).join('')}
//                     </ul>
//                 </td>
//                 <td class="px-6 py-4 center-icon flex space-x-2">
//                     <button class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" onclick="openEditForm('${group.id}', '${escapeHtml(group.name)}')">
//                         <span class="material-icons">edit</span>
//                     </button>
//                     <form action="/groups/${group.id}" method="POST" style="display:inline;">
//                         @csrf @method('DELETE')
//                         <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this group?')">
//                             <span class="material-icons">delete_forever</span>
//                         </button>
//                     </form>
//                 </td>
//             </tr>
//         `;
//         tbody.append(groupItem);
//     });
// }

// function escapeHtml(unsafe) {
//     if (!unsafe) return '';
//     return unsafe
//         .replace(/&/g, "&amp;")
//         .replace(/</g, "&lt;")
//         .replace(/>/g, "&gt;")
//         .replace(/"/g, "&quot;")
//         .replace(/'/g, "&#039;");
// }

// // Fetch messages every 5 seconds
// setInterval(fetchMessages, 5000);

// // Initial fetch
// fetchMessages();

    // FORM
        function openForm() {
            document.getElementById('popupForm').classList.remove('hidden');
        }

        function closeForm() {
            document.getElementById('popupForm').classList.add('hidden');
        }

        function openEditForm(id, name, description) {
            document.getElementById('editPopupForm').classList.remove('hidden');
            document.getElementById('editClassroomForm').action = `/classrooms/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
        }

        function closeEditForm() {
            document.getElementById('editPopupForm').classList.add('hidden');
        }

        document.getElementById('openFormButton').addEventListener('click', openForm);
        document.getElementById('closeFormButton').addEventListener('click', closeForm);
        document.getElementById('closeEditFormButton').addEventListener('click', closeEditForm);

        // Automatically close success and error alerts after 5 seconds
        setTimeout(function() {
            if (document.getElementById('successAlert')) {
                document.getElementById('successAlert').remove();
            }
        }, 5000); // 5000 milliseconds = 5 seconds

        setTimeout(function() {
            if (document.getElementById('errorAlert')) {
                document.getElementById('errorAlert').remove();
            }
        }, 5000); // 5000 milliseconds = 5 seconds

    </script>
    <style>
    /* Add smooth transition for highlight effect */
    .highlight-update {
        transition: background-color 0.5s ease;
    }

    /* Optional: Add animation for updates */
    @keyframes highlightFade {
        from { background-color: rgba(255, 251, 235, 1); }
        to { background-color: rgba(255, 251, 235, 0); }
    }
</style>
</x-app-layout>
