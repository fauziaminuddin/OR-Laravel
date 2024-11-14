<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Classroom Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Success and error alerts -->
                    @if(session('success'))
                        <div class="alert alert-success" id="successAlert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" id="errorAlert">
                            {{ session('error') }}
                        </div>
                    @endif
                    <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300" style="text-align: center;">{{ $classroom->name }}</h3>
                    <p class="mt-4 text-gray-700 dark:text-gray-300" style="text-align: center;">{{ $classroom->description }}</p>
                    <!-- Admin buttons -->
                    @if(auth()->user()->is_admin)
                        <div style="margin-top: -40px;">
                            <button id="openGroupFormButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <span class="text-xl">+</span> Create Group
                            </button>
                            <!-- Collaboration Button -->
                            <div style="margin-top: 10px;">
                                <button id="openCollabFormButton" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Add Student
                                </button>
                            </div>
                        </div>
                    @endif
                        <hr class="my-4">
                        <h4 class="text-xl font-semibold mb-2 text-blue-700 dark:text-blue-300">Groups</h4>
                    <div id="messages-list" class="mt-4">
                        <ul>
                            @foreach($classroom->groups as $group)
                            <div id="messages-container" class="border p-2 mb-4 rounded-lg shadow">
                                <li class="flex py-2">
                                    <span class="text-lg font-bold text-gray-700 dark:text-gray-200" style="padding-left: 20px;">{{ $group->name }}</span>
                                    @if(auth()->user()->is_admin)
                                    <div>
                                        <button data-id="{{ $group->id }}" data-name="{{ $group->name }}" class="edit-group text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" style="padding-left: 40px;">
                                            <span class="material-icons">edit</span>
                                        </button>
                                        <form action="{{ route('groups.destroy', $group->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this group?')">
                                                <span class="material-icons">delete_forever</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                                </li>
                                <!-- Assignment Section -->
                                    <div class="" style="padding-left: 40px;">                                      
                                        <ul>
                                            @foreach($group->assignments as $assignment)
                                                <li class="flex py-2">
                                                    <a href="{{ route('classrooms.assign', $assignment->id) }}" class="text-gray-700 dark:text-gray-200 flex items-center hover:underline">
                                                        - {{ $assignment->title }}
                                                    </a>
                                                    <span style="padding-left: 20px"></span>
                                                    @if($assignment->file_path)
                                                        <span class="material-icons bg-orange-200 text-orange-800 rounded-lg px-2 py-1">
                                                            description
                                                        </span>
                                                        {{-- <a href="{{ Storage::url($assignment->file_path) }}" target="_blank">Download/View File</a> --}}
                                                    @else
                                                        {{-- <p>No file uploaded.</p> --}}
                                                    @endif
                                                    @if($assignment->dashboard)
                                                    <span style="padding-left: 10px"></span>                                       
                                                        <span class="material-icons bg-yellow-200 text-yellow-800 rounded-lg px-2 py-1">
                                                            insights
                                                        </span>
                                                    @endif
                                                    <span  style="padding-left: 10px"></span>
                                                    <div class="bg-blue-200 text-blue-800 rounded-lg px-2 py-1 flex items-center">
                                                        <span class="material-icons mr-2">account_circle</span>
                                                        {{ $assignment->user->name }}
                                                    </div>
                                                    <span  style="padding-left: 10px"></span>
                                                    <div class="bg-green-200 text-green-800 rounded-lg px-2 py-1 flex items-center">
                                                        <span class="material-icons mr-2">
                                                            schedule</span>
                                                            {{ $assignment->created_at->format('H:i:s d-m-Y') }}
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2 open-assignment-create-form" data-group-id="{{ $group->id }}">
                                            <span class="text-xl">+</span> Create Assignment
                                        </button>
                                    </div>
                                    {{-- <hr class="my-4"> --}}
                                </div>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Form for creating a new group -->
    <div id="popupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form action="{{ route('groups.store', $classroom->id) }}" method="POST" class="p-10">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-lg font-medium text-gray-700">Group Name:</label>
                    <input type="text" name="name" id="name" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Group</button>
                    <button id="closeFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popup Form for editing a group -->
    <div id="editPopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form id="editGroupForm" method="POST" class="p-10">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="editName" class="block text-lg font-medium text-gray-700">Group Name:</label>
                    <input type="text" name="name" id="editName" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Update Group</button>
                    <button id="closeEditFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Popup Form for creating a new assignment -->
    <div id="assignmentCreatePopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg w-96">
            <form id="assignmentCreateForm" method="POST" enctype="multipart/form-data" class="p-10">
                @csrf
                <div class="mb-4">
                    <label for="assignmentCreateTitle" class="block text-lg font-medium text-gray-700">Title:</label>
                    <input type="text" name="title" id="assignmentCreateTitle" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label for="assignmentCreateNote" class="block text-lg font-medium text-gray-700">Note:</label>
                    <textarea name="note" id="assignmentCreateNote" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2"></textarea>
                </div>
                <div class="mb-4">
                    <label for="assignmentCreateFile" class="block text-lg font-medium text-gray-700">Upload File:</label>
                    <input type="file" name="file" id="assignmentCreateFile" class="form-input mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div class="mb-4">
                    <label for="assignmentCreateDashboard" class="block text-lg font-medium text-gray-700">Dashboard:</label>
                    <select name="dashboard" id="assignmentCreateDashboard" class="form-select mt-1 block w-full text-lg border border-gray-300 rounded-lg px-3 py-2">
                        <option value="" disabled selected>Select a dashboard</option>
                        @foreach($dashboards as $dashboard)
                            <option value="{{ $dashboard->id }}">{{ $dashboard->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Create Assignment</button>
                    <button id="closeAssignmentCreateFormButton" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Collaborator Management Popup -->
<div id="collabPopupForm" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg w-96 p-6">
        <h2 class="text-xl font-bold mb-4">Manage Students</h2>

        <!-- List of Current Collaborators -->
        <div id="collaboratorsList">
            <h3 class="text-lg mb-2">Students List</h3>
            <ul>
                @foreach($classroom->collaborators as $collaborator)
                    <li class="mb-2 flex justify-between items-center">
                        @if($collaborator->user)
                            <span>{{ $collaborator->user->name }}</span>
                        @else
                            <span>No student found</span>
                        @endif

                        <!-- Only show Remove button if the collaborator is not an admin -->
                        @if(!$collaborator->is_admin)
                            <form action="{{ route('collaborators.destroy', $collaborator->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this student?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Add New Collaborator -->
        <div class="mt-6">
            <h3 class="text-lg mb-2">Add Student</h3>
            <input type="text" id="studentSearch" placeholder="Search student..." class="form-input mb-4 w-full">
            <ul id="studentResults"></ul>
        </div>

        <button id="closeCollabFormButton" class="mt-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
            Close
        </button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set interval to fetch messages every 5 seconds
        setInterval(fetchMessages, 5000);

        function fetchMessages() {
            const classroomId = {{ $classroom->id }}; // Ambil classroom ID dari Blade
            
            fetch(`/groups/messages/${classroomId}`)
                .then(response => response.json())
                .then(messages => {
                    const messagesContainer = document.querySelector("#messages-list");
                    messagesContainer.innerHTML = ""; // Bersihkan konten lama

                    // Render setiap pesan group
                    messages.groupMessages.forEach(group => {
                        const groupItem = document.createElement("div");
                        groupItem.classList.add("border", "p-2", "mb-4", "rounded-lg", "shadow");
                        groupItem.innerHTML = `
                            <li class="flex py-2">
                                <span class="text-lg font-bold text-gray-700 dark:text-gray-200" style="padding-left: 20px;">
                                    ${escapeHtml(group.name)}
                                </span>
                                ${group.user_id === {{ Auth::id() }} || {{ auth()->user()->isAdmin() }} ? `
                                <div>
                                    <button data-id="${group.id}" data-name="${escapeHtml(group.name)}" class="edit-group text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300" style="padding-left: 40px;">
                                        <span class="material-icons">edit</span>
                                    </button>
                                    <form action="/groups/${group.id}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" onclick="return confirm('Are you sure you want to delete this group?')">
                                            <span class="material-icons">delete_forever</span>
                                        </button>
                                    </form>
                                </div>
                                ` : ''}
                            </li>
                            <div class="assignments-list" style="padding-left: 40px;">
                                <ul>
                                    ${group.assignments.map(assignment => `
                                        <li class="flex py-2">
                                            <a href="/assignments/${assignment.id}" class="text-gray-700 dark:text-gray-200 flex items-center hover:underline">
                                                - ${escapeHtml(assignment.title)}
                                            </a>
                                            <span style="padding-left: 20px"></span>
                                            ${assignment.file_path ? `<span class="material-icons bg-orange-200 text-orange-800 rounded-lg px-2 py-1">description</span>` : ''}
                                            ${assignment.dashboard ? `<span style="padding-left: 10px"></span><span class="material-icons bg-yellow-200 text-yellow-800 rounded-lg px-2 py-1">insights</span>` : ''}
                                            <span style="padding-left: 10px"></span>
                                            <div class="bg-blue-200 text-blue-800 rounded-lg px-2 py-1 flex items-center">
                                                <span class="material-icons mr-2">account_circle</span>
                                                ${escapeHtml(assignment.user.name)}
                                            </div>
                                            <span style="padding-left: 10px"></span>
                                            <div class="bg-green-200 text-green-800 rounded-lg px-2 py-1 flex items-center">
                                                <span class="material-icons mr-2">schedule</span>
                                                ${escapeHtml(new Date(assignment.created_at).toLocaleString())}
                                            </div>
                                        </li>
                                    `).join('')}
                                </ul>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2 open-assignment-create-form" data-group-id="${group.id}">
                                    <span class="text-xl">+</span> Create Assignment
                                </button>
                            </div>
                        `;
                        messagesContainer.appendChild(groupItem);
                    });
                    
                    attachEditButtonListeners();

                    // Tambahkan listener untuk tombol "Create Assignment"
                    document.querySelectorAll('.open-assignment-create-form').forEach(button => {
                        button.addEventListener('click', function() {
                            const groupId = this.getAttribute('data-group-id');
                            openAssignmentCreateForm(groupId);
                        });
                    });
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });


    // <!-- JavaScript for Popup Forms -->
    function openForm() {
        document.getElementById('popupForm').classList.remove('hidden');
    }

    function closeForm() {
        document.getElementById('popupForm').classList.add('hidden');
    }

    function openEditForm(id, name) {
        document.getElementById('editPopupForm').classList.remove('hidden');
        document.getElementById('editGroupForm').action = `/groups/${id}`;
        document.getElementById('editName').value = name;
    }

    function closeEditForm() {
        document.getElementById('editPopupForm').classList.add('hidden');
    }

    function openAssignmentCreateForm(groupId) {
        document.getElementById('assignmentCreatePopupForm').classList.remove('hidden');
        document.getElementById('assignmentCreateForm').action = `/groups/${groupId}/assignments`;
    }

    function closeAssignmentCreateForm() {
        document.getElementById('assignmentCreatePopupForm').classList.add('hidden');
    }

    // Function to open the collaboration form
    function openCollabForm() {
        document.getElementById('collabPopupForm').classList.remove('hidden');
    }
    function attachEditButtonListeners() {
        document.querySelectorAll('.edit-group').forEach(button => {
            button.addEventListener('click', function() {
                const groupId = this.getAttribute('data-id');
                const groupName = this.getAttribute('data-name');
                openEditForm(groupId, groupName);
            });
        });
    }

    // Event listeners for group create/edit form
    const openGroupFormButton = document.getElementById('openGroupFormButton');
    if (openGroupFormButton) {
        openGroupFormButton.addEventListener('click', openForm);
    }

    document.getElementById('closeFormButton').addEventListener('click', closeForm);
    document.getElementById('closeEditFormButton').addEventListener('click', closeEditForm);

    // Event listeners for group edit buttons
    document.querySelectorAll('.edit-group').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.getAttribute('data-id');
            const groupName = this.getAttribute('data-name');
            openEditForm(groupId, groupName);
        });
    });

    // Event Listeners for Assignment Creation
    document.querySelectorAll('.open-assignment-create-form').forEach(button => {
        button.addEventListener('click', function() {
            const groupId = this.getAttribute('data-group-id');
            openAssignmentCreateForm(groupId);
        });
    });

    document.getElementById('closeAssignmentCreateFormButton').addEventListener('click', closeAssignmentCreateForm);
    
    // Event listener for the collaboration button
    const openCollabFormButton = document.getElementById('openCollabFormButton');
    if (openCollabFormButton) {
        openCollabFormButton.addEventListener('click', openCollabForm); // Corrected to openCollabForm
    }

    document.getElementById('closeCollabFormButton').addEventListener('click', function() {
        document.getElementById('collabPopupForm').classList.add('hidden');
    });

    //collab
    const csrfToken = '{{ csrf_token() }}';
    const classroomId = '{{ $classroom->id }}';

    // Handle search input
    document.getElementById('studentSearch').addEventListener('keyup', function() {
        let query = this.value;
        if (query.length > 2) {
            fetch(`/search-users?query=${query}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                let resultsList = document.getElementById('studentResults');
                resultsList.innerHTML = ''; // Clear previous results

                data.forEach(user => {
                    let listItem = document.createElement('li');
                    listItem.innerHTML = `${user.name} <button class="add-user" data-id="${user.id}">Add</button>`;
                    resultsList.appendChild(listItem);
                });

                // Attach event listener to the add buttons
                document.querySelectorAll('.add-user').forEach(button => {
                    button.addEventListener('click', function() {
                        let userId = this.getAttribute('data-id');
                        addCollaborator(userId);
                    });
                });
            })
            .catch(error => console.error('Error:', error));
        }
    });

    // Add collaborator function
    function addCollaborator(userId) {
        fetch(`/classrooms/${classroomId}/collaborators`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page
                location.reload();
            } else {
                alert(data.error); // Display the error message
            }
        })
        .catch(error => console.error('Error:', error));
    }

    setTimeout(function() {
            if (document.getElementById('successAlert')) {
                document.getElementById('successAlert').remove();
            }
        }, 5000); // 5000 milliseconds = 5 seconds

        setTimeout(function() {
            if (document.getElementById('errorAlert')) {
                document.getElementById('errorAlert').remove();
            }
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</x-app-layout>
