<?php

namespace Database\Seeders;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::factory()->create([
            'name' => 'Summer Trip 2026',
            'description' => 'Photos from our summer road trip through the mountains and coastal towns.',
        ]);

        $people = [];
        $names = ['Alice Chen', 'Bob Williams', 'Carmen Diaz', 'David Park', 'Eva Johansson', 'Felix Nguyen', 'Grace Okonkwo'];
        foreach ($names as $name) {
            $people[] = Person::factory()->create([
                'project_id' => $project->id,
                'name' => $name,
            ]);
        }

        $batch = PhotoBatch::factory()->create([
            'project_id' => $project->id,
            'total_photos' => 220,
            'processed_photos' => 142,
            'status' => 'processing',
        ]);

        $photos = [];
        for ($i = 0; $i < 20; $i++) {
            $color = $this->photoColor($i);
            $path = $this->generatePlaceholderSvg(400, 300, $color, 'Photo '.($i + 1));
            $photos[] = Photo::factory()->create([
                'project_id' => $project->id,
                'batch_id' => $batch->id,
                'path' => $path,
                'status' => $i < 15 ? 'processed' : 'pending',
            ]);
        }

        $clusterA = 'cluster-001';
        $clusterB = 'cluster-002';

        $faceColors = ['#1a3a5c', '#3a1a5c', '#5c3a1a', '#1a5c3a', '#5c1a3a', '#3a5c1a', '#4a2a6c', '#6c4a2a', '#2a6c4a', '#4a6c2a'];

        for ($i = 0; $i < 10; $i++) {
            $cropPath = $this->generatePlaceholderSvg(100, 100, $faceColors[$i], 'F'.($i + 1));

            $data = [
                'photo_id' => $photos[$i % count($photos)]->id,
                'crop_path' => $cropPath,
                'det_score' => round(0.65 + ($i * 0.03), 4),
            ];

            if ($i < 5) {
                $data['person_id'] = $people[$i % count($people)]->id;
            } elseif ($i < 8) {
                $data['cluster_id'] = $i < 7 ? $clusterA : $clusterB;
            }

            Face::factory()->create($data);
        }
    }

    private function photoColor(int $index): string
    {
        $colors = ['#1e3a5f', '#2d4a3e', '#4a2e1e', '#3d2d5a', '#5a3d2d', '#2e4a5a', '#4a1e3e', '#3e4a1e'];

        return $colors[$index % count($colors)];
    }

    private function generatePlaceholderSvg(int $width, int $height, string $color, string $label): string
    {
        $dir = public_path('placeholders');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = 'placeholder_'.strtolower(str_replace(' ', '_', $label)).'.svg';
        $filepath = $dir.'/'.$filename;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d">'.
            '<rect width="100%%" height="100%%" fill="%s"/>'.
            '<text x="50%%" y="50%%" font-family="Inter, sans-serif" font-size="14" fill="rgba(255,255,255,0.4)" text-anchor="middle" dominant-baseline="middle">%s</text>'.
            '</svg>',
            $width, $height, $color, $label
        );

        File::put($filepath, $svg);

        return 'placeholders/'.$filename;
    }
}
