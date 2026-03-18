<x-layouts.app title="User Management">
    <x-slot:header>Settings</x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary">User Management</h2>
            <p class="text-xs text-secondary mt-0.5">Manage all users and their roles</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="roleFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent/20">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="supervisor">Supervisor</option>
                <option value="cosupervisor">Co-Supervisor</option>
                <option value="student">Student</option>
            </select>
            <select id="statusFilter" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-accent/20">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <x-card :padding='false'>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="usersTable">
                <thead>
                    <tr class="border-b border-border bg-surface">
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">User</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Role</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Department</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Students</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Joined</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($users as $user)
                        <tr class="hover:bg-surface/60 transition-colors" data-role="{{ $user->role }}" data-status="{{ $user->status }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-primary">{{ $user->name }}</p>
                                        <p class="text-xs text-secondary">{{ $user->email }}</p>
                                        @if($user->staff_id)
                                            <p class="text-xs text-gray-400">Staff ID: {{ $user->staff_id }}</p>
                                        @elseif($user->matric_number)
                                            <p class="text-xs text-gray-400">Matric: {{ $user->matric_number }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if($user->role === 'admin')
                                    <x-badge color="purple">Admin</x-badge>
                                @elseif($user->role === 'supervisor')
                                    <x-badge color="blue">Supervisor</x-badge>
                                @elseif($user->role === 'cosupervisor')
                                    <x-badge color="cyan">Co-Supervisor</x-badge>
                                @else
                                    <x-badge color="gray">{{ ucfirst($user->role) }}</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-secondary">{{ $user->department ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if(in_array($user->role, ['supervisor', 'cosupervisor']))
                                    <span class="text-sm text-primary">{{ $user->supervisedStudents->count() }}</span>
                                @elseif($user->role === 'student')
                                    <span class="text-xs text-secondary">Student</span>
                                @else
                                    <span class="text-xs text-secondary">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-secondary text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3"><x-status-badge :status="$user->status" /></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1">
                                    {{-- Role Dropdown --}}
                                    <div class="relative">
                                        <select
                                            class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 pr-6 cursor-pointer hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none bg-white"
                                            onchange="changeRole({{ $user->id }}, this)"
                                            @if($user->id === auth()->id()) disabled @endif>
                                            <option value="">Change Role</option>
                                            <option value="admin" @if($user->role === 'admin') selected @endif>Admin</option>
                                            <option value="supervisor" @if($user->role === 'supervisor') selected @endif>Supervisor</option>
                                            <option value="cosupervisor" @if($user->role === 'cosupervisor') selected @endif>Co-Supervisor</option>
                                            <option value="student" @if($user->role === 'student') selected @endif>Student</option>
                                        </select>
                                        <svg class="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>

                                    {{-- Status Dropdown --}}
                                    <div class="relative">
                                        <select
                                            class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 pr-6 cursor-pointer hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none bg-white"
                                            onchange="changeStatus({{ $user->id }}, this)"
                                            @if($user->id === auth()->id()) disabled @endif>
                                            <option value="">Change Status</option>
                                            <option value="active" @if($user->status === 'active') selected @endif>Active</option>
                                            <option value="pending" @if($user->status === 'pending') selected @endif>Pending</option>
                                            <option value="inactive" @if($user->status === 'inactive') selected @endif>Inactive</option>
                                        </select>
                                        <svg class="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-secondary text-sm">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-border">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </x-card>

    <script>
        function changeRole(userId, select) {
            const newRole = select.value;
            if (!newRole) return;

            if (confirm(`Are you sure you want to change this user's role to "${newRole}"?`)) {
                select.disabled = true;

                fetch(`/admin/settings/users/${userId}/role`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ role: newRole })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message) {
                        location.reload();
                    } else {
                        alert('Failed to update role');
                        select.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Error updating role');
                    select.disabled = false;
                });
            } else {
                select.value = '';
            }
        }

        function changeStatus(userId, select) {
            const newStatus = select.value;
            if (!newStatus) return;

            if (confirm(`Are you sure you want to change this user's status to "${newStatus}"?`)) {
                select.disabled = true;

                fetch(`/admin/settings/users/${userId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message) {
                        location.reload();
                    } else {
                        alert('Failed to update status');
                        select.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Error updating status');
                    select.disabled = false;
                });
            } else {
                select.value = '';
            }
        }

        // Filter functionality
        document.getElementById('roleFilter').addEventListener('change', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const roleValue = document.getElementById('roleFilter').value;
            const statusValue = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                const rowRole = row.dataset.role;
                const rowStatus = row.dataset.status;

                const roleMatch = !roleValue || rowRole === roleValue;
                const statusMatch = !statusValue || rowStatus === statusValue;

                row.style.display = (roleMatch && statusMatch) ? '' : 'none';
            });
        }
    </script>
</x-layouts.app>
