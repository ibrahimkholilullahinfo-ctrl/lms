<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('mode', 20)->default('offline');
            $table->string('provider', 30)->default('offline');
            $table->string('meeting_id')->nullable();
            $table->text('join_url')->nullable();
            $table->string('meeting_password')->nullable();
            $table->string('venue')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('status', 20)->default('scheduled')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_no')->unique();
            $table->string('title');
            $table->date('issued_at');
            $table->string('grade')->nullable();
            $table->string('template')->default('default');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('audience', 30)->default('all');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plugin', 40);
            $table->string('transaction_id')->nullable()->index();
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 20)->default('pending')->index();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 30)->index();
            $table->string('provider', 60);
            $table->string('version', 20)->default('1.0.0');
            $table->text('description')->nullable();
            $table->json('settings_schema')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->timestamps();
        });

        Schema::create('institute_plugin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plugin_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['institute_id', 'plugin_id']);
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('filename');
            $table->string('disk')->default('local');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('type', 20)->default('database');
            $table->string('status', 20)->default('completed')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->boolean('is_homepage')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['institute_id', 'slug']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->cascadeOnDelete();
            $table->string('label_en');
            $table->string('label_bn')->nullable();
            $table->string('url')->default('#');
            $table->string('target', 20)->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('location', 20)->default('header');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('institute_plugin');
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('class_sessions');
    }
};
