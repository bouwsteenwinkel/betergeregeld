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
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h4"/>',
        'folder'   => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'truck'    => '<path d="M3 6a1 1 0 0 1 1-1h9v9H3z"/><path d="M13 8h4l4 4v2h-8z"/><circle cx="7" cy="17" r="1.6"/><circle cx="17" cy="17" r="1.6"/>',
        'package'  => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v9"/>',
        'ruler'    => '<path d="M3 16.5 16.5 3 21 7.5 7.5 21z"/><path d="m7 9 2 2M11 5l2 2M9 15l2 2M13 11l2 2"/>',
        'building' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/><path d="M10 21v-3h4v3"/>',
        'target'   => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4"/>',
        'droplet'  => '<path d="M12 3s6 6.4 6 10.4a6 6 0 0 1-12 0C6 9.4 12 3 12 3z"/>',
        'bell'     => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M10.5 21a1.5 1.5 0 0 0 3 0"/>',
        'bolt'     => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'palette'  => '<path d="M12 3a9 9 0 1 0 0 18c.9 0 1.5-.7 1.5-1.5 0-1 .8-1.5 1.6-1.5H18a3 3 0 0 0 3-3 8 8 0 0 0-9-9z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="10.5" r="1"/>',
        'moon'     => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
        'undo'     => '<path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/>',
        'chat'     => '<path d="M21 11.5a8 8 0 0 1-11.5 7.2L3 21l1.3-6.5A8 8 0 1 1 21 11.5z"/>',
        'calc'     => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M8 6h8"/><path d="M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h4"/>',
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
        '📆' => 'calendar', '🗓' => 'calendar', '💬' => 'chat', '💭' => 'chat',
        '🧾' => 'document', '📄' => 'document', '📃' => 'document', '📁' => 'folder', '📂' => 'folder',
        '🚚' => 'truck', '🚛' => 'truck', '📦' => 'package', '🧱' => 'package',
        '📐' => 'ruler', '📏' => 'ruler', '🏢' => 'building', '🏠' => 'building', '🏡' => 'building',
        '🎯' => 'target', '🛁' => 'droplet', '🚿' => 'droplet', '🚽' => 'droplet', '💧' => 'droplet',
        '🔕' => 'bell', '🔔' => 'bell', '📷' => 'image', '📵' => 'phone', '🎨' => 'palette',
        '🌙' => 'moon', '🌛' => 'moon', '⚡' => 'bolt', '🔌' => 'bolt', '↩️' => 'undo', '↩' => 'undo', '🔄' => 'undo',
        '🧰' => 'wrench', '🧑‍🔧' => 'wrench', '👷' => 'wrench', '🧮' => 'calc',
        '✉️' => 'mail', '✉' => 'mail', '📧' => 'mail', '📨' => 'mail', '📮' => 'mail',
    ];
    $key  = $icons[$name] ?? ($icons[$alias[$name] ?? ''] ?? $icons['check']);
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" width="26" height="26" aria-hidden="true">{!! $key !!}</svg>
