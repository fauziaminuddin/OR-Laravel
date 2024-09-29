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
                    
                    <label for="name" class="block text-lg font-medium text-white">Dashboard Name : {{ $dashboard->name }}</label>
                    <!-- Gauge Widgets -->
                    <div id="gauge-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($dashboard->widgets as $widget)
                            @if ($widget->type === 'gauge')
                                <div class="mb-4 bg-gray-200 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium dark:text-white">{{ $widget->widget_name }}</h3>
                                    <div id="gauge-{{ $widget->id }}" class="gauge-container" style="width: 250px; height: 200px; background-color: transparent;"></div>
                                    <p class="text-gray-500 dark:text-gray-200">
                                        Latest Value: 
                                        <span id="gauge-value-{{ $widget->id }}">
                                            {{ $gaugeData[$widget->id]['y'] ?? 'N/A' }}
                                        </span>
                                    </p> 
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Line Chart Widgets -->
                    <div id="line-chart-widgets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                        @foreach ($dashboard->widgets as $widget)
                            @if ($widget->type === 'line_chart')
                                <div class="flex-none w-full max-w-full mb-4 bg-gray-200 dark:bg-gray-700 p-4 rounded-lg" style="height: 450px;">
                                    <h3 class="text-lg font-medium dark:text-white">{{ $widget->widget_name }}</h3>
                                    <div id="line-chart-{{ $widget->id }}" class="w-full h-full">
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
    <script src='https://cdn.plot.ly/plotly-2.32.0.min.js'></script>
    <script>
        function openForm(actionUrl, method) {
            document.getElementById('widgetForm').action = actionUrl;
            document.getElementById('formMethod').value = method;
        }

        document.addEventListener('DOMContentLoaded', function () {
            @foreach ($gaugeData as $widgetId => $dataPoint)
            // Get the gauge range for the widget
            var gaugeRange = @json($gaugeRanges[$widgetId] ?? ['min_value' => 0, 'max_value' => 100]);

            var data = [
                {
                    domain: { x: [0, 1], y: [0, 1] },
                    value: {{ $dataPoint['y'] }},
                    type: "indicator",
                    mode: "gauge+number",
                    gauge: {
                        axis: { range: [gaugeRange.min_value, gaugeRange.max_value] },
                        steps: [
                            { range: [gaugeRange.min_value, (gaugeRange.min_value + gaugeRange.max_value) / 2], color: "lightgray" },
                            { range: [(gaugeRange.min_value + gaugeRange.max_value) / 2, gaugeRange.max_value], color: "gray" }
                        ],
                        bar: {
                            color: "cyan",
                            thickness: 0.5
                        },
                        bgcolor: "white",
                        borderwidth: 1,
                        bordercolor: "gray",
                    }
                }
            ];

            var layout = {
                width: 250,
                height: 200,
                margin: { t: 0, b: 0, l: 50, r: 0 },
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'transparent',
                font: { color: "cyan", family: "Arial" }
            };
            Plotly.newPlot('gauge-{{ $widgetId }}', data, layout);
        @endforeach

            @foreach ($lineChartData as $widgetId => $dataPoints)
                var trace1 = {
                    x: {!! json_encode(array_column($dataPoints, 'x')) !!},
                    y: {!! json_encode(array_column($dataPoints, 'y')) !!},
                    type: 'scatter'
                };

                var data = [trace1];

                var layout = {
                    width: 475,
                    height: 350,
                    xaxis: {
                        title: 'Time',
                        type: 'date'
                    },
                    yaxis: {
                        title: 'Value'
                    }
                    
                };

                Plotly.newPlot('line-chart-{{ $widgetId }}', data, layout);
            @endforeach
        });
    </script>
</x-app-layout>
