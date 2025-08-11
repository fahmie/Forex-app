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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('target_currency', 3);
            $table->decimal('rate', 15, 6);
            $table->date('rate_date');
            $table->timestamp('fetched_at')->nullable();
            $table->string('source')->nullable(); // API source
            $table->timestamps();

            // Indexes for better performance
            $table->index(['base_currency', 'target_currency', 'rate_date']);
            $table->index(['rate_date']);

            // Foreign key constraints
            $table->foreign('base_currency')->references('code')->on('currencies')->onDelete('cascade');
            $table->foreign('target_currency')->references('code')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
