<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Junges\Kafka\Facades\Kafka;
use App\Models\Assignment;

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
        // Publish to Kafka
        Kafka::publish('localhost:9092')->onTopic('group_updates')
            ->withBodyKey('group_created', [
                'id' => $group->id,
                'name' => $group->name,
                'classroom_id' => $classroom->id,
                'created_at' => $group->created_at,
                'updated_at' => $group->updated_at,
            ])
            ->send();

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
        // Publish to Kafka
        Kafka::publish('localhost:9092')->onTopic('group_updates')
            ->withBodyKey('group_updated', [
                'id' => $group->id,
                'name' => $group->name,
                'classroom_id' => $group->classroom_id,
                'updated_at' => $group->updated_at,
            ])
            ->send();

        return redirect()->route('classrooms.show', $group->classroom_id)->with('success', 'Group updated successfully.');
    }

    // Method to delete a group
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $classroomId = $group->classroom_id;
        $group->delete();
        // Publish to Kafka
        Kafka::publish('localhost:9092')->onTopic('group_updates')
            ->withBodyKey('group_deleted', [
                'id' => $group->id,
                'classroom_id' => $classroomId,
            ])
            ->send();

        return redirect()->route('classrooms.show', $classroomId)->with('success', 'Group deleted successfully.');
    }
    public function getMessages()
    {
        try {
            // Fetch group messages with assignments and users
            $groupMessages = Group::with('assignments.user')->get(); 
            $assignmentMessages = Assignment::with('user')->get();
    
            $messages = [
                'groupMessages' => $groupMessages,
                'assignmentMessages' => $assignmentMessages,
            ];
    
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
