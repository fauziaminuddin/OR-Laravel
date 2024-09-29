<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Line Chart Example</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
    <div style="width: 80%; margin: auto;">
        <canvas id="lineChart"></canvas>
    </div>
    
    <script>
        // Example static data with timestamps in milliseconds
        const data = [
            { x: 1725382202397, y: 2031 },
            { x: 1725382192948, y: 1995 },
            { x: 1725382182261, y: 2047 },
            { x: 1725382171962, y: 2041 },
            { x: 1725382161993, y: 2007 }
        ];

        // Parse the timestamp to a Date object
        const parsedData = data.map(point => ({
            x: new Date(point.x), // Convert timestamp to Date object
            y: point.y
        }));

        // Create the line chart
        function createLineChart() {
            const ctx = document.getElementById('lineChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Value',
                        data: parsedData, // Use the parsed data
                        borderColor: '#4caf50',
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'time',
                            time: {
                                tooltipFormat: 'HH:mm:ss', // Time format for the tooltip
                                unit: 'second', // Show one point per second
                            },
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Value'
                            }
                        }
                    }
                }
            });
        }

        // Call the function to create the chart
        createLineChart();
    </script>
</body>
</html>
