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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('statement');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->unsignedTinyInteger('priority')->default(0)->comment('0=none,1=low,2=medium,3=high');
            $table->date('task_date');
            $table->unsignedInteger('sort_order')->default(0)->comment('User-defined drag-and-drop order');
            $table->timestamps();

            $table->index(['user_id', 'task_date']);
            $table->index(['user_id', 'task_date', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
