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
        Schema::create('stash_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->string('account_number'); // e.g., "6SQ-92997-18"
            $table->date('statement_period_start');
            $table->date('statement_period_end');
            $table->string('file_path'); // Path to original PDF
            $table->string('file_hash')->nullable(); // SHA256 hash for dedup
            $table->decimal('opening_cash', 12, 2);
            $table->decimal('closing_cash', 12, 2);
            $table->decimal('opening_securities_value', 12, 2);
            $table->decimal('closing_securities_value', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stash_statements');
    }
};
