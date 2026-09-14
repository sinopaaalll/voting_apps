<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voting', function (Blueprint $table) {
            $table->dropForeign(['kandidat_id']);
            $table->foreign('kandidat_id')
                ->references('id')
                ->on('kandidat')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('voting', function (Blueprint $table) {
            $table->dropForeign(['kandidat_id']);
            $table->foreign('kandidat_id')
                ->references('id')
                ->on('kandidat')
                ->restrictOnDelete();
        });
    }
};
