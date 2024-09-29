<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Student Project') }}
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

                    @foreach ($usersWithAssetsAndDashboards as $data)
                        <div class="user-section mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $data['user']->name }}</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $data['user']->email }}</p>

                            <div class="mt-4 table-responsive">
                                <h3 class="text-md font-semibold text-gray-800 dark:text-gray-400">Projects</h3>
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr class="text-left text-gray-600 dark:text-gray-400">
                                            <th class="px-6 py-3 text-sm font-medium">Name</th>
                                            <th class="px-6 py-3 text-sm font-medium">Project ID</th>
                                            <th class="px-6 py-3 text-sm font-medium">Created On</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                        @forelse ($data['assets'] as $asset)
                                            <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                                                <td class="px-6 py-4">{{ $asset['name'] }}</td>
                                                <td class="px-6 py-4">{{ $asset['id'] }}</td>
                                                <td class="px-6 py-4">{{ $asset['createdOn'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No assets found for this user.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-md font-semibold text-gray-800 dark:text-gray-400">Dashboards</h3>
                                <div class="table-responsive mt-2">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr class="text-left text-gray-600 dark:text-gray-400">
                                                <th class="px-6 py-3 text-sm font-medium">Dashboard Name</th>
                                                <th class="px-6 py-3 text-sm font-medium">Created On</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            @forelse ($data['dashboards'] as $dashboard)
                                                <tr class="text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer" onclick="window.location='{{ route('admin.show', $dashboard->id) }}';">
                                                    <td class="px-6 py-4" >{{ $dashboard->name }}</td>
                                                    <td class="px-6 py-4">{{ $dashboard->created_at->setTimezone('Asia/Jakarta')->toDateTimeString() }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No dashboards found for this user.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="my-6 border-t border-gray-100 dark:border-gray-300">

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
