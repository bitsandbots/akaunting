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
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('chart_of_account_id');
            $table->decimal('debit_amount', 15, 2)->default(0.00);
            $table->decimal('credit_amount', 15, 2)->default(0.00);
            $table->decimal('debit_foreign', 15, 2)->default(0.00);
            $table->decimal('credit_foreign', 15, 2)->default(0.00);
            $table->string('currency_code', 3);
            $table->decimal('currency_rate', 15, 8)->default(1.0);
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('functional_class_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('journal_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->cascadeOnDelete();

            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->restrictOnDelete();

            $table->foreign('fund_id')
                ->references('id')
                ->on('funds')
                ->restrictOnDelete();

            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->nullOnDelete();

            $table->foreign('functional_class_id')
                ->references('id')
                ->on('functional_classes')
                ->restrictOnDelete();

            $table->index(['company_id', 'chart_of_account_id', 'journal_entry_id']);
            $table->index(['company_id', 'fund_id', 'journal_entry_id']);
            $table->index('program_id');
            $table->index('functional_class_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
