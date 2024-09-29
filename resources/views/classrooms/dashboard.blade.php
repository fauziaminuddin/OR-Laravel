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

                    <label for="name" class="block text-lg font-medium text-gray-900 dark:text-gray-100">Dashboard Name : {{ $dashboard->name }}</label>
                    <a href="{{ url()->previous() }}" class="inline-block px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700">
                        Back to Assignments
                    </a>
                    
                    <!-- Gauge Widgets -->
                    <div id="gauge-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($dashboard->widgets as $widget)
                            @if ($widget->type === 'gauge')
                                <div class="mb-4 bg-gray-200 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium dark:text-white">{{ $widget->widget_name }}</h3>
                                    
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

    <!-- JavaScript for Popup Form and Widgets -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    
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
    }, 10000);
</script>

</x-app-layout>