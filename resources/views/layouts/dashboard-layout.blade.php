<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-bg text-text-pri font-sans antialiased">

        @php
            $projectParam = request()->route('project');
            $project = $projectParam instanceof \App\Models\Project
                ? $projectParam
                : ($projectParam ? \App\Models\Project::find($projectParam) : null);
            $batch = $project?->photoBatches()->latest()->first();
            $currentRoute = request()->route()?->getName();
        @endphp

        {{-- Topbar --}}
        <header class="fixed top-0 inset-x-0 h-12 bg-bg border-b border-border z-50 flex items-center px-4 gap-4">
            <a href="{{ $project ? route('project.overview', $project) : route('projects') }}" wire:navigate class="font-mono text-sm font-medium text-text-pri tracking-tight shrink-0">
                {{ config('app.name') }}
            </a>
            @if ($project)
                <span class="text-text-faint text-xs hidden sm:inline">/</span>
                <span class="font-mono text-xs text-text-muted truncate hidden sm:inline">{{ $project->name }}</span>
            @endif
            <div class="flex-1"></div>
            @if ($batch)
                <x-progress-bar
                    :current="$batch->processed_photos"
                    :total="$batch->total_photos"
                    :status="$batch->status"
                    class="w-32 md:w-40"
                />
            @endif
        </header>

        {{-- Desktop Sidebar --}}
        <aside class="hidden md:flex fixed left-0 top-12 bottom-0 w-52 bg-bg border-r border-border flex-col z-40">
            <div class="flex-1 py-3">
                @if ($project)
                    <div class="px-4 pb-3 mb-3">
                        <x-scanline-rule class="w-16" />
                    </div>

                    @foreach ([
                        ['label' => 'Overview', 'route' => 'project.overview'],
                        ['label' => 'Upload', 'route' => 'project.upload'],
                        ['label' => 'Photos', 'route' => 'project.photos'],
                        ['label' => 'Faces', 'route' => 'project.faces'],
                        ['label' => 'Folders', 'route' => 'project.folders'],
                    ] as $item)
                        @php $isActive = $currentRoute === $item['route']; @endphp
                        <a
                            href="{{ route($item['route'], $project) }}"
                            wire:navigate
                            @class([
                                'flex items-center gap-3 px-4 py-2 text-sm font-mono border-l-2 transition-colors',
                                'border-accent text-text-pri bg-surface-alt' => $isActive,
                                'border-transparent text-text-muted hover:text-text-pri hover:bg-surface-alt' => ! $isActive,
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endif
            </div>

            <div class="p-4 border-t border-border">
                <a
                    href="{{ route('projects') }}"
                    wire:navigate
                    class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors"
                >
                    &larr; Back to projects
                </a>
            </div>
        </aside>

        {{-- Content --}}
        <main class="pt-12 pb-20 md:pb-0 md:ml-52 min-h-screen">
            {{ $slot }}
        </main>

        {{-- Mobile Bottom Tab Bar --}}
        @if ($project)
            <nav class="md:hidden fixed bottom-0 inset-x-0 h-16 bg-bg border-t border-border z-50 flex items-center justify-around">
                @foreach ([
                    ['label' => 'Overview', 'route' => 'project.overview', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                    ['label' => 'Upload', 'route' => 'project.upload', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                    ['label' => 'Faces', 'route' => 'project.faces', 'icon' => 'M5.121 17.804A8.004 8.004 0 0112 15c2.49 0 4.74 1.01 6.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['label' => 'Folders', 'route' => 'project.folders', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ] as $item)
                    @php $isActive = $currentRoute === $item['route']; @endphp
                    <a
                        href="{{ route($item['route'], $project) }}"
                        wire:navigate
                        @class([
                            'flex flex-col items-center gap-1 px-3 py-1 text-[10px] font-mono transition-colors',
                            'text-text-pri' => $isActive,
                            'text-text-muted' => ! $isActive,
                        ])
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        @endif

        <x-toast-container />

        <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (params) => {
                window.dispatchEvent(new CustomEvent('toast', { detail: params }))
            })
        })
        </script>

        @livewireScripts
    </body>
</html>
