<?php

namespace App\Support;

/**
 * Sidebar navigation per role. Every item points at a real registered route.
 */
class Navigation
{
    public static function for(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                ['heading' => 'Platform'],
                ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => 'super.dashboard'],
                ['label' => 'Institutes', 'icon' => 'building', 'route' => 'super.institutes.index'],
                ['label' => 'Users', 'icon' => 'people', 'route' => 'super.users.index'],
                ['heading' => 'System'],
                ['label' => 'Plugin manager', 'icon' => 'puzzle', 'route' => 'super.plugins.index'],
                ['label' => 'Backups', 'icon' => 'database-down', 'route' => 'super.backups.index'],
                ['label' => 'Recycle bin', 'icon' => 'trash3', 'route' => 'super.recycle.index'],
                ['label' => 'Activity log', 'icon' => 'activity', 'route' => 'super.activity.index'],
                ['label' => 'Settings', 'icon' => 'gear', 'route' => 'super.settings.edit'],
            ],
            'institute_admin' => [
                ['heading' => 'Overview'],
                ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => 'admin.dashboard'],
                ['label' => 'Reports', 'icon' => 'bar-chart', 'route' => 'admin.reports.index'],
                ['heading' => 'Academics'],
                ['label' => 'Courses', 'icon' => 'journal-bookmark', 'route' => 'admin.courses.index'],
                ['label' => 'Batches', 'icon' => 'collection', 'route' => 'admin.batches.index'],
                ['label' => 'Lessons', 'icon' => 'file-earmark-text', 'route' => 'admin.lessons.index'],
                ['label' => 'Class schedule', 'icon' => 'calendar-event', 'route' => 'admin.class-sessions.index'],
                ['label' => 'Enrollments', 'icon' => 'person-plus', 'route' => 'admin.enrollments.index'],
                ['heading' => 'People'],
                ['label' => 'Students', 'icon' => 'mortarboard', 'route' => 'admin.students.index'],
                ['label' => 'Teachers', 'icon' => 'person-workspace', 'route' => 'admin.teachers.index'],
                ['label' => 'Guardians', 'icon' => 'people', 'route' => 'admin.guardians.index'],
                ['heading' => 'Assessment'],
                ['label' => 'Attendance', 'icon' => 'check2-square', 'route' => 'admin.attendance.index'],
                ['label' => 'Assignments', 'icon' => 'clipboard-check', 'route' => 'admin.assignments.index'],
                ['label' => 'Examinations', 'icon' => 'pencil-square', 'route' => 'admin.exams.index'],
                ['label' => 'Certificates', 'icon' => 'award', 'route' => 'admin.certificates.index'],
                ['heading' => 'Finance & content'],
                ['label' => 'Payments', 'icon' => 'credit-card', 'route' => 'admin.payments.index'],
                ['label' => 'Announcements', 'icon' => 'megaphone', 'route' => 'admin.announcements.index'],
                ['label' => 'Pages', 'icon' => 'window-stack', 'route' => 'admin.pages.index'],
                ['label' => 'Menus', 'icon' => 'list', 'route' => 'admin.menus.index'],
                ['heading' => 'Configuration'],
                ['label' => 'Plugins', 'icon' => 'puzzle', 'route' => 'admin.plugins.index'],
                ['label' => 'Branding & SEO', 'icon' => 'palette', 'route' => 'admin.settings.edit'],
                ['label' => 'Backups', 'icon' => 'database-down', 'route' => 'admin.backups.index'],
                ['label' => 'Recycle bin', 'icon' => 'trash3', 'route' => 'admin.recycle.index'],
            ],
            'teacher' => [
                ['heading' => 'Teaching'],
                ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => 'teacher.dashboard'],
                ['label' => 'My batches', 'icon' => 'collection', 'route' => 'teacher.batches.index'],
                ['label' => 'Class schedule', 'icon' => 'calendar-event', 'route' => 'teacher.classes.index'],
                ['label' => 'Attendance', 'icon' => 'check2-square', 'route' => 'teacher.attendance.index'],
                ['label' => 'Assignments', 'icon' => 'clipboard-check', 'route' => 'teacher.assignments.index'],
                ['label' => 'Examinations', 'icon' => 'pencil-square', 'route' => 'teacher.exams.index'],
            ],
            'student' => [
                ['heading' => 'Learning'],
                ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => 'student.dashboard'],
                ['label' => 'My courses', 'icon' => 'journal-bookmark', 'route' => 'student.courses.index'],
                ['label' => 'Class schedule', 'icon' => 'calendar-event', 'route' => 'student.classes.index'],
                ['label' => 'Assignments', 'icon' => 'clipboard-check', 'route' => 'student.assignments.index'],
                ['label' => 'Results', 'icon' => 'graph-up', 'route' => 'student.results.index'],
                ['label' => 'Payments', 'icon' => 'credit-card', 'route' => 'student.payments.index'],
                ['label' => 'Certificates', 'icon' => 'award', 'route' => 'student.certificates.index'],
            ],
            'guardian' => [
                ['heading' => 'Family'],
                ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => 'guardian.dashboard'],
                ['label' => 'My children', 'icon' => 'people', 'route' => 'guardian.children.index'],
            ],
            default => [],
        };
    }
}
