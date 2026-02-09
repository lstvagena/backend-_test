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
        Schema::create('company_progconf', function (Blueprint $table) {
            $table->id();
            $table->string('comcde', 50)->index(); // company code
            $table->string('appcde', 20)->index(); // HR, PAYROLL, etc
            $table->longText('fcon'); // encrypted DB credentials
            $table->timestamps();
            $table->unique(['comcde', 'appcde']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_progconf');
    }
};
