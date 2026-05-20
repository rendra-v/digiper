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
        Schema::create('perkaras', function (Blueprint $table) {
            $table->id();
            $table->string('no_registrasi')->unique();
            $table->date('tanggal_perkara_masuk');
            $table->string('kamar');
            $table->string('nama_p1')->nullable();
            $table->string('nama_p2')->nullable();
            $table->string('nama_p3')->nullable();
            $table->string('nama_p4')->nullable();
            $table->string('nama_p5')->nullable();
            $table->string('nama_panteraan_pengakhiri')->nullable();
            $table->date('tanggal_putus')->nullable();
            $table->string('amar')->nullable();
            $table->decimal('biaya', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkaras');
    }
};
