<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_centroids', function (Blueprint $table) {
            $table->foreignUuid('person_id')->primary()->constrained('people')->cascadeOnDelete();
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::statement('ALTER TABLE person_centroids ADD COLUMN centroid vector(512)');
        DB::statement('CREATE INDEX person_centroids_hnsw_idx ON person_centroids USING hnsw (centroid vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('person_centroids');
    }
};
