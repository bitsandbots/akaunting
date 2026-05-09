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
        Schema::create('functional_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->enum('parent_class', ['program_services', 'management_general', 'fundraising']);
            $table->boolean('is_system')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('functional_classes');
    }
};
