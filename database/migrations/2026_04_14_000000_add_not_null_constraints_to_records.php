<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add NOT NULL constraints to required fields in records table
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            // Make required fields NOT NULL
            $table->date('date')->nullable(false)->change();
            $table->string('action')->nullable(false)->change();
            $table->string('worker')->nullable(false)->change();
            $table->decimal('time', 8, 2)->nullable(false)->change();
            $table->decimal('costs', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            // Revert to nullable
            $table->date('date')->nullable()->change();
            $table->string('action')->nullable()->change();
            $table->string('worker')->nullable()->change();
            $table->decimal('time', 8, 2)->nullable()->change();
            $table->decimal('costs', 10, 2)->nullable()->change();
        });
    }
};
