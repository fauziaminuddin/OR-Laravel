<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
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
                    <div class="table-responsive">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="text-left text-gray-600 dark:text-gray-400">
                                    <th class="px-6 py-3 text-sm font-medium">Dashboard Name</th>
                                    <th class="px-6 py-3 text-sm font-medium">Created On</th>
                                    <th class="px-6 py-3 text-sm font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($dashboards as $dashboard)
                                    <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                        <td class="px-6 py-4 cursor-pointer" onclick="window.location='{{ route('dashboards.show', $dashboard->id) }}';">{{ $dashboard->name }}</td>
                                        <td class="px-6 py-4">{{ $dashboard->created_at->setTimezone('Asia/Jakarta')->toDateTimeString() }}</td>
                                        <td class="px-6 py-4 flex space-x-2">
                                            <!-- Edit Button -->
                                            <button onclick="editDashboard({{ $dashboard->id }}, '{{ $dashboard->name }}')" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                <span class="material-icons">
                                                    edit
                                                </span>
                                            </button>
                                            <!-- Delete Button -->
                                            <form action="{{ route('dashboards.destroydash', $dashboard->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this dashboard?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this asset?')">
                                                    <span class="material-icons">delete_forever</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-md font-medium text-gray-700 dark:text-gray-100"><em>You could use Dashboard to visualize data that sent to your attribute's project</em></p>
                    <br>
                    <div class="mb-4">
                        <button id="openFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create New Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form for Creating Dashboard -->
    <div id="popupForm" class="fixed inset-0 items-center flex justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form action="{{ route('dashboards.store') }}" method="POST" class="p-10">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-lg font-medium text-gray-700">Dashboard Name:</label>
                    <input type="text" name="name" id="name" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Dashboard</button>
                    <button id="closeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Form for Editing Dashboard -->
    <div id="editPopupForm" class="fixed inset-0 items-center flex justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form id="editForm" method="POST" class="p-10">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="editName" class="block text-lg font-medium text-gray-700">Dashboard Name:</label>
                    <input type="text" name="name" id="editName" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Dashboard</button>
                    <button id="closeEditFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Popup Forms -->
    <script>
        function openForm() {
            document.getElementById('popupForm').classList.remove('hidden');
        }

        function closeForm() {
            document.getElementById('popupForm').classList.add('hidden');
        }

        function editDashboard(id, name) {
            document.getElementById('editName').value = name;
            document.getElementById('editForm').action = '/dashboards/' + id;
            document.getElementById('editPopupForm').classList.remove('hidden');
        }

        function closeEditForm() {
            document.getElementById('editPopupForm').classList.add('hidden');
        }

        document.getElementById('openFormButton').addEventListener('click', openForm);
        document.getElementById('closeFormButton').addEventListener('click', closeForm);
        document.getElementById('closeEditFormButton').addEventListener('click', closeEditForm);

        // Automatically close success and error alerts after 5 seconds
        setTimeout(function() {
            document.getElementById('successAlert')?.remove();
        }, 5000); // 5000 milliseconds = 5 seconds

        setTimeout(function() {
            document.getElementById('errorAlert')?.remove();
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</x-app-layout>
