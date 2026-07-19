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
        // 1. Tạo bảng danh mục Tiêu chí Khảo sát động (survey_criteria)
        Schema::create('survey_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Cập nhật bảng patron_surveys (bỏ các cột rating cứng, chuyển sang bảng chi tiết)
        if (Schema::hasColumn('patron_surveys', 'rating_service')) {
            Schema::table('patron_surveys', function (Blueprint $table) {
                $table->dropColumn(['rating_service', 'rating_resource', 'rating_facility']);
            });
        }

        // 3. Tạo bảng chi tiết Điểm đánh giá theo Tiêu chí (patron_survey_ratings)
        Schema::create('patron_survey_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patron_survey_id')->constrained('patron_surveys')->onDelete('cascade');
            $table->foreignId('survey_criterion_id')->constrained('survey_criteria')->onDelete('cascade');
            $table->tinyInteger('rating')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patron_survey_ratings');
        Schema::dropIfExists('survey_criteria');
        
        if (Schema::hasTable('patron_surveys')) {
            Schema::table('patron_surveys', function (Blueprint $table) {
                $table->tinyInteger('rating_service')->default(5);
                $table->tinyInteger('rating_resource')->default(5);
                $table->tinyInteger('rating_facility')->default(5);
            });
        }
    }
};
