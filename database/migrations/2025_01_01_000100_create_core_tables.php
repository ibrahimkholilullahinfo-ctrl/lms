<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color', 20)->default('#0b2545');
            $table->string('secondary_color', 20)->default('#f97316');
            $table->string('locale', 5)->default('en');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institute_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role', 30)->default('student')->index();
            $table->string('phone', 30)->nullable();
            $table->string('avatar')->nullable();
            $table->string('locale', 5)->nullable();
            $table->string('designation')->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
            $table->index(['institute_id', 'role']);
        });

        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('relation', 30)->default('parent');
            $table->timestamps();
            $table->unique(['guardian_id', 'student_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general')->index();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('guardian_student');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institute_id');
            $table->dropColumn(['role', 'phone', 'avatar', 'locale', 'designation', 'bio', 'status', 'last_login_at', 'deleted_at']);
        });
        Schema::dropIfExists('institutes');
    }
};
