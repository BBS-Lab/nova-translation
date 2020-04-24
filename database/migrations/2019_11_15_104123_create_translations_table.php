<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->unsignedInteger('locale_id');
            $table->unsignedInteger('translation_id');
            $table->unsignedInteger('translatable_id');
            $table->string('translatable_type');
            $table->unsignedInteger('translatable_source');

            $table->primary(['locale_id', 'translation_id', 'translatable_id', 'translatable_type'], 'translations_pk');
            $table->index(['translatable_id', 'translatable_type', 'locale_id'], 'scopable_idx');
            $table->index(['translatable_id', 'translatable_type'], 'translatable_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
