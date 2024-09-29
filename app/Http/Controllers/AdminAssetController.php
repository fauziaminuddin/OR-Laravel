<?php

// app/Http/Controllers/AdminAssetController.php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Widget;
use GuzzleHttp\Client;
use App\Models\UserAsset;
use App\Models\GaugeRange;
use Illuminate\Http\Request;
use App\Models\AttributeDashboard;
use App\Services\OpenRemoteService;
use Illuminate\Support\Facades\Auth;

class AdminAssetController extends Controller
{
    protected $client;
    protected $openRemoteService;

    public function __construct(OpenRemoteService $openRemoteService)
    {
        $this->client = new Client([
            'base_uri' => 'https://localhost/',
            'verify' => false,
        ]);
        $this->openRemoteService = $openRemoteService;
    }

    public function index(Request $request)
    {
        $token = $this->openRemoteService->refreshTokenIfNeeded();
        
        // Fetch all users
        $users = User::all();

        // Prepare an array to hold user and their assets
        $usersWithAssetsAndDashboards = [];

        foreach ($users as $user) {
            // Fetch asset IDs created by the user
            $userAssetIds = UserAsset::where('user_id', $user->id)->pluck('asset_id')->toArray();

            try {
                // Fetch assets from OpenRemote API
                $response = $this->client->post('api/master/asset/query', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        "recursive" => true,
                        "select" => ["basic" => true],
                        "access" => "PRIVATE",
                        "orderBy" => ["property" => "CREATED_ON", "descending" => true],
                        "limit" => 0
                    ]
                ]);

                $assets = json_decode($response->getBody()->getContents(), true);

                // Filter assets to only include those associated with the user
                $filteredAssets = array_filter($assets, function ($asset) use ($userAssetIds) {
                    return in_array($asset['id'], $userAssetIds);
                });

                // Convert timestamps to human-readable dates
                foreach ($filteredAssets as &$asset) {
                    if (isset($asset['createdOn'])) {
                        $asset['createdOn'] = Carbon::createFromTimestampMs($asset['createdOn'])->setTimezone('Asia/Jakarta')->toDateTimeString();
                    }
                }
                $userDashboards = AttributeDashboard::where('user_id', $user->id)->get();

                $usersWithAssetsAndDashboards[] = [
                    'user' => $user,
                    'assets' => $filteredAssets,
                    'dashboards' => $userDashboards,
                ];
            } catch (\Exception $e) {
                return view('admin.assets_users', ['usersWithAssetsAndDashboards' => $usersWithAssetsAndDashboards])->with('error', 'Failed to fetch assets: ' . $user->name . ' - ' . $e->getMessage());
            }
        }
    
        return view('admin.assets_users', compact('usersWithAssetsAndDashboards'));
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

        return view('admin.show', compact('dashboard', 'dataPoints', 'gaugeData', 'lineChartData', 'userAssets', 'userAssets', 'gaugeRanges'));
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
}

