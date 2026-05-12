<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App\Abstracts\Model uses the SoftDeletes trait, so every nonprofit
 * model inherits a deleted_at scope. The original create migration for
 * transaction_dimensions omitted the column, which forces a SQL error
 * the moment the model is queried. Add it now; allocation rows are not
 * audit objects (unlike fiscal_periods), so soft-delete is the right
 * semantic — a removed split row is recoverable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_dimensions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('transaction_dimensions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
