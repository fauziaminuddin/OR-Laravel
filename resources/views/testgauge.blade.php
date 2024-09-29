{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <div>
    <canvas id="myChart"></canvas>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <script>
    const ctx = document.getElementById('myChart').getContext('2d');
  
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Red', 'Remaining'],
        datasets: [{
          label: '# of Votes',
          data: [12, 8],  // 12 out of 20 votes, indicating a min-max range
          borderWidth: 1,
          backgroundColor: ['#ff6384', '#e0e0e0'],  // Red and grey to show the range
        }]
      },
      options: {
        cutout: '70%', // Adjusts the size of the inner circle
        plugins: {
          tooltip: {
            callbacks: {
              label: function(tooltipItem) {
                return tooltipItem.label + ': ' + tooltipItem.raw + ' votes';
              }
            }
          }
        }
      }
    });
  </script>
  
</body>
</html> --}}
