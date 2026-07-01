@php
    /**
     * Nette inline-SVG iconen voor channel-sites. NOOIT emoji gebruiken.
     * Gebruik een semantische naam ($name). Oude emoji-waarden uit config
     * worden via de alias-tabel automatisch naar een SVG vertaald.
     */
    $name = $name ?? 'check';
    $icons = [
        'calendar' => '<path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.18 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/>',
        'star'     => '<path d="M12 2l3 6.3 6.9.9-5 4.6 1.3 6.8L12 18.3 5.5 21.6l1.3-6.8-5-4.6 6.9-.9z"/>',
        'image'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        'location' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'check'    => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.1 15.9M14.5 14.5 20 20M8.1 8.1 12 12"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'cart'     => '<circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/><path d="M2 3h3l2.4 12a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L22 7H6"/>',
        'gift'     => '<rect x="3" y="8" width="18" height="4"/><path d="M12 8v13M4 12v9h16v-9"/><path d="M12 8a3 3 0 1 0-3-3M12 8a3 3 0 1 1 3-3"/>',
        'card'     => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'link'     => '<path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
        'wrench'   => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6.6 6.6a1.5 1.5 0 0 0 2.1 2.1l6.6-6.6a4 4 0 0 0 5.4-5.4l-2.3 2.3-1.8-1.8z"/>',
        'menu'     => '<path d="M4 5h16M4 12h16M4 19h10"/>',
        'chart'    => '<path d="M3 3v18h18"/><path d="m7 14 3-3 3 3 5-6"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'laptop'   => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M2 20h20"/>',
        'coffee'   => '<path d="M17 8h1a3 3 0 0 1 0 6h-1"/><path d="M3 8h14v5a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5z"/><path d="M6 2v2M10 2v2M14 2v2"/>',
        'spark'    => '<path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18"/>',
        'lock'     => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'gear'     => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
    ];
    $alias = [
        '📅' => 'calendar', '🗓️' => 'calendar', '🕐' => 'clock', '⏰' => 'clock',
        '💇' => 'scissors', '💈' => 'scissors', '✂️' => 'scissors',
        '🧔' => 'image', '📸' => 'image', '🖼️' => 'image',
        '🍽️' => 'menu', '⭐' => 'star', '🌟' => 'star', '📍' => 'location', '📌' => 'location',
        '🛍️' => 'cart', '🛒' => 'cart', '🎁' => 'gift', '💳' => 'card', '🔗' => 'link',
        '🔧' => 'wrench', '🛠️' => 'wrench', '📞' => 'phone', '☎️' => 'phone', '✅' => 'check', '💅' => 'star',
        '📈' => 'chart', '📊' => 'chart', '🔍' => 'search', '🔎' => 'search', '🌐' => 'globe',
        '💻' => 'laptop', '🖥️' => 'laptop', '☕' => 'coffee', '✨' => 'spark', '💬' => 'check',
        '🔐' => 'lock', '🔒' => 'lock', '⚙️' => 'gear', '🏪' => 'cart', '🤖' => 'spark', '👤' => 'check',
    ];
    $key  = $icons[$name] ?? ($icons[$alias[$name] ?? ''] ?? $icons['check']);
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="26" height="26" aria-hidden="true">{!! $key !!}</svg>
