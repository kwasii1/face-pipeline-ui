<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::app')]
class extends Component
{
    //
};
?>

<div class="relative flex h-full items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-bg/70 via-bg/85 to-bg/95"></div>

    <div class="relative z-10 flex flex-col items-center text-center px-6 max-w-2xl">
        <h1 class="font-mono text-4xl md:text-5xl lg:text-6xl font-bold text-text-pri tracking-tight">
            Photo Organizer
        </h1>

        <x-scanline-rule class="w-48 my-6" />

        <p class="font-sans text-lg md:text-xl text-text-muted leading-relaxed max-w-xl">
            A technical pipeline for organizing and processing your photo collection with face detection.
        </p>

        <div class="flex gap-4 mt-10">
            <a href="#" class="inline-flex items-center px-6 py-3 bg-accent text-bg font-mono text-sm font-medium tracking-wider uppercase hover:opacity-90 transition-opacity">
                Try Now
            </a>
            <a href="#" class="inline-flex items-center px-6 py-3 border border-border text-text-pri font-mono text-sm font-medium tracking-wider uppercase hover:border-text-muted transition-colors">
                Learn More
            </a>
        </div>
    </div>
</div>
