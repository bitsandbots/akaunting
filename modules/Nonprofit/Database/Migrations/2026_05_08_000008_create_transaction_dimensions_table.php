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
        Schema::create('transaction_dimensions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('transaction_id');
            $table->unsignedBigInteger('fund_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('functional_class_id')->nullable();
            $table->decimal('allocation_percent', 7, 4)->nullable();
            $table->decimal('allocation_amount', 15, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->cascadeOnDelete();

            $table->foreign('fund_id')
                ->references('id')
                ->on('funds')
                ->nullOnDelete();

            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->nullOnDelete();

            $table->foreign('functional_class_id')
                ->references('id')
                ->on('functional_classes')
                ->nullOnDelete();

            $table->index(['company_id', 'transaction_id']);
            $table->index('fund_id');
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
        Schema::dropIfExists('transaction_dimensions');
    }
};
