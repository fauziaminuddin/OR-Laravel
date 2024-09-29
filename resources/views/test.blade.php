<!DOCTYPE html>
<html>
<head>
    <title>reverb Test</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        var pusher = new Pusher("{{ env('REVERB_APP_KEY') }}", {
            cluster: "",
            wsHost: "{{ env('REVERB_HOST') }}",
            wsPort: "{{ env('REVERB_PORT') }}",
            wssPort: "{{ env('REVERB_PORT') }}",
            forceTLS: false,
            enabledTransports: ['ws']
    });
        var channel = pusher.subscribe("test-channel");
        channel.bind("App\\Events\\TestEvent", (data) => {
            let tableBid = document.getElementById('table-bid');
                var row = tableBid.insertRow();
                var cell1 = row.insertCell(0);
                cell1.innerHTML = data.name;
        });
    </script>
</head>
<body>
    <table id="table-bid" border="1" style="font-size: 78px">
        <tr>
            <td>Message</td>
        </tr>
    </table>
</body>
</html>
