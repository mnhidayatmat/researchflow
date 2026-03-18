<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained()->cascadeOnDelete();
            $table->integer('chunk_index'); // Position in the document
            $table->text('content'); // The text chunk
            $table->json('metadata')->nullable(); // start_char, end_char, token_estimate, etc.
            $table->timestamps();

            // Vector storage - using JSON array for now
            // For production with PostgreSQL, use pgvector: $table->vector('embedding', $dimension);
            $table->json('vector');

            // Indexes for efficient retrieval
            $table->unique(['file_id', 'chunk_index']);
            $table->index('file_id');

            // For vector similarity search with pgvector, add:
            // $table->index('vector', 'ai_embeddings_vector_index', 'ivfflat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_embeddings');
    }
};
