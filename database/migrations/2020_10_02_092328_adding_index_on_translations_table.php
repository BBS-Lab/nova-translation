<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddingIndexOnTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->id()->first();
            $table->dropPrimary('translations_pk');
            $table->index(['translatable_type', 'translatable_source'], 'source_idx');
            $table->index(['translation_id', 'translatable_id', 'translatable_type'], 'eager_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary(['locale_id', 'translation_id', 'translatable_id', 'translatable_type'], 'translations_pk');
            $table->dropIndex('source_idx');
            $table->dropIndex('eager_idx');
        });
    }
}
