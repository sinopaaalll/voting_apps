<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kandidat', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('nomor_urut')->unique();
            $table->string('name', 100);
            $table->string('photo');
            $table->text('visi_misi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kandidat');
    }
};
