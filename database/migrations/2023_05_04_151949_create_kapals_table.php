<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kapals', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('kode');
            $table->string('nama_kapal');
            $table->string('nama_pemilik');
            $table->string('alamat_pemilik');
            $table->string('ukuran_kapal');
            $table->string('kapten');
            $table->string('jumlah_anggota');
            $table->string('foto_kapal');
            $table->string('nomor_izin');
            $table->string('dokumen_perizinan');
            $table->boolean('status')->default(0);
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kapals');
    }
};
