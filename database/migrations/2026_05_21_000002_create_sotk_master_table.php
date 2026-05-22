<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sotk_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('sotk_periods')->cascadeOnDelete();
            $table->string('nik', 20);
            $table->string('nama', 150);
            $table->string('level_jabatan', 100);
            $table->string('jabatan', 200);
            $table->string('klasifikasi_jabatan', 50)->comment('Operasional / Bisnis');
            $table->string('kode_cabang', 20);
            $table->string('unit_kantor', 200);
            $table->string('kelas', 50)->comment('Divisi / BRKS Cabang / Kantor Pusat');
            $table->string('penempatan', 200);
            $table->integer('row_number')->unsigned()->nullable()->comment('Nomor baris di Excel');
            $table->timestamps();

            $table->index('period_id');
            $table->index('nik');
            $table->index('kode_cabang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sotk_master');
    }
};
