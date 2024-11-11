<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Collaborator;
use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;
use App\Models\AttributeDashboard;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::where('user_id', Auth::id())->get();
        return view('classrooms.index', compact('classrooms'));
    }

    public function fetchClassrooms()
    {
        $classrooms = Classroom::where('user_id', Auth::id())->get();
        return response()->json($classrooms);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        // Create the classroom
        $classroom = Classroom::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
            'description' => $request->description,
        ]);

        // Automatically add the admin as a collaborator
        $collaborator = Collaborator::create([
            'classroom_id' => $classroom->id,
            'user_id' => Auth::id(),
            'is_admin' => true, // Set is_admin to true explicitly
        ]);
         // Publish to Kafka
        // Kafka::publish('localhost:9092')->onTopic('classroom_updates')
        // ->withBodyKey('classroom_created', $classroom)
        // ->send();

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function destroy($id)
    {
        $classroom = Classroom::findOrFail($id);
        if ($classroom->user_id === Auth::id()) {
            $classroom->delete();
            // Publish to Kafka
            // Kafka::publish('localhost:9092')->onTopic('classroom_updates')
            //     ->withBodyKey('classroom_deleted', ['id' => $id])
            //     ->send();

            return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
        }
        return redirect()->route('classrooms.index')->with('error', 'Unauthorized action.');
    }
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Find the classroom by ID
        $classroom = Classroom::findOrFail($id);
        $classroom->name = $request->input('name');
        $classroom->description = $request->input('description');
        $classroom->save();

        // Publish to Kafka
        // Kafka::publish('localhost:9092')->onTopic('classroom_updates')
        //     ->withBodyKey('classroom_updated', $classroom)
        //     ->send();

        // Redirect back with a success message
        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }
    public function show($id)
    {
        // Fetch the classroom by ID
        $classroom = Classroom::with('groups', 'collaborators.user')->findOrFail($id);

        $dashboards = AttributeDashboard::where('user_id', Auth::id())->get();
        
        // Return a view with classroom details
        return view('classrooms.show', compact('classroom', 'dashboards'));
    }
    // Add a collaborator to a classroom
    public function addCollaborator(Request $request, $classroomId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $classroom = Classroom::findOrFail($classroomId);
        $userId = $request->user_id;

        // Check if the user is already a collaborator
        $existingCollaborator = Collaborator::where('classroom_id', $classroom->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingCollaborator) {
            return response()->json(['error' => 'User is already a collaborator.']);
        }

        $collaborator = Collaborator::create([
            'classroom_id' => $classroom->id,
            'user_id' => $userId,
            'is_admin' => false,
        ]);

        $collaborator->user = $collaborator->user()->first();

        return response()->json(['success' => true, 'collaborator' => $collaborator]);
    }
    // Remove a collaborator from a classroom
    public function removeCollaborator($id)
    {
        $collaborator = Collaborator::findOrFail($id);
        $collaborator->delete();

        return back()->with('success', 'Collaborator removed successfully.');
    }

    public function collaborations()
    {
        // Get the list of classrooms where the current user is a collaborator
        $classrooms = Classroom::whereHas('collaborators', function ($query) {
            $query->where('user_id', Auth::id());
        })->get();

        return view('classrooms.collaborations', compact('classrooms'));
    }

}
