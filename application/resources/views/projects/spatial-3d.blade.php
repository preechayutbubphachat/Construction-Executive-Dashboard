<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->code }} &middot; 3D Spatial Digital Twin & BIM Visualizer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased overflow-hidden">
    <livewire:site-spatial-3d-view :project="$project" />
    @livewireScripts
</body>
</html>
