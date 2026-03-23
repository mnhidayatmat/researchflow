<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ResearchFlow' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { surface: '#F7F7F5', accent: '#D97706' } } }
        }
    </script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
        }
    </style>
</head>
<body class="h-full bg-surface flex items-center justify-center">
    <div class="w-full max-w-sm mx-4">
        <div class="text-center mb-8">
            <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-amber-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h1 class="text-lg font-semibold text-gray-900">ResearchFlow</h1>
            <p class="text-sm text-gray-500 mt-1">Academic Supervision Workspace</p>
        </div>
        {{ $slot }}
    </div>
</body>
</html>
