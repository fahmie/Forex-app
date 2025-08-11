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
        Schema::create('currency_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('from_amount', 15, 6);
            $table->decimal('to_amount', 15, 6);
            $table->decimal('exchange_rate', 15, 6);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['from_currency', 'to_currency']);
            $table->index(['created_at']);

            // Foreign key constraints
            $table->foreign('from_currency')->references('code')->on('currencies')->onDelete('cascade');
            $table->foreign('to_currency')->references('code')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_conversions');
    }
};
