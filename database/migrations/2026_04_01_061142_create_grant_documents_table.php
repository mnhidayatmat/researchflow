<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grant_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();

            $table->index('grant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grant_documents');
    }
};
