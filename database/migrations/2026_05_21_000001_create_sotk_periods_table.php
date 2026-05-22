<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sotk_periods', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('bulan')->unsigned()->comment('1-12');
            $table->smallInteger('tahun')->unsigned()->comment('e.g. 2026');
            $table->string('label', 30)->comment('e.g. Januari 2026');
            $table->integer('total_pegawai')->unsigned()->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamps();

            $table->unique(['bulan', 'tahun'], 'uq_bulan_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sotk_periods');
    }
};
