<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oer_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('open_educational_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('oer_subjects')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->text('description')->nullable();
            $table->text('url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->string('license')->nullable();
            $table->string('language')->default('vi');
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_educational_resources');
        Schema::dropIfExists('oer_subjects');
    }
};
