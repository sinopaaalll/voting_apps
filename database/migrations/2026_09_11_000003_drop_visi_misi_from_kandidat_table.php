<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('kandidat', 'visi_misi')) {
            Schema::table('kandidat', function (Blueprint $table) {
                $table->dropColumn('visi_misi');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('kandidat', 'visi_misi')) {
            Schema::table('kandidat', function (Blueprint $table) {
                $table->text('visi_misi')->nullable();
            });
        }
    }
};
