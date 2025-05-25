<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('status')->default(0)->comment('0: pending, 1: published, 2: failed');
            $table->text('response')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->unique(['platform_id', 'post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_post');
    }
};