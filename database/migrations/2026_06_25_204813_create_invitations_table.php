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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            
            // Groom details
            $table->string('groom_name_short');
            $table->string('groom_name_full');
            $table->string('groom_father')->nullable();
            $table->string('groom_mother')->nullable();
            
            // Bride details
            $table->string('bride_name_short');
            $table->string('bride_name_full');
            $table->string('bride_father')->nullable();
            $table->string('bride_mother')->nullable();
            
            // Text values
            $table->text('welcome_message')->nullable();
            $table->text('greetings_message')->nullable(); // Kata sambutan utama
            
            // Event Details
            $table->dateTime('event_date'); // Target countdown & main date
            $table->string('akad_time')->nullable();
            $table->string('akad_location')->nullable();
            $table->string('resepsi_time')->nullable();
            $table->string('resepsi_location')->nullable();
            $table->text('maps_url')->nullable();
            $table->text('maps_embed_url')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('template')->default('elegant');
            
            // Customization
            $table->string('bg_music_url')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
