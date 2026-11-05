<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Mempercepat WHERE lecturer_id/status dan pengurutan created_at pada dashboard dosen.
            $table->index(['lecturer_id', 'status', 'created_at'], 'courses_lecturer_status_created_at_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            // Mempercepat daftar materi berdasarkan course_id dan pengurutan terbaru.
            $table->index(['course_id', 'created_at'], 'materials_course_created_at_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            // Mempercepat filter course_id/status dan pengurutan daftar tugas.
            $table->index(['course_id', 'status', 'created_at'], 'assignments_course_status_created_at_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            // Mempercepat pengambilan submission milik user dan pengurutan submitted_at.
            $table->index(['user_id', 'submitted_at'], 'submissions_user_submitted_at_index');
            // Mempercepat rekap pengumpulan dan filter tugas yang terlambat.
            $table->index(
                ['assignment_id', 'is_late', 'submitted_at'],
                'submissions_assignment_late_submitted_at_index'
            );
        });

        Schema::table('grades', function (Blueprint $table) {
            // Mempercepat pemfilteran dan pengurutan nilai berdasarkan graded_at.
            $table->index(['graded_at'], 'grades_graded_at_index');
        });

        Schema::table('course_user', function (Blueprint $table) {
            // Mempercepat pencarian seluruh mata kuliah atau mahasiswa melalui join user_id.
            $table->index(['user_id'], 'course_user_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            $table->dropIndex('course_user_user_id_index');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex('grades_graded_at_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_assignment_late_submitted_at_index');
            $table->dropIndex('submissions_user_submitted_at_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_course_status_created_at_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropIndex('materials_course_created_at_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_lecturer_status_created_at_index');
        });
    }
};
