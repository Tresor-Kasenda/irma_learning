<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_classes', function (Blueprint $table) {
            $table->string('sub_title')->nullable();
            $table->text("presentation")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_classes', function (Blueprint $table) {
            $table->dropColumn('sub_title');
            $table->dropColumn('presentation');
        });
    }
};
