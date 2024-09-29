<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Home') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-3xl">Welcome to the TE-LabPlatform</h3>
                    <br>
                    <p class="text-md text-gray-900 dark:text-gray-100">
                        TE-LabPlatform is a Web Platform Laboratory that could be used by Electrical Engineer of UPI to connect their IoT Device to Platform using MQTT.
                    </p>
                    <br>
                    <p class="text-md text-gray-900 dark:text-gray-100">
                        To start your project you could create your first project in "My Project" page.
                    </p>
                    <br>
                    <!-- Tambahkan teks tambahan di sini -->
                    <div class="space-y-2">
                        <div class="flex">
                            <strong>My Project</strong> 
                            <span class="ml-4">: Use for manage the project for storing the data</span>
                        </div>
                        <div class="flex">
                            <strong>Access Key</strong> 
                            <span class="ml-3">: Use for maintain the MQTT communication between the web and your device</span>
                        </div>
                        <div class="flex">
                            <strong>Dashboard</strong> 
                            <span class="ml-4">: Use for display your data in a widget</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
