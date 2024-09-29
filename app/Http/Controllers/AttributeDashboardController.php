<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Widget;
use GuzzleHttp\Client;
use App\Models\UserAsset;
use App\Models\GaugeRange;
use Illuminate\Http\Request;
use App\Models\AttributeDashboard;
use App\Services\OpenRemoteService;
use Illuminate\Support\Facades\Auth;

class AttributeDashboardController extends Controller
{
    protected $client;
    protected $openRemoteService;

    public function __construct(OpenRemoteService $openRemoteService)
    {
        $this->client = new Client([
            'base_uri' => 'https://localhost/', // Base URI for the OpenRemote API
            'verify' => false, // Only for local development, disable SSL verification
        ]);
        $this->openRemoteService = $openRemoteService;
    }

    public function index()
    {
        $dashboards = AttributeDashboard::where('user_id', Auth::id())->get();
        return view('dashboards.index', ['dashboards' => $dashboards]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        AttributeDashboard::create([
            'name' => $request->input('name'),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('dashboards.index')->with('success', 'Dashboard created successfully.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $dashboard = AttributeDashboard::findOrFail($id);
        $dashboard->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('dashboards.index')->with('success', 'Dashboard updated successfully.');
    }

    public function destroydash($id)
    {
        $dashboard = AttributeDashboard::findOrFail($id);
        $dashboard->delete();

        return redirect()->route('dashboards.index')->with('success', 'Dashboard deleted successfully.');
    }


    public function show($id)
    {
        $dashboard = AttributeDashboard::findOrFail($id);

        // Fetch widgets associated with the dashboard
        $widgets = $dashboard->widgets;

        // Initialize array to store data points for each widget
        $dataPoints = [];
        $gaugeRanges = [];

        // Fetch data points for each widget
        foreach ($widgets as $widget) {
            try {
                $response = $this->client->post("api/master/asset/datapoint/{$widget->asset_id}/{$widget->attribute_name}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->openRemoteService->refreshTokenIfNeeded(),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);

                $dataPoints[$widget->id] = json_decode($response->getBody()->getContents(), true);
            } catch (\Exception $e) {
                $dataPoints[$widget->id] = [];
            }
            // Fetch gauge range for gauge widgets
            if ($widget->type == 'gauge') {
                $gaugeRange = GaugeRange::where('widget_id', $widget->id)->first();
                $gaugeRanges[$widget->id] = $gaugeRange;
            }
        }

        // Prepare data for gauge widgets
        $gaugeData = [];
        foreach ($widgets as $widget) {
            if ($widget->type == 'gauge') {
                $latestDataPoint = $dataPoints[$widget->id][0] ?? null; // Take the latest or top data point
                if ($latestDataPoint) {
                    $gaugeData[$widget->id] = $latestDataPoint;
                }
            }
        }

        // Prepare data for line chart widgets
        $lineChartData = [];
        foreach ($widgets as $widget) {
            if ($widget->type == 'line_chart') {
                $lineChartData[$widget->id] = $dataPoints[$widget->id];
            }
        }

        $userAssets = UserAsset::where('user_id', Auth::id())->get();

        return view('dashboards.show', compact('dashboard', 'dataPoints', 'gaugeData', 'lineChartData', 'userAssets', 'gaugeRanges'));
    }

    public function fetchData($id)
    {
        $dashboard = AttributeDashboard::findOrFail($id);
        $widgets = $dashboard->widgets;

        // Initialize arrays to store data
        $dataPoints = [];
        $gaugeRanges = [];
        $lineCharts = [];

        foreach ($widgets as $widget) {
            try {
                $response = $this->client->post("api/master/asset/datapoint/{$widget->asset_id}/{$widget->attribute_name}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->openRemoteService->refreshTokenIfNeeded(),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($widget->type == 'gauge') {
                    $dataPoints[$widget->id] = $data;
                    $gaugeRange = GaugeRange::where('widget_id', $widget->id)->first();
                    $gaugeRanges[$widget->id] = $gaugeRange;
                } elseif ($widget->type == 'line_chart') {
                    $lineCharts[$widget->id] = $data;
                }
            } catch (\Exception $e) {
                if ($widget->type == 'gauge') {
                    $dataPoints[$widget->id] = [];
                } elseif ($widget->type == 'line_chart') {
                    $lineCharts[$widget->id] = [];
                }
            }
        }

        // Prepare response data
        $response = [
            'dataPoints' => $dataPoints,
            'gaugeRanges' => $gaugeRanges,
            'lineCharts' => $lineCharts
        ];

        return response()->json($response);
    }

    public function addWidget(Request $request, $dashboardId)
    {
        $request->validate([
            'widget_name' => 'required|string|max:255',
            'asset_id' => 'required|string|max:255',
            'attribute_name' => 'required|string|max:255',
            'type' => 'required|string|in:line_chart,gauge,button', // Validate type
        ]);

        $widget = Widget::create([
            'dashboard_id' => $dashboardId,
            'widget_name' => $request->widget_name,
            'asset_id' => $request->asset_id,
            'attribute_name' => $request->attribute_name,
            'type' => $request->type,
        ]);

        // Create a default gauge range for every widget
        GaugeRange::create([
            'widget_id' => $widget->id,
            'min_value' => 0,
            'max_value' => 100,
        ]);

        return redirect()->route('dashboards.show', $dashboardId)->with('success', 'Widget added successfully.');
    }

    public function getDataPoints(Request $request, $widgetId)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();
        $widget = Widget::find($widgetId);

        if (!$widget) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        try {
            $response = $this->client->post("api/master/asset/datapoint/{$widget->asset_id}/{$widget->attribute_name}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $dataPoints = json_decode($response->getBody()->getContents(), true);

            return response()->json($dataPoints);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data points: ' . $e->getMessage()], 500);
        }
    }

    public function updateWidget(Request $request, $dashboardId, $widgetId)
    {
        $request->validate([
            'widget_name' => 'required|string|max:255',
            'asset_id' => 'required|string|max:255',
            'attribute_name' => 'required|string|max:255',
            'type' => 'required|string|in:line_chart,gauge,button',
        ]);

        $dashboard = AttributeDashboard::findOrFail($dashboardId);
        $widget = $dashboard->widgets()->findOrFail($widgetId);

        $widget->update([
            'widget_name' => $request->input('widget_name'),
            'asset_id' => $request->input('asset_id'),
            'attribute_name' => $request->input('attribute_name'),
            'type' => $request->input('type'),
        ]);

        return redirect()->route('dashboards.show', $dashboardId)->with('success', 'Widget updated successfully.');
    }
    public function destroy($dashboardId, $widgetId)
    {
        $dashboard = AttributeDashboard::findOrFail($dashboardId);
        $widget = $dashboard->widgets()->findOrFail($widgetId);

        $widget->delete();

        return redirect()->route('dashboards.show', $dashboardId)->with('success', 'Widget deleted successfully.');
    }

    public function updateGaugeRange(Request $request, AttributeDashboard $dashboard, $widgetId)
    {
        $request->validate([
            'min_value' => 'required|numeric',
            'max_value' => 'required|numeric',
        ]);

        // Find or create a GaugeRange for the widget
        $gaugeRange = GaugeRange::updateOrCreate(
            ['widget_id' => $widgetId],
            [
                'min_value' => $request->input('min_value'),
                'max_value' => $request->input('max_value'),
            ]
        );

        return redirect()->route('dashboards.show', $dashboard->id)
                         ->with('success', 'Gauge range updated successfully!');
    }

}
