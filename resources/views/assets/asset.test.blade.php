<x-app-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Asset Details</h1>
        
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4">
                <h2 class="text-xl font-bold">Asset: {{ $asset['name'] }}</h2>
                <p>ID: {{ $asset['id'] }}</p>
                <p>Created On: {{ $asset['createdOn'] }}</p>
            </div>
            
            <div class="p-4">
                <h3 class="text-lg font-bold mb-2">Attributes</h3>
                <table class="min-w-full bg-white border">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-gray-300">Name</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300">Type</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300">Value</th>
                            <th class="px-6 py-3 border-b-2 border-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($asset['attributes'] as $key => $attribute)
                            <tr id="attribute_{{ $key }}">
                                <td class="px-6 py-4">{{ $key }}</td>
                                <td class="px-6 py-4">{{ $attribute['type'] }}</td>
                                <td class="px-6 py-4">
                                    @if ($attribute['type'] === 'text')
                                        <input type="text" id="value_{{ $key }}" value="{{ $attribute['value'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                                    @elseif ($attribute['type'] === 'number')
                                        <input type="number" id="value_{{ $key }}" value="{{ $attribute['value'] }}" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                                    @elseif ($attribute['type'] === 'boolean')
                                        <input type="checkbox" id="value_{{ $key }}" {{ $attribute['value'] ? 'checked' : '' }} class="form-checkbox mt-1 block text-lg border border-gray-300 rounded-lg px-3 py-2">
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <button type="button" onclick="updateAttribute('{{ $asset['id'] }}', '{{ $key }}')" class="text-blue-600 hover:text-blue-900">
                                        <span class="material-icons">send</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function updateAttribute(assetId, attributeName) {
            const valueElement = document.getElementById('value_' + attributeName);
            let attributeValue;

            if (valueElement.type === 'checkbox') {
                attributeValue = valueElement.checked;
            } else {
                attributeValue = valueElement.value;
            }

            const data = [
                {
                    ref: {
                        id: assetId,
                        name: attributeName
                    },
                    value: attributeValue
                }
            ];

            fetch(`/assets/${assetId}/attributes`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Attribute updated successfully!');
                } else {
                    alert('Failed to update attribute: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating attribute: ' + error.message);
            });
        }
    </script>
</x-app-layout>
