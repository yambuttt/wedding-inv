<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        @php
            $guest = request()->query('to', '');
            $invitation = \App\Models\Invitation::where('slug', 'sari-raju')->first() ?? \App\Models\Invitation::first();
            $title = 'Undangan Pernikahan';
            $initials = 'W';
            if ($invitation) {
                $title = "Undangan Pernikahan {$invitation->bride_name_short} & {$invitation->groom_name_short}";
                if ($guest) {
                    $title .= " - Spesial untuk {$guest}";
                }
                $initials = substr($invitation->bride_name_short, 0, 1) . '&' . substr($invitation->groom_name_short, 0, 1);
            }
        @endphp

        <title>{{ $title }}</title>

        <!-- Dynamic Custom Initials Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='50' fill='%23030907'/><circle cx='50' cy='50' r='45' fill='none' stroke='%23e6ca65' stroke-width='3'/><circle cx='50' cy='50' r='41' fill='none' stroke='%23e6ca65' stroke-width='1' stroke-dasharray='3 2'/><text x='50' y='58' font-family='serif' font-weight='bold' font-size='30' fill='%23e6ca65' text-anchor='middle'>{{ $initials }}</text></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Great+Vibes&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Special+Elite&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Homemade+Apple&family=Pinyon+Script&family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Abril+Fatface&family=Outfit:wght@300;400;500;600;700&family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Leaflet Map Assets -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#030907] antialiased">
        
        <!-- Livewire Wedding Invitation Component -->
        <livewire:wedding-invitation />

    </body>
</html>
