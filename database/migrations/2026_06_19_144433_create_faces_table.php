<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->string('cluster_id')->nullable();
            $table->json('bbox')->nullable();
            $table->string('crop_path');
            $table->float('det_score')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE faces ADD COLUMN embedding vector(512)');
        DB::statement('CREATE INDEX faces_embedding_hnsw_idx ON faces USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('faces');
    }
};
