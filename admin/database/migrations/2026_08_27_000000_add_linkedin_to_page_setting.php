<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'linkedin_access_token',
        'linkedin_token_expires_at',
        'linkedin_person_urn',
    ];

    public function up(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            if (! Schema::hasColumn('page_setting', 'linkedin_access_token')) {
                $table->text('linkedin_access_token')->nullable();
            }
            if (! Schema::hasColumn('page_setting', 'linkedin_token_expires_at')) {
                $table->dateTime('linkedin_token_expires_at')->nullable();
            }
            if (! Schema::hasColumn('page_setting', 'linkedin_person_urn')) {
                $table->string('linkedin_person_urn', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('page_setting', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
