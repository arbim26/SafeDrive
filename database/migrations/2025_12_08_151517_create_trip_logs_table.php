<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->timestamp('timestamp');
            $table->decimal('fatigue_level', 3, 2)->nullable(); // skor 0-1
            $table->enum('eye_status', ['open', 'closed'])->nullable();
            $table->boolean('yawn_status')->default(false);
            $table->boolean('seatbelt_status')->default(true);
            $table->decimal('accuracy', 3, 2)->nullable(); // akurasi deteksi
            $table->string('confidence_level')->nullable(); // HIGH, MEDIUM, LOW
            $table->decimal('ear', 5, 3)->nullable(); // Eye Aspect Ratio
            $table->decimal('mar', 5, 3)->nullable(); // Mouth Aspect Ratio
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_logs');
    }
};