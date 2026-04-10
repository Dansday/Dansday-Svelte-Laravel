<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('embeddings', function (Blueprint $table) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('embeddings', 'chunk_index')) {
                $table->unsignedSmallInteger('chunk_index')->default(0)->after('row_id');
            }

            $sm = \Illuminate\Support\Facades\DB::getDoctrineSchemaManager();
            $indexes = array_keys($sm->listTableIndexes('embeddings'));

            if (in_array('embeddings_table_name_row_id_unique', $indexes)) {
                $table->dropUnique(['table_name', 'row_id']);
            }
            if (!in_array('embeddings_table_name_row_id_chunk_index_unique', $indexes)) {
                $table->unique(['table_name', 'row_id', 'chunk_index']);
            }
            if (!in_array('embeddings_table_name_row_id_index', $indexes)) {
                $table->index(['table_name', 'row_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('embeddings', function (Blueprint $table) {
            $table->dropUnique(['table_name', 'row_id', 'chunk_index']);
            $table->dropIndex(['table_name', 'row_id']);
            $table->dropColumn('chunk_index');
        });
    }
};
