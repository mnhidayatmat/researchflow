<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->id === $student->user_id) return true;
        if ($user->id === $student->supervisor_id) return true;
        if ($user->id === $student->cosupervisor_id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->id === $student->user_id) return true;
        if ($user->id === $student->supervisor_id) return true;
        if ($user->id === $student->cosupervisor_id) return true;
        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isAdmin();
    }
}
