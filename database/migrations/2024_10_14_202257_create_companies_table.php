<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('exit_strategy_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('funding_level_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('company_size_id')->nullable()->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('approved_at')->nullable();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            //$table->string('logo')->nullable();
            $table->text('notes')->nullable();
            $table->integer('valuation')->nullable();
            $table->integer('exit_valuation')->nullable();
            $table->string('stock_symbol')->nullable();
            $table->integer('total_funding')->nullable();
            $table->date('last_funding_date')->nullable();
            $table->string('headquarter')->nullable();
            $table->date('founded_at')->nullable();
            $table->integer('employee_count')->nullable();
            $table->string('stock_quote')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
