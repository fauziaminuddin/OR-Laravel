<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $dashboard->name }}
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
                    <label for="name" class="block text-lg font-medium text-gray-900 dark:text-gray-100">Dashboard Name : {{ $dashboard->name }}</label>

                    <!-- Button to open the form to add a widget -->
                    <div class="mt-4 flex space-x-2 mb-4">
                        <a href="{{ route('dashboards.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Dashboard List</a>
                        <button id="openFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add Widget
                        </button>
                    </div>
                    <p class="text-md font-light text-gray-700 dark:text-gray-100"><em><b>REMINDER</b> : When creating widget, you need to fill the <b>"Attribute Name"</b> same as the name of your <b>Attribute name</b> in your Project<em><p>
                <p class="text-md font-light text-gray-700 dark:text-gray-100"><em><b>ONLY</b> attribute with <b>"Number"</b> Type could be shown with Widget<em><p>
		<!-- Gauge Widgets -->
                    <div id="gauge-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($dashboard->widgets as $widget)
                            @if ($widget->type === 'gauge')
                                <div class="mb-4 bg-gray-200 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium dark:text-white">{{ $widget->widget_name }}</h3>
                                    <div class="mt-4 flex space-x-2">
                                        <!-- Edit Button -->
                                        <button class="editButton text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" data-widget-id="{{ $widget->id }}">
                                            <span class="material-icons">
                                                edit
                                            </span>
                                        </button>
                                        <!-- Edit Button for Range -->
                                        <button class="editRangeButton bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" data-widget-id="{{ $widget->id }}">
                                                Range
                                        </button>
                                        <!-- Delete Button Form -->
                                        <form action="{{ route('widgets.destroy', ['dashboard' => $dashboard->id, 'widget' => $widget->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this widget?');">
                                                <span class="material-icons">delete_forever</span>
                                            </button>
                                        </form>
                                    </div>
                                    <!-- Canvas for Gauge Chart -->
                                    <div id="gauge-{{ $widget->id }}" class="gauge-container" style="width: 300px; height: 300px; background-color: transparent;">
                                        <canvas id="gaugeChart-{{ $widget->id }}" width="300" height="300"></canvas>
                                        <!-- Min and Max Range Display -->
                                        <div class="range-values mt-4 flex justify-between text-gray-500 dark:text-gray-200">
                                            <span class="px-5" id="min-range-{{ $widget->id }}">{{ $gaugeRanges[$widget->id]['min_value'] ?? '0' }}</span>
                                            <span class="px-3" id="max-range-{{ $widget->id }}">{{ $gaugeRanges[$widget->id]['max_value'] ?? '100' }}</span>
                                        </div>
                                        <p class="text-gray-500 dark:text-gray-100 ">
                                            Latest Value: 
                                            <span class="chart-text" id="gauge-value-{{ $widget->id }}">
                                                {{ $gaugeData[$widget->id]['y'] ?? 'N/A' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Line Chart Widgets -->
                    <div id="line-chart-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-8">
                        @foreach ($dashboard->widgets as $widget)
                            @if ($widget->type === 'line_chart')
                                <div class="flex-none w-full max-w-full mb-4 bg-gray-200 dark:bg-gray-700 p-4 rounded-lg" style="height: 450px;">
                                    <h3 class="text-lg font-medium dark:text-white">{{ $widget->widget_name }}</h3>
                                    <div class="mt-4 flex space-x-2">
                                        <!-- Edit Button -->
                                        <button class="editButton text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" data-widget-id="{{ $widget->id }}">
                                            <span class="material-icons">
                                                edit
                                            </span>
                                        </button>
                                        <!-- Delete Button Form -->
                                        <form action="{{ route('widgets.destroy', ['dashboard' => $dashboard->id, 'widget' => $widget->id]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this widget?');">
                                                <span class="material-icons">delete_forever</span>
                                            </button>
                                        </form>
                                    </div>
                                    <div id="line-chart-{{ $widget->id }}" class="gauge-container" style="width: 500px; height: 350px;">
                                        <canvas id="lineChart-{{ $widget->id }}" width="500" height="350"></canvas>
                                    </div>
                                </div>   
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form -->
<div id="popupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg w-96">
        <form id="widgetForm" action="{{ route('dashboards.widgets.store', $dashboard->id) }}" method="POST" class="p-10">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="mb-4">
                <label for="widget_name" class="block text-lg font-medium text-gray-700">Widget Name:</label>
                <input type="text" name="widget_name" id="widget_name" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label for="asset_id" class="block text-lg font-medium text-gray-700">Project:</label>
                <select name="asset_id" id="asset_id" class="form-select mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="">Select a Project</option>
                    @foreach($userAssets as $userAsset)
                        <option value="{{ $userAsset->asset_id }}">{{ $userAsset->asset_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="attribute_name" class="block text-lg font-medium text-gray-700">Attribute Name:</label>
                <input type="text" name="attribute_name" id="attribute_name" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label for="type" class="block text-lg font-medium text-gray-700">Widget Type:</label>
                <select name="type" id="type" class="form-select mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                    <option value="line_chart">Line Chart</option>
                    <option value="gauge">Gauge</option>
                </select>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Widget</button>
                <button id="closeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>
<!-- Popup Form for Editing Gauge Range -->
<div id="popupRangeForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg w-96">
        <form id="gaugeRangeForm" action="#" method="POST" class="p-10">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="min_value" class="block text-lg font-medium text-gray-700">Min Value:</label>
                <input type="number" name="min_value" id="min_value" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label for="max_value" class="block text-lg font-medium text-gray-700">Max Value:</label>
                <input type="number" name="max_value" id="max_value" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Range</button>
                <button id="closeRangeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>


    <!-- JavaScript for Popup Form and Widgets -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to open the popup form with specified action URL and method
    function openForm(actionUrl, method) {
        document.getElementById('widgetForm').action = actionUrl;
        document.getElementById('formMethod').value = method;
        document.getElementById('popupForm').classList.remove('hidden');
    }

    // Function to close the popup form
    function closeForm() {
        document.getElementById('popupForm').classList.add('hidden');
    }

    // Function to open the range editing popup form with specified action URL and current range values
    function openRangeForm(actionUrl, min_value, max_value) {
        document.getElementById('gaugeRangeForm').action = actionUrl;
        document.getElementById('min_value').value = min_value;
        document.getElementById('max_value').value = max_value;
        document.getElementById('popupRangeForm').classList.remove('hidden');
    }

    // Function to close the range editing popup form
    function closeRangeForm() {
        document.getElementById('popupRangeForm').classList.add('hidden');
    }

    // Event listener to open the widget creation form when the button is clicked
    document.getElementById('openFormButton').addEventListener('click', function() {
        openForm("{{ route('dashboards.widgets.store', $dashboard->id) }}", "POST");
    });

    // Event listeners to close the forms when the cancel button is clicked
    document.getElementById('closeFormButton').addEventListener('click', closeForm);
    document.getElementById('closeRangeFormButton').addEventListener('click', closeRangeForm);

    // Event listeners for each edit range button to open the gauge range editing form
    document.querySelectorAll('.editRangeButton').forEach(button => {
        button.addEventListener('click', function() {
            const widgetId = this.dataset.widgetId;
            const widget = @json($dashboard->widgets->keyBy('id'));

            // Populate the form with widget range data
            const gaugeRange = @json($gaugeRanges);
            const range = gaugeRange[widgetId] || { min_value: 0, max_value: 100 };
            openRangeForm("{{ url('dashboards') }}/{{ $dashboard->id }}/widgets/" + widgetId + "/range", range.min_value, range.max_value);
        });
    });

    // Event listeners for each edit button to open the widget editing form
    document.querySelectorAll('.editButton').forEach(button => {
        button.addEventListener('click', function() {
            const widgetId = this.dataset.widgetId;
            const widget = @json($dashboard->widgets->keyBy('id'));

            // Populate the form with widget data
            document.getElementById('widget_name').value = widget[widgetId].widget_name;
            document.getElementById('asset_id').value = widget[widgetId].asset_id;
            document.getElementById('attribute_name').value = widget[widgetId].attribute_name;
            document.getElementById('type').value = widget[widgetId].type;

            openForm("{{ url('dashboards') }}/{{ $dashboard->id }}/widgets/" + widgetId, "PUT");
        });
    });

    // Automatically close success and error alerts after 5 seconds
    setTimeout(function() {
        document.getElementById('successAlert')?.remove();
    }, 5000); // 5000 milliseconds = 5 seconds

    setTimeout(function() {
        document.getElementById('errorAlert')?.remove();
    }, 5000); // 5000 milliseconds = 5 seconds

    const gaugeCharts = {};
    const lineCharts = {};

    // Function to create a gauge chart for each widget
    function createGaugeChart(widgetId, value, min = 0, max = 100) {
        const ctx = document.getElementById('gaugeChart-' + widgetId).getContext('2d');
        if (gaugeCharts[widgetId]) {
            gaugeCharts[widgetId].destroy(); // Destroy previous chart instance if exists
        }

        gaugeCharts[widgetId] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [value - min, max - value],
                    backgroundColor: ['#00FFFF', '#808080'],
                    borderWidth: 1,
                }]
            },
            options: {
                cutoutPercentage: 70,
                animation:{
                    animateRotate: false,
                    animateScale: false,
                },
                plugins: {
                    datalabels: {
                        display: true,
                        formatter: (value) => Math.round(value),
                        color: '#000',
                        font: {
                            size: 20,
                            weight: 'bold'
                        }
                    }
                },
                tooltips: { enabled: false },
                hover: { mode: null },
                responsive: true,
                circumference: 180,
                rotation: -90,
            }
        });
    }
    // Function to create or update a line chart
    function createLineChart(widgetId, data) {
        const ctx = document.getElementById('lineChart-' + widgetId).getContext('2d');
        if (lineCharts[widgetId]) {
            lineCharts[widgetId].destroy(); // Destroy previous chart instance if exists
        }
         // Sort the data in descending order based on the x values (timestamps)
        const sortedData = data.sort((a, b) => b.x - a.x);
        // Limit to the 10 latest data points
        const limitedData = sortedData.slice(0, 10);
        // Parse timestamps and prepare data
        const parsedData = limitedData.map(point => ({
            x: new Date(point.x), // Convert timestamp to Date object
            y: point.y
        }));

        lineCharts[widgetId] = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Value',
                    data: parsedData, // Use the parsed data
                    borderColor: '#7DF9FF',
                    backgroundColor: '#90EE90',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
        options: {
            responsive: true,
            animation: false,
            scales: {
                x: {
                    type: 'time', // Time series data
                    distribution: 'series',
                    time: {
                        // unit: 'second', // Adjust as needed
                        // tooltipFormat: 'YYYY-MM-DD HH:mm:ss' // Time format for tooltips
                    },
                    title: {
                        display: true,
                        text: 'Time',
                        color: '#FFA500'
                    },
                    grid: {
                        color: '#D3D3D3' // Gridline color
                    },

                    ticks: {
                        source: 'auto',
                        color: '#AEC6CF' // Axis labels color
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Value',
                        color: '#FFA500'
                    },
                    grid: {
                        color: '#D3D3D3' // Gridline color
                    },
                    ticks: {
                        color: '#AEC6CF' // Axis labels color
                    },
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#FFFFFF' // Legend text color
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                    return context.label + ': ' + context.formattedValue;
                    },
                    title: function(context) {
                    return moment(context.label).format('YYYY-MM-DD HH:mm:ss');
                    }
                }
            }
        }
    });
}
    // Call fetchData initially
    fetchData();

    // Function to update the dashboard widgets with fetched data
    function updateDashboard(data) {
        $.each(data.gaugeRanges, function(widgetId, range) {
            let latestData = data.dataPoints[widgetId] ? data.dataPoints[widgetId][0] : null;
            if (latestData) {
                $('#gauge-value-' + widgetId).text(latestData.y);
                createGaugeChart(widgetId, latestData.y, range.min_value, range.max_value);
            }
        });
        // Update line charts
        $.each(data.lineCharts, function(widgetId, points) {
                createLineChart(widgetId, points);
        });
    }

    // Example AJAX fetching the data
    function fetchData() {
        $.ajax({
            url: '/dashboard/{{ $dashboard->id }}/fetch-data',
            method: 'GET',
            success: function(response) {
                updateDashboard(response);
            },
            error: function(xhr) {
                console.error('Failed to fetch data:', xhr.responseText);
            }
        });
    }

    // Set an interval to fetch data every 10 seconds
    setInterval(function() {
        fetchData();
    }, 3000);
</script>

</x-app-layout>
