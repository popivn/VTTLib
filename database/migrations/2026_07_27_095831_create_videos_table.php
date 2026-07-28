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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Tên video');
            $table->string('description')->nullable()->comment('Mô tả video');
            $table->string('path')->comment('Đường dẫn file video');
            $table->string('thumbnail')->nullable()->comment('Ảnh thumbnail');
            $table->string('duration')->nullable()->comment('Thời lượng video');
            $table->bigInteger('file_size')->nullable()->comment('Kích thước file (bytes)');
            $table->string('mime_type')->nullable()->comment('Loại file video');
            $table->boolean('is_active')->default(true)->comment('Trạng thái hiển thị');
            $table->integer('sort_order')->default(0)->comment('Thứ tự sắp xếp');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Người tạo');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
