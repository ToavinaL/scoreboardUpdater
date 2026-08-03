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
        Schema::create('tournaments', function (Blueprint $table) {

            $table->id();
        
            $table->string('name');
        
            $table->string('game')
                ->default('Mortal Kombat 1');
        
            $table->enum('format', [
                'single_elimination',
                'double_elimination',
                'round_robin'
            ]);
        
            $table->date('date')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
