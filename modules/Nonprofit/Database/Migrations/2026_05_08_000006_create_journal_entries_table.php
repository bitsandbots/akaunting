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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('entry_number', 50);
            $table->date('entry_date');
            $table->text('description')->nullable();
            $table->string('reference', 255)->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->enum('status', ['draft', 'posted', 'reversed', 'void'])->default('draft');
            $table->unsignedBigInteger('reversed_by_entry_id')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('currency_code', 3);
            $table->decimal('currency_rate', 15, 8)->default(1.0);
            $table->dateTime('posted_at')->nullable();
            $table->unsignedInteger('posted_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();

            $table->foreign('reversed_by_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->nullOnDelete();

            $table->foreign('reverses_entry_id')
                ->references('id')
                ->on('journal_entries')
                ->nullOnDelete();

            $table->foreign('posted_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['company_id', 'entry_number']);
            $table->index(['company_id', 'entry_date', 'status']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
