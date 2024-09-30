<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Assignment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg relative">
                <!-- Edit and Delete buttons -->
                <div class="absolute top-4 right-4 flex space-x-2">
                    <button data-id="{{ $assignment->id }}" data-title="{{ $assignment->title }}" data-note="{{ $assignment->note }}" 
                            data-file="{{ $assignment->file_path }}" data-dashboard="{{ $assignment->dashboard }}" 
                            class="edit-assignment text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                        <span class="material-icons">edit</span>
                    </button>
                    <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this assignment?')">
                            <span class="material-icons">delete_forever</span>
                        </button>
                    </form>
                </div>

                <!-- Assignment details in table format -->
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $assignment->title }}</h3>
                    <table class="min-w-full mt-4 border border-gray-300 dark:border-gray-700">
                        <tbody class="bg-white dark:bg-gray-800">
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-300">Created by</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">: {{ $assignment->user->name }}</td>
                            </tr>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-300">Note</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">: {{ $assignment->note }}</td>
                            </tr>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-300">Uploaded File</td>
                                <td class="px-4 py-2">
                                    @if($assignment->file_path)
                                        <span class="text-gray-700 dark:text-gray-300">: </span>
                                        <a href="{{ Storage::url($assignment->file_path) }}" class="text-blue-500 hover:underline" target="_blank">
                                            {{ basename($assignment->file_path) }}
                                        </a>
                                    @else
                                        <span class="px-4 py-2 text-gray-700 dark:text-gray-300">: No file uploaded.</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-300">Dashboard</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                    : @if($dashboardId)
                                    <a href="{{ route('classrooms.dashboard', $dashboardId) }}" class="text-blue-500 hover:underline">
                                        {{ $assignment->dashboard }}
                                    </a>
                                @else
                                    {{ 'Dashboard not found' }}
                                @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-300">Created At</td>
                                <td class="px-4 py-2 text-gray-700 dark:text-gray-300">: {{ $assignment->created_at->format('H:i:s d-m-Y') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-2">
                        <a href="{{ route('classrooms.show', $assignment->group->classroom_id) }}" class="inline-block mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg">Back to Classroom</a>
                    </div>
                    <!-- Replies Section -->
                    <div class="mt-2">
                        <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300">Replies</h3>
                        @foreach($replies as $reply)
                            <div class="bg-gray-100 dark:bg-gray-700 p-3 border border-gray-300 dark:border-gray-700 mb-4 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <p class="text-lg font-medium text-blue-700 dark:text-blue-300 flex items-center">
                                        <span class="material-icons mr-2">account_circle</span>{{ $reply->user->name }}</p>
                                    <div class="flex space-x-2">
                                        <button data-id="{{ $reply->id }}" data-reply="{{ $reply->reply }}" 
                                                class="edit-reply text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            <span class="material-icons">edit</span>
                                        </button>
                                        <form action="{{ route('replies.destroy', $reply->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" 
                                                    onclick="return confirm('Are you sure you want to delete this reply?')">
                                                <span class="material-icons">delete_forever</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $reply->reply }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $reply->created_at->format('H:i:s d-m-Y') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reply form -->
                    <div class="mt-2">
                        <form action="{{ route('assignments.replies.store', $assignment) }}" method="POST" class="flex items-start space-x-2">
                            @csrf
                            <textarea name="reply" id="replyTextarea" placeholder="Write your reply here..." 
                                      class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2 resize-none overflow-hidden" 
                                      rows="1" style="height: auto;" required></textarea>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-1 flex items-center">
                                <span class="material-icons">
                                    send
                                </span>
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form for editing an assignment -->
    <div id="assignmentEditPopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form id="assignmentEditForm" method="POST" enctype="multipart/form-data" class="p-10">
                @csrf
                @method('PUT')
                <input type="hidden" name="_method" value="PUT">
                <div class="mb-4">
                    <label for="assignmentEditTitle" class="block text-lg font-medium text-gray-700">Title:</label>
                    <input type="text" name="title" id="assignmentEditTitle" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label for="assignmentEditNote" class="block text-lg font-medium text-gray-700">Note:</label>
                    <textarea name="note" id="assignmentEditNote" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mb-4">
                    <label for="assignmentEditFile" class="block text-lg font-medium text-gray-700">Upload File:</label>
                    <input type="file" name="file" id="assignmentEditFile" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                    {{-- <p id="assignmentEditFileName"></p> --}}
                </div>
                <div class="mb-4">
                    <label for="assignmentEditDashboard" class="block text-lg font-medium text-gray-700">Dashboard:</label>
                    <select name="dashboard" id="assignmentEditDashboard" class="form-select mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Dashboard</option>
                        @foreach($dashboards as $dashboard)
                            <option value="{{ $dashboard->name }}" {{ $assignment->dashboard === $dashboard->name ? 'selected' : '' }}>
                                {{ $dashboard->name }}
                            </option>
                        @endforeach
                    </select>
                </div>                
                <div class="mt-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Update Assignment</button>
                    <button id="closeAssignmentEditFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Popup Form for Editing a Reply -->
    <div id="replyEditPopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96 p-6">
            <form id="replyEditForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <textarea name="reply" id="replyEditContent" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required></textarea>
                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Update Reply</button>
                    <button id="closeReplyEditFormButton" type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const replyTextarea = document.getElementById('replyTextarea');

        replyTextarea.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        // edit assignment
        function openAssignmentEditForm(assignmentId, title, note, file, dashboard) {
            document.getElementById('assignmentEditPopupForm').classList.remove('hidden');
            document.getElementById('assignmentEditForm').action = `/assignments/${assignmentId}/update`; 
            document.getElementById('assignmentEditTitle').value = title;
            document.getElementById('assignmentEditNote').value = note;
            document.getElementById('assignmentEditDashboard').value = dashboard;
            if (file) {
                document.getElementById('assignmentEditFileName').textContent = file;
            }
        }
        function closeAssignmentEditForm() {
            document.getElementById('assignmentEditPopupForm').classList.add('hidden');
        }
        document.getElementById('closeAssignmentEditFormButton').addEventListener('click', closeAssignmentEditForm);

        document.querySelectorAll('.edit-assignment').forEach(button => {
            button.addEventListener('click', function() {
                const assignmentId = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const note = this.getAttribute('data-note');
                const file = this.getAttribute('data-file');
                const dashboard = this.getAttribute('data-dashboard');
                openAssignmentEditForm(assignmentId, title, note, file, dashboard);
            });
        });
        // edit reply
        function openReplyEditForm(replyId, replyContent) {
            document.getElementById('replyEditPopupForm').classList.remove('hidden');
            document.getElementById('replyEditForm').action = `/replies/${replyId}/update`; // Update form action
            document.getElementById('replyEditContent').value = replyContent; // Set current reply content in textarea
        }

        function closeReplyEditForm() {
            document.getElementById('replyEditPopupForm').classList.add('hidden');
        }

        document.getElementById('closeReplyEditFormButton').addEventListener('click', closeReplyEditForm);

        document.querySelectorAll('.edit-reply').forEach(button => {
            button.addEventListener('click', function() {
                const replyId = this.getAttribute('data-id');
                const replyContent = this.getAttribute('data-reply');
                openReplyEditForm(replyId, replyContent);
            });
        });
    </script>
</x-app-layout>
