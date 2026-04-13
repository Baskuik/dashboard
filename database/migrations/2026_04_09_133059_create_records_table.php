<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('upload_id')->constrained('uploads', 'bestand_id')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->string('worker')->nullable();
            $table->decimal('time', 8, 2)->nullable(); // uren
            $table->decimal('costs', 10, 2)->nullable(); // kosten
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
