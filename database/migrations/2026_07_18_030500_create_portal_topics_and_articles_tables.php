<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('portal_topics')) {
            Schema::create('portal_topics', function (Blueprint $table) {
                $table->id(); // TOPICID
                $table->bigInteger('parent_id')->nullable(); // PARENT
                $table->string('description'); // DESCRIPTION
                $table->text('abstract')->nullable(); // ABSTRACT
                $table->boolean('is_active')->default(true); // ACTIVE
                $table->integer('display_index')->default(0); // DISPLAYINDEX
                $table->string('customer_id', 50)->default('DEFAULT'); // CUSTOMERID
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('portal_articles')) {
            Schema::create('portal_articles', function (Blueprint $table) {
                $table->id(); // ARTICLEID
                $table->bigInteger('topic_id')->unsigned(); // TOPICID
                $table->bigInteger('bib_id')->unsigned()->nullable(); // BIBID (maps to bibliographic_records.id)
                $table->text('abstract')->nullable(); // ABSTRACT
                $table->longText('contents')->nullable(); // CONTENTS
                $table->binary('book_image')->nullable(); // BOOKIMAGE
                $table->integer('access_counter')->default(0); // ACCESSCOUNTER
                $table->integer('sort_order')->default(0); // SORTORDER
                $table->string('user_id', 20)->nullable(); // USERID
                $table->string('user_id_last', 20)->nullable(); // USERIDLAST
                $table->string('customer_id', 50)->default('DEFAULT'); // CUSTOMERID
                $table->timestamps();

                $table->foreign('topic_id')->references('id')->on('portal_topics')->onDelete('cascade');
                $table->foreign('bib_id')->references('id')->on('bibliographic_records')->onDelete('set null');
            });

            // Modify column to MEDIUMBLOB to handle medium-sized image blobs safely under MySQL
            if (config('database.default') === 'mysql') {
                DB::statement('ALTER TABLE portal_articles MODIFY book_image MEDIUMBLOB');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_articles');
        Schema::dropIfExists('portal_topics');
    }
};
