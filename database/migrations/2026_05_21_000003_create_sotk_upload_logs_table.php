<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sotk_upload_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('sotk_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['upload', 'replace', 'delete']);
            $table->string('file_name', 255)->nullable();
            $table->integer('total_rows')->unsigned()->default(0);
            $table->integer('valid_rows')->unsigned()->default(0);
            $table->integer('error_rows')->unsigned()->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sotk_upload_logs');
    }
};
