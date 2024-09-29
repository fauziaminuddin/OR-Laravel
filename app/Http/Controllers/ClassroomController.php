<?php

namespace App\Http\Controllers;

use App\Models\AttributeDashboard;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::where('user_id', Auth::id())->get();
        return view('classrooms.index', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);

        Classroom::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
            'description' => $request->description,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function destroy($id)
    {
        $classroom = Classroom::findOrFail($id);
        if ($classroom->user_id === Auth::id()) {
            $classroom->delete();
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

        // Update the classroom details
        $classroom->name = $request->input('name');
        $classroom->description = $request->input('description');
        
        // Save the updated classroom
        $classroom->save();

        // Redirect back with a success message
        return redirect()->route('classrooms.index')->with('success', 'Classroom updated successfully.');
    }
    public function show($id)
    {
        // Fetch the classroom by ID
        $classroom = Classroom::with('groups')->findOrFail($id);

        $dashboards = AttributeDashboard::where('user_id', Auth::id())->get();
        
        // Return a view with classroom details
        return view('classrooms.show', compact('classroom', 'dashboards'));
    }
}
