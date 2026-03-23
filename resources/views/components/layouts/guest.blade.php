<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'ResearchFlow' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: '#F7F7F5',
                        card: '#FFFFFF',
                        border: '#E5E7EB',
                        primary: '#1F2937',
                        secondary: '#6B7280',
                        accent: '#D97706',
                        'accent-light': '#FEF3C7',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="relative min-h-full overflow-x-hidden bg-surface">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(217,119,6,0.18),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(31,41,55,0.08),_transparent_35%)]"></div>
    <div class="absolute inset-x-0 top-0 h-40 bg-[linear-gradient(135deg,_rgba(255,255,255,0.8),_rgba(247,247,245,0.2))]"></div>

    <div class="relative flex min-h-full items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent shadow-[0_18px_45px_rgba(217,119,6,0.28)]">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-primary">ResearchFlow</h1>
                <p class="mt-2 text-sm text-secondary">Academic supervision workspace for students, supervisors, and administrators.</p>
            </div>

            {{ $slot }}
        </div>
    </div>
</body>
</html>
