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
        Schema::create('patron_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('card_number')->nullable();
            $table->string('email_phone')->nullable();
            $table->string('patron_group')->default('sinh_vien');
            $table->tinyInteger('rating_service')->default(5);
            $table->tinyInteger('rating_resource')->default(5);
            $table->tinyInteger('rating_facility')->default(5);
            $table->tinyInteger('rating_overall')->default(5);
            $table->string('survey_category')->default('gop_y_chung');
            $table->text('content');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patron_surveys');
    }
};
