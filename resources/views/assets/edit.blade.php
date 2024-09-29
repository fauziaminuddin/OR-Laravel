<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit Project
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @elseif(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('assets.update', ['id' => $asset['id']]) }}" method="POST" id="edit-asset-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-lg font-medium text-white">Project Name</label>
                            <input type="text" name="name" id="name" value="{{ $asset['name'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                        </div>

                        <input type="hidden" name="version" value="{{ $asset['version'] }}">
                        <input type="hidden" name="createdOn" value="{{ $asset['createdOn'] }}">
                        <input type="hidden" name="accessPublicRead" value="{{ $asset['accessPublicRead'] }}">
                        <input type="hidden" name="realm" value="{{ $asset['realm'] }}">
                        <input type="hidden" name="type" value="{{ $asset['type'] }}">

                        <!-- Include location attribute if needed -->
                        @if(isset($asset['attributes']['location']))
                            <input type="hidden" name="attributes[location][name]" value="location">
                            <input type="hidden" name="attributes[location][type]" value="GEO_JSONPoint">
                            <input type="hidden" name="attributes[location][value]" value="{{ $asset['attributes']['location']['value'] }}">
                            <input type="hidden" name="attributes[location][timestamp]" value="{{ $asset['attributes']['location']['timestamp'] }}">
                        @endif

                        <h2 class="mt-6 text-lg font-medium text-white">Attributes</h2>
                        <div class="table-responsive">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr class="text-left text-gray-600 dark:text-gray-400">
                                        <th class="px-6 py-3 text-sm font-medium">Name</th>
                                        <th class="px-6 py-3 text-sm font-medium">Type</th>
                                        <th class="px-6 py-3 text-sm font-medium">Value</th>
                                        <th class="px-6 py-3 text-sm font-medium center-icon">Delete</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($asset['attributes'] as $key => $attribute)
                                        @if($key !== 'location' && $attribute['type'] !== 'GEO_JSONPoint')
                                            <tr id="attribute_{{ $key }}" class="text-gray-700">
                                                <td class="px-6 py-4 dark:text-gray-300">
                                                    {{ $attribute['name'] }}
                                                </td>
                                                <td class="px-6 py-4 dark:text-gray-300">{{ $attribute['type'] }}</td>
                                                <td class="px-6 py-4 dark:text-black">
                                                    @if($attribute['type'] === 'text')
                                                        <input type="text" name="attributes[{{ $key }}][value]" value="{{ $attribute['value'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                                                    @elseif($attribute['type'] === 'number')
                                                        <input type="number" name="attributes[{{ $key }}][value]" value="{{ $attribute['value'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                                                    @elseif($attribute['type'] === 'boolean')
                                                        <input type="checkbox" name="attributes[{{ $key }}][value]" {{ $attribute['value'] ? 'checked' : '' }}>
                                                    @else
                                                        <input type="text" name="attributes[{{ $key }}][value]" value="{{ $attribute['value'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 center-icon">
                                                    @if($key !== 'notes')
                                                        <button type="button" onclick="deleteAttribute('{{ $key }}')" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 delete-button">
                                                            <span class="material-icons">delete_forever</span>
                                                        </button>
                                                    @endif
                                                </td>
                                                <input type="hidden" name="attributes[{{ $key }}][type]" value="{{ $attribute['type'] }}">
                                                <input type="hidden" name="attributes[{{ $key }}][timestamp]" value="{{ $attribute['timestamp'] }}">
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('assets.show', ['id' => $asset['id']]) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Cancel</a>
                            <button type="button" onclick="openAddAttributeModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Attribute</button>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Update Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attribute Modal -->
    <div id="addAttributeModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50 flex justify-center items-center">
        <div class="relative bg-white w-1/2 md:w-1/3 p-6 rounded-lg">
            <button type="button" onclick="closeAddAttributeModal()" class="absolute top-0 right-0 m-4 text-gray-600 hover:text-gray-800">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h2 class="text-lg font-medium text-gray-700 mb-4">Add Attribute</h2>
            <form id="add-attribute-form" class="space-y-4">
                <div>
                    <label for="attribute_name" class="block text-sm font-medium text-gray-700">Attribute Name</label>
                    <input type="text" id="attribute_name" name="attribute_name" class="form-input mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label for="attribute_type" class="block text-sm font-medium text-gray-700">Attribute Type</label>
                    <select id="attribute_type" name="attribute_type" class="form-select mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="boolean">Boolean</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="addAttribute()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddAttributeModal() {
            document.getElementById('addAttributeModal').classList.remove('hidden');
        }

        function closeAddAttributeModal() {
            document.getElementById('addAttributeModal').classList.add('hidden');
        }

        function addAttribute() {
        const attributeName = document.getElementById('attribute_name').value;
        const attributeType = document.getElementById('attribute_type').value;

        const tableBody = document.querySelector('table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td class="px-6 py-4 dark:text-black">
                <input type="text" name="attributes[${attributeName}][name]" value="${attributeName}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
            </td>
            <td class="px-6 py-4 dark:text-gray-300">${attributeType}</td>
            <td class="px-6 py-4 dark:text-black">
                ${attributeType === 'text' ? '<input type="text" name="attributes[' + attributeName + '][value]" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">' : attributeType === 'number' ? '<input type="number" name="attributes[' + attributeName + '][value]" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">' : '<input type="checkbox" name="attributes[' + attributeName + '][value]" value="true">'}
            </td>
            <td class="px-6 py-4">
                <button type="button" onclick="deleteNewAttribute(this)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                    <span class="material-icons">delete</span>
                </button>
            </td>
            <input type="hidden" name="attributes[${attributeName}][type]" value="${attributeType}">
            <input type="hidden" name="attributes[${attributeName}][timestamp]" value="0">
        `;
        tableBody.appendChild(newRow);

        closeAddAttributeModal(); // Close the modal after adding attribute
    }


        function deleteAttribute(key) {
            const row = document.getElementById(`attribute_${key}`);
            if (row) {
                row.parentNode.removeChild(row);
            }
        }

        function deleteNewAttribute(button) {
            const row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
</x-app-layout>
