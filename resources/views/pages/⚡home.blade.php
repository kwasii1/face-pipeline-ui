<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::app')]
class extends Component
{
    public bool $showLearnMore = false;
};
?>

<div class="relative flex h-full items-center justify-center overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-bg/70 via-bg/85 to-bg/95"></div>

    <div class="relative z-10 flex flex-col items-center text-center px-6 max-w-2xl">
        <p class="font-mono text-xs tracking-[0.2em] uppercase text-text-faint mb-4">
            Face Pipeline
        </p>

        <h1 class="font-mono text-4xl md:text-5xl lg:text-6xl font-bold text-text-pri tracking-tight">
            Photo Organizer
        </h1>

        <x-scanline-rule class="w-48 my-6" />

        <p class="font-sans text-lg md:text-xl text-text-muted leading-relaxed max-w-xl">
            A technical pipeline for organizing and processing your photo collection with face detection.
        </p>

        <div class="flex gap-4 mt-10">
            <a
                href="{{ route('projects') }}"
                wire:navigate
                class="inline-flex items-center px-6 py-3 bg-accent text-bg font-mono text-sm font-medium tracking-wider uppercase hover:opacity-90 transition-opacity"
            >
                Try it out
            </a>
            <button
                wire:click="$set('showLearnMore', true)"
                class="inline-flex items-center px-6 py-3 border border-border text-text-pri font-mono text-sm font-medium tracking-wider uppercase hover:border-text-muted transition-colors"
            >
                Learn how it works
            </button>
        </div>
    </div>

    <div class="absolute bottom-0 inset-x-0 flex justify-between items-end p-6 z-10 pointer-events-none">
        <span class="font-mono text-[10px] text-text-faint">{{ config('app.name') }} v0.1</span>
        <span class="font-mono text-[10px] text-text-faint">no auth &middot; runs locally</span>
    </div>

    <div
        x-data="{ open: @entangle('showLearnMore') }"
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
        x-cloak
    >
        <div
            class="absolute inset-0 bg-bg/80 backdrop-blur-sm"
            x-on:click="open = false; $wire.set('showLearnMore', false)"
        ></div>

        <div class="relative z-10 w-full max-w-lg bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
            <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                <h2 class="font-mono text-sm font-medium text-text-pri">How it works</h2>
                <button x-on:click="open = false; $wire.set('showLearnMore', false)" class="text-text-muted hover:text-text-pri transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-5">
                @foreach ([
                    ['Detection', 'Faces are found in photos using a detection model. Bounding boxes are drawn around each face.'],
                    ['Embedding', 'Each detected face is converted into a 512-dimensional vector — a mathematical "fingerprint" of the face.'],
                    ['Clustering', 'Similar face vectors are grouped together. Each cluster represents one person appearing across multiple photos.'],
                    ['Tagging', 'You review and name each cluster. Once tagged, all faces in that cluster are linked to that person.'],
                    ['Search', 'After tagging, you can find photos containing specific people, including combinations of people together.'],
                ] as $index => [$title, $desc])
                    <div class="flex gap-3">
                        <span class="font-mono text-xs text-text-faint shrink-0 mt-0.5">{{ $index + 1 }}.</span>
                        <div>
                            <p class="font-mono text-sm text-text-pri mb-1">{{ $title }}</p>
                            <p class="font-sans text-sm text-text-muted leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
