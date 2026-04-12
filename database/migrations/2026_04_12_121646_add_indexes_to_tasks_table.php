<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('deadline');
            $table->index('status');
            $table->index(['user_id', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['deadline']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id_deadline']);
        });
    }
};
