<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Access Key') }}
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
                                <tr class="text-left text-gray-600 dark:text-gray-400" >
                                    <th class="px-6 py-3 text-sm font-medium">Access Key Name</th>
                                    <th class="px-6 py-3 text-sm font-medium">Secret</th>
                                    <th class="px-6 py-3 text-sm font-medium center-icon">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($users as $user)
                                <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600" >
                                        <td class="px-6 py-4">{{ $user['realm'] }}:{{ $user['username'] }}</td>
                                        <td class="px-6 py-4">{{ $user['secret'] }}</td>
                                        <td class="px-6 py-4 center-icon">
                                            <form action="{{ route('service-users.delete', ['id' => $user['id']]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this service user?')">
                                                    <span class="material-icons">delete_forever</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <br>
                    <p class="text-md font-medium text-gray-900 dark:text-gray-100"><em>Access Key is needed for communication between your device and TE-LabPlatform</em></p>
                <div class="mt-4 flex justify-start">
                    <button id="openFormButton" onclick="openForm()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create New Access Key</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form -->
    <div id="popupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form action="{{ route('service-users.store') }}" method="POST" class="p-10">
                @csrf
                <div class="mb-4">
                    <label for="username" class="block text-lg font-medium text-gray-700">Access Key Name:</label>
                    <input type="text" name="username" id="username" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <!-- Add other fields as needed -->

                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Access Key</button>
                    <button id="closeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
        <!-- JavaScript for Popup Form -->
        <script>
            function openForm() {
                document.getElementById('popupForm').classList.remove('hidden');
            }
    
            function closeForm() {
                document.getElementById('popupForm').classList.add('hidden');
            }
    
            document.getElementById('openFormButton').addEventListener('click', openForm);
            document.getElementById('closeFormButton').addEventListener('click', closeForm);
    
            // Automatically close success and error alerts after 5 seconds
            setTimeout(function() {
                document.getElementById('successAlert').remove();
            }, 5000); // 5000 milliseconds = 5 seconds
    
            setTimeout(function() {
                document.getElementById('errorAlert').remove();
            }, 5000); // 5000 milliseconds = 5 seconds
        </script>
</x-app-layout>
