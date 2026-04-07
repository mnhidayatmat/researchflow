<?php

namespace App\Policies;

use App\Models\ProgressReport;
use App\Models\User;

class ProgressReportPolicy
{
    public function view(User $user, ProgressReport $report): bool
    {
        if ($user->isAdmin()) return true;
        $student = $report->student;
        return $user->id === $student->user_id
            || $user->id === $student->supervisor_id
            || $user->id === $student->cosupervisor_id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent() || $user->isAdmin();
    }

    public function update(User $user, ProgressReport $report): bool
    {
        if ($user->isAdmin()) return true;
        return $user->id === $report->student->user_id;
    }

    public function delete(User $user, ProgressReport $report): bool
    {
        if ($user->isAdmin()) return true;
        return $user->id === $report->student->user_id;
    }

    public function manageAttachment(User $user, ProgressReport $report): bool
    {
        if ($user->isAdmin()) return true;
        return $user->id === $report->student->user_id;
    }

    public function review(User $user, ProgressReport $report): bool
    {
        if ($user->isAdmin()) return true;
        $student = $report->student;
        return $user->id === $student->supervisor_id || $user->id === $student->cosupervisor_id;
    }
}
