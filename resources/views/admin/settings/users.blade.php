<x-layouts.app title="User Management">
    <x-slot:header>Settings</x-slot:header>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5 sm:mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">User Management</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Manage all users and their roles</p>
        </div>
        <div class="flex items-center gap-2">
            <select id="roleFilter" class="flex-1 sm:flex-none text-sm border border-border dark:border-dark-border bg-white dark:bg-dark-card rounded-lg px-3 py-2.5 sm:py-2 text-primary dark:text-dark-primary focus:outline-none focus:ring-2 focus:ring-accent/20">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="supervisor">Supervisor</option>
                <option value="cosupervisor">Co-Supervisor</option>
                <option value="student">Student</option>
            </select>
            <select id="statusFilter" class="flex-1 sm:flex-none text-sm border border-border dark:border-dark-border bg-white dark:bg-dark-card rounded-lg px-3 py-2.5 sm:py-2 text-primary dark:text-dark-primary focus:outline-none focus:ring-2 focus:ring-accent/20">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Mobile Card List --}}
    <div class="sm:hidden space-y-3" id="usersMobileList">
        @forelse($users as $user)
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 user-card-item" data-role="{{ $user->role }}" data-status="{{ $user->status }}">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-accent/10 text-accent flex items-center justify-center text-sm font-semibold shrink-0">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-primary dark:text-dark-primary truncate">{{ $user->name }}</p>
                            <x-status-badge :status="$user->status" size="sm" />
                        </div>
                        <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5 truncate">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            @if($user->role === 'admin')
                                <x-badge color="purple">Admin</x-badge>
                            @elseif($user->role === 'supervisor')
                                <x-badge color="blue">Supervisor</x-badge>
                            @elseif($user->role === 'cosupervisor')
                                <x-badge color="cyan">Co-Supervisor</x-badge>
                            @else
                                <x-badge color="gray">{{ ucfirst($user->role) }}</x-badge>
                            @endif
                            @if($user->isPro())
                                <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded-full bg-accent/15 text-accent leading-none">Pro</span>
                            @else
                                <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded-full bg-gray-100 dark:bg-dark-surface text-tertiary dark:text-dark-tertiary leading-none">Free</span>
                            @endif
                            @if($user->staff_id)
                                <span class="text-[10px] text-tertiary dark:text-dark-tertiary">Staff: {{ $user->staff_id }}</span>
                            @elseif($user->matric_number)
                                <span class="text-[10px] text-tertiary dark:text-dark-tertiary">Matric: {{ $user->matric_number }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($user->id !== auth()->id())
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-border dark:border-dark-border flex-wrap">
                    <div class="relative flex-1 min-w-[120px]">
                        <select
                            class="w-full text-xs border border-border dark:border-dark-border bg-white dark:bg-dark-card rounded-lg px-3 py-2 pr-8 cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none text-primary dark:text-dark-primary"
                            onchange="changeRole({{ $user->id }}, this)">
                            <option value="">Change Role</option>
                            <option value="admin" @if($user->role === 'admin') selected @endif>Admin</option>
                            <option value="supervisor" @if($user->role === 'supervisor') selected @endif>Supervisor</option>
                            <option value="cosupervisor" @if($user->role === 'cosupervisor') selected @endif>Co-Supervisor</option>
                            <option value="student" @if($user->role === 'student') selected @endif>Student</option>
                        </select>
                        <svg class="w-3 h-3 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="relative flex-1 min-w-[120px]">
                        <select
                            class="w-full text-xs border border-border dark:border-dark-border bg-white dark:bg-dark-card rounded-lg px-3 py-2 pr-8 cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none text-primary dark:text-dark-primary"
                            onchange="changeStatus({{ $user->id }}, this)">
                            <option value="">Change Status</option>
                            <option value="active" @if($user->status === 'active') selected @endif>Active</option>
                            <option value="pending" @if($user->status === 'pending') selected @endif>Pending</option>
                            <option value="inactive" @if($user->status === 'inactive') selected @endif>Inactive</option>
                        </select>
                        <svg class="w-3 h-3 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="relative flex-1 min-w-[100px]">
                        <select
                            class="w-full text-xs border border-border dark:border-dark-border bg-white dark:bg-dark-card rounded-lg px-3 py-2 pr-8 cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none text-primary dark:text-dark-primary"
                            onchange="changePlan({{ $user->id }}, this)"
                            @if($user->role === 'admin') disabled @endif>
                            <option value="free" @if(($user->plan ?? 'free') === 'free') selected @endif>Free</option>
                            <option value="pro" @if(($user->plan ?? 'free') === 'pro') selected @endif>Pro</option>
                        </select>
                        <svg class="w-3 h-3 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')"
                            class="p-2 rounded-lg text-secondary dark:text-dark-secondary hover:text-danger hover:bg-danger/10 transition-colors"
                            title="Delete user">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                @else
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-border dark:border-dark-border">
                    <span class="text-xs text-tertiary dark:text-dark-tertiary">This is your account</span>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8 text-center text-secondary dark:text-dark-secondary text-sm">
                No users found.
            </div>
        @endforelse

        @if($users->hasPages())
            <div class="pt-2">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Desktop Table --}}
    <x-card :padding='false' class="hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="usersTable">
                <thead>
                    <tr class="border-b border-border dark:border-dark-border bg-surface dark:bg-dark-surface">
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">User</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Role</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Plan</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Department</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Students</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Joined</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border dark:divide-dark-border">
                    @forelse($users as $user)
                        <tr class="hover:bg-surface/60 dark:hover:bg-dark-surface/60 transition-colors" data-role="{{ $user->role }}" data-status="{{ $user->status }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-primary dark:text-dark-primary">{{ $user->name }}</p>
                                        <p class="text-xs text-secondary dark:text-dark-secondary">{{ $user->email }}</p>
                                        @if($user->staff_id)
                                            <p class="text-xs text-gray-400 dark:text-dark-tertiary">Staff ID: {{ $user->staff_id }}</p>
                                        @elseif($user->matric_number)
                                            <p class="text-xs text-gray-400 dark:text-dark-tertiary">Matric: {{ $user->matric_number }}</p>
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
                            <td class="px-5 py-3">
                                @if($user->isPro())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-accent/15 text-accent">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                                        Pro
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-gray-100 dark:bg-dark-surface text-tertiary dark:text-dark-tertiary">Free</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-secondary dark:text-dark-secondary">{{ $user->department ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if(in_array($user->role, ['supervisor', 'cosupervisor']))
                                    <span class="text-sm text-primary dark:text-dark-primary">{{ $user->supervisedStudents->count() }}</span>
                                @elseif($user->role === 'student')
                                    <span class="text-xs text-secondary dark:text-dark-secondary">Student</span>
                                @else
                                    <span class="text-xs text-secondary dark:text-dark-secondary">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-secondary dark:text-dark-secondary text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3"><x-status-badge :status="$user->status" /></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-1">
                                    <div class="relative">
                                        <select
                                            class="text-xs border border-gray-200 dark:border-dark-border rounded-lg px-2 py-1.5 pr-6 cursor-pointer hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none bg-white dark:bg-dark-card text-primary dark:text-dark-primary"
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
                                    <div class="relative">
                                        <select
                                            class="text-xs border border-gray-200 dark:border-dark-border rounded-lg px-2 py-1.5 pr-6 cursor-pointer hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none bg-white dark:bg-dark-card text-primary dark:text-dark-primary"
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
                                    <div class="relative">
                                        <select
                                            class="text-xs border border-gray-200 dark:border-dark-border rounded-lg px-2 py-1.5 pr-6 cursor-pointer hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-accent/20 appearance-none bg-white dark:bg-dark-card text-primary dark:text-dark-primary"
                                            onchange="changePlan({{ $user->id }}, this)"
                                            @if($user->role === 'admin') disabled title="Admins always have Pro access" @endif>
                                            <option value="free" @if(($user->plan ?? 'free') === 'free') selected @endif>Free</option>
                                            <option value="pro" @if(($user->plan ?? 'free') === 'pro') selected @endif>Pro</option>
                                        </select>
                                        <svg class="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    @if($user->id !== auth()->id())
                                    <button onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="p-1.5 rounded-lg text-secondary dark:text-dark-secondary hover:text-danger hover:bg-danger/10 transition-colors"
                                            title="Delete user">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-secondary dark:text-dark-secondary text-sm">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-border dark:border-dark-border">
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

        function changePlan(userId, select) {
            const newPlan = select.value;
            if (!newPlan) return;

            if (confirm(`Change this user's plan to "${newPlan.toUpperCase()}"?`)) {
                select.disabled = true;

                fetch(`/admin/settings/users/${userId}/plan`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ plan: newPlan })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message) {
                        location.reload();
                    } else {
                        alert('Failed to update plan');
                        select.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Error updating plan');
                    select.disabled = false;
                });
            }
        }

        function deleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to delete "${userName}"? This action cannot be undone.`)) return;

            fetch(`/admin/settings/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete user');
                }
            })
            .catch(() => alert('Error deleting user'));
        }

        // Filter functionality - works for both mobile and desktop
        document.getElementById('roleFilter').addEventListener('change', filterTable);
        document.getElementById('statusFilter').addEventListener('change', filterTable);

        function filterTable() {
            const roleValue = document.getElementById('roleFilter').value;
            const statusValue = document.getElementById('statusFilter').value;

            // Desktop table rows
            const rows = document.querySelectorAll('#usersTable tbody tr');
            rows.forEach(row => {
                const rowRole = row.dataset.role;
                const rowStatus = row.dataset.status;
                const roleMatch = !roleValue || rowRole === roleValue;
                const statusMatch = !statusValue || rowStatus === statusValue;
                row.style.display = (roleMatch && statusMatch) ? '' : 'none';
            });

            // Mobile cards
            const cards = document.querySelectorAll('.user-card-item');
            cards.forEach(card => {
                const cardRole = card.dataset.role;
                const cardStatus = card.dataset.status;
                const roleMatch = !roleValue || cardRole === roleValue;
                const statusMatch = !statusValue || cardStatus === statusValue;
                card.style.display = (roleMatch && statusMatch) ? '' : 'none';
            });
        }
    </script>
</x-layouts.app>
