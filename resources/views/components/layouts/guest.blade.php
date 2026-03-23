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
<body class="min-h-full bg-surface">
    <div class="flex min-h-full items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
