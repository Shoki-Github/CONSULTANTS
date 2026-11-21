<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->string('organisation')->nullable();
            $table->string('organisation_type')->nullable();
            $table->string('service_of_interest')->nullable();
            $table->string('preferred_language', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropColumn([
                'organisation',
                'organisation_type',
                'service_of_interest',
                'preferred_language',
            ]);
        });
    }
};
