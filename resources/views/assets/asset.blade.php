<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $asset['name'] }}
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

                    <label for="name" class="block text-lg font-medium text-gray-900 dark:text-white" style="width: 100px; display: inline-block;">Project id</label>
                    <span class="text-lg text-gray-900 dark:text-white">: {{ $asset['id'] }}</span>
                    <br>
                    <label for="name" class="block text-lg font-medium text-gray-900 dark:text-white" style="width: 100px; display: inline-block;">Created On</label>
                    <span class="text-lg text-gray-900 dark:text-white">: {{ $asset['createdOn'] }}</span>

                    <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-white">Attributes</h3>
                    <br>

                    <div class="table-responsive">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr class="text-left text-gray-600 dark:text-gray-400">
                                    <th class="px-6 py-3 text-sm font-medium">Name</th>
                                    <th class="px-6 py-3 text-sm font-medium">Type</th>
                                    <th class="px-6 py-3 text-sm font-medium">Value</th>
                                    <th class="text-sm font-medium">Send Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach ($asset['attributes'] as $key => $attribute)
                                    @if($attribute['type'] !== 'GEO_JSONPoint')
                                        <tr class="text-gray-700 dark:text-gray-300" id="attribute_{{ $key }}">
                                            <td class="px-6 py-4">{{ $key }}</td>
                                            <td class="px-6 py-4">
                                                @if($attribute['type'] === 'text')
                                                    <span class="bg-purple-200 text-purple-800 rounded-lg px-2 py-1">{{ $attribute['type'] }}</span>
                                                @elseif($attribute['type'] === 'number')
                                                    <span class="bg-orange-200 text-orange-800 rounded-lg px-2 py-1">{{ $attribute['type'] }}</span>
                                                @elseif($attribute['type'] === 'boolean')
                                                    <span class="bg-blue-200 text-blue-800 rounded-lg px-2 py-1">{{ $attribute['type'] }}</span>
                                                @else
                                                    {{ $attribute['type'] }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 dark:text-black">
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
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-md font-medium text-gray-700 dark:text-gray-100"><em>Attribute is use to store the data that send from your device</em></p>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('assets.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Project List</a>
                        <a href="{{ route('assets.edit', ['id' => $asset['id']]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit Project</a>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Code Configuration</h3>
                        <p class="text-md font-medium text-gray-700 dark:text-gray-100"><em>Add the configuration below to your device code</em></p>
                        <div class="relative bg-gray-900 text-white p-4 rounded mt-4">
                            <button onclick="copyCode()" class="absolute top-0 right-0 mt-2 mr-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded">
                                Copy Code
                            </button>
                            <pre id="codeSnippet" class="overflow-auto p-4 whitespace-pre-wrap">
                                <code>
<span class="keyword">const char</span>* <span class="server">server</span> = "localhost";
<span class="keyword">const char</span>* <span class="username">username</span> = "accesskey_username"; <span class="string">//change with your key username from access key page</span>
<span class="keyword">const char</span>* <span class="secret">secret</span> = "accesskey_secret"; <span class="string">//change with your key secret from access key page</span>
<span class="keyword">const char</span>* <span class="client-id">ClientID</span> = "Client123";

<span class="string">//route for publish data</span>
<span class="keyword">const char</span>* <span class="send-topic">send_topic</span> = "master/Client123/writeattributevalue/{attribute_name}/{{ $asset['id'] }}";
<span class="string">//route for subscribe data</span>
<span class="keyword">const char</span>* <span class="get-topic">get_topic</span> = "master/Client123/attribute/{attribute_name}/{{ $asset['id'] }}";

<span class="string">//change the {attribute_name} with your attribute name</span>

<span class="function">void</span> <span class="setup">setup</span>() {
    <span class="string">//your setup code</span>
    <span class="setup">client.setServer</span>(server, <span class="server">1883</span>);
}
                                </code>
                            </pre>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-md font-medium text-gray-700 dark:text-gray-100"><em>If you want to see the example implementation for the code in ESP32, you could download the example file</em></p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">Download the example file:</p>
                        <a href="{{ route('download.example') }}" class="inline-flex items-center px-4 py-2 mt-2 text-white bg-green-500 hover:bg-green-700 font-bold rounded">
                            <span class="material-icons mr-2">file_download</span>
                            Download Example
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Automatically close success and error alerts after 5 seconds
        setTimeout(function() {
            document.getElementById('successAlert').remove();
        }, 5000); // 5000 milliseconds = 5 seconds
    
        setTimeout(function() {
            document.getElementById('errorAlert').remove();
        }, 5000); // 5000 milliseconds = 5 seconds

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

            fetch(`/projects/${assetId}/attributes`, {
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
            .catch(error => console.error('Error:', error));
        }

        function copyCode() {
        const codeSnippet = document.getElementById('codeSnippet').innerText;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(codeSnippet).then(() => {
                alert('Code copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy code: ', err);
                alert('Failed to copy code. Please try again.');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = codeSnippet;
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                alert('Code copied to clipboard!');
            } catch (err) {
                console.error('Failed to copy code: ', err);
                alert('Failed to copy code. Please try again.');
            }
            document.body.removeChild(textarea);
        }
    }
    </script>
</x-app-layout>
