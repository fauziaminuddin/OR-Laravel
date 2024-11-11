<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Reply;
use GuzzleHttp\Client;
use App\Models\UserAsset;
use App\Models\Assignment;
use App\Models\GaugeRange;
use Illuminate\Http\Request;
use App\Models\AttributeDashboard;
use App\Services\OpenRemoteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Junges\Kafka\Facades\Kafka;


class AssignmentsController extends Controller
{
    // Store a new assignment
    public function store(Request $request, $groupId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048', // Only allow certain file types
            'dashboard' => 'nullable|string',
        ]);

        $group = Group::findOrFail($groupId);

        // Handle file upload if there's a file
        $filePath = null;
        if ($request->hasFile('file')) {
            $originalFileName = $request->file('file')->getClientOriginalName(); // Get the original file name
            $timestamp = time(); // Get the current timestamp
            $uniqueFileName = $timestamp . '_' . $originalFileName; // Prepend timestamp to the original filename
            $filePath = $request->file('file')->storeAs('assignments', $uniqueFileName, 'public'); // Store with unique name
        }

        // Create the new assignment
        $assignment = new Assignment();
        $assignment->group_id = $group->id;
        $assignment->title = $request->input('title');
        $assignment->note = $request->input('note');
        $assignment->file_path = $filePath;
        $assignment->dashboard = $request->input('dashboard');
        $assignment->user_id = Auth::id(); // Save the current user's ID
        $assignment->save();
        // Publish to Kafka
        // Kafka::publish('localhost:9092')->onTopic('assign_updates')
        //     ->withBodyKey('assign_created', [
        //         'id' => $assignment->id,
        //         'group_id' => $group->id,
        //         'title' => $assignment->title,
        //         'note' =>  $assignment->note,
        //         'file_path' => $assignment->file_path,
        //         'dashboard' => $assignment->dashboard,
        //         'created_at' => $assignment->created_at,
        //         'updated_at' => $assignment->updated_at,
        //         'user_id' => $assignment->user_id,
        //     ])
        //     ->send();

        return redirect()->route('classrooms.show', $group->classroom_id)->with('success', 'Assignment created successfully.');
    }

    // Update an existing assignment
    public function update(Request $request, $assignmentId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'note' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
            'dashboard' => 'nullable|string',
        ]);

        $assignment = Assignment::findOrFail($assignmentId);

        // Handle file upload if there's a new file
        if ($request->hasFile('file')) {
            // Delete the old file if it exists
            if ($assignment->file_path) {
                Storage::disk('public')->delete($assignment->file_path);
            }
            $originalFileName = $request->file('file')->getClientOriginalName(); // Get the original file name
            $timestamp = time(); // Get the current timestamp
            $uniqueFileName = $timestamp . '_' . $originalFileName; // Prepend timestamp to the original filename
            $assignment->file_path = $request->file('file')->storeAs('assignments', $uniqueFileName, 'public'); // Store with unique name
        }

        // Update the assignment details
        $assignment->title = $request->input('title');
        $assignment->note = $request->input('note');
        $assignment->dashboard = $request->input('dashboard');
        $assignment->save();
        // Publish to Kafka
        // Kafka::publish('localhost:9092')->onTopic('assign_updates')
        //     ->withBodyKey('assign_updated', [
        //         'id' => $assignment->id,
        //         'title' => $assignment->title,
        //         'note' =>  $assignment->note,
        //         'file_path' => $assignment->file_path,
        //         'dashboard' => $assignment->dashboard,
        //         'updated_at' => $assignment->updated_at,
        //     ])
        //     ->send();

        return redirect()->route('classrooms.assign', ['assignment' => $assignment])->with('success', 'Assignment updated successfully.');
    }

    // Delete an assignment
    public function destroy($assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);

        // Delete the file if it exists
        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $groupId = $assignment->group_id;
        $assignment->delete();
        // Publish to Kafka
        // Kafka::publish('localhost:9092')->onTopic('assign_updates')
        //     ->withBodyKey('assign_deleted', [
        //         'id' => $assignment->id,
        //     ])
        //     ->send();

        return redirect()->route('classrooms.show', $assignment->group->classroom_id)->with('success', 'Assignment deleted successfully.');
    }
    public function show(Assignment $assignment)
    {
        $dashboards = AttributeDashboard::where('user_id', Auth::id())->get(); // Fetch dashboards for the logged-in user

     // Check if the dashboard field is numeric (an ID)
    if (is_numeric($assignment->dashboard)) {
        // Fetch the dashboard by ID
        $dashboard = AttributeDashboard::find($assignment->dashboard);
        $dashboardId = $dashboard ? $dashboard->id : null; // Get the ID if found
        $dashboardName = $dashboard ? $dashboard->name : null; // Get the name if found
    } else {
        // Otherwise, treat it as a name and fetch the ID based on the name
        $dashboardId = optional(AttributeDashboard::where('name', trim($assignment->dashboard))->first())->id;
        $dashboardName = optional(AttributeDashboard::where('name', trim($assignment->dashboard))->first())->name;
    }
        $replies = Reply::where('assignment_id', $assignment->id)
                ->orderBy('created_at', 'asc')
                ->get();

        return view('classrooms.assign', compact('assignment', 'dashboards', 'dashboardId', 'replies', 'dashboardName'));
    }
    public function fetchReplies(Assignment $assignment)
    {
        $replies = Reply::where('assignment_id', $assignment->id)
            ->orderBy('created_at', 'asc')
            ->with('user')->get();

        return response()->json($replies);
    }

    public function storeReply(Request $request, Assignment $assignment)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $reply = new Reply();
        $reply->assignment_id = $assignment->id;
        $reply->user_id = Auth::id();
        $reply->reply = $request->input('reply');
        $reply->save();

        return redirect()->route('classrooms.assign', $assignment)->with('success', 'Reply created successfully.');
    }
    public function updateReply(Request $request, Reply $reply)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        // Ensure the authenticated user is the owner of the reply
        if ($reply->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reply->reply = $request->input('reply');
        $reply->save();

        return redirect()->route('classrooms.assign', $reply->assignment_id)->with('success', 'Reply updated successfully.');
    }

    // Delete a reply
    public function destroyReply(Reply $reply)
    {
        // Ensure the authenticated user is the owner of the reply
        if ($reply->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $reply->delete();

        return redirect()->route('classrooms.assign', $reply->assignment_id)->with('success', 'Reply deleted successfully.');
    }

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

    public function showDash($id)
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

        return view('classrooms.dashboard', compact('dashboard', 'dataPoints', 'gaugeData', 'lineChartData', 'userAssets', 'gaugeRanges'));
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

}
