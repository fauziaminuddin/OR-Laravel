<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Classroom;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // Method to create a new group
    public function store(Request $request, $classroomId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $classroom = Classroom::findOrFail($classroomId);

        $group = new Group();
        $group->name = $request->input('name');
        $group->classroom_id = $classroom->id;
        $group->save();

        return redirect()->route('classrooms.show', $classroom->id)->with('success', 'Group created successfully.');
    }

    // Method to update an existing group
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group = Group::findOrFail($id);
        $group->name = $request->input('name');
        $group->save();

        return redirect()->route('classrooms.show', $group->classroom_id)->with('success', 'Group updated successfully.');
    }

    // Method to delete a group
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $classroomId = $group->classroom_id;
        $group->delete();

        return redirect()->route('classrooms.show', $classroomId)->with('success', 'Group deleted successfully.');
    }
}
