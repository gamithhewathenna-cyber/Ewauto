<?php
/**
 * Social media links. Each platform's URL is stored in the `settings` table
 * as `social_<key>` and edited from Admin > Settings. Platforms without a
 * URL are hidden everywhere on the site.
 */
function social_platforms(): array
{
    return [
        'facebook'  => [
            'label' => 'Facebook',
            'placeholder' => 'https://www.facebook.com/yourpage',
            'icon' => 'M13 22v-8h2.7l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.3-3.7 3.8V11H7v3h3v8h3z',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'placeholder' => 'https://www.instagram.com/yourpage',
            'icon' => 'M12 2.2c3.2 0 3.6 0 4.9.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.6 4.8-4.9 4.9-1.3.1-1.6.1-4.9.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 4 4 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2zm0 3.2A6.6 6.6 0 105.4 12 6.6 6.6 0 0012 5.4zm0 10.9A4.3 4.3 0 1116.3 12 4.3 4.3 0 0112 16.3zm6.8-11.1a1.5 1.5 0 11-1.5-1.5 1.5 1.5 0 011.5 1.5z',
        ],
        'tiktok'    => [
            'label' => 'TikTok',
            'placeholder' => 'https://www.tiktok.com/@yourpage',
            'icon' => 'M16.6 5.8A4.3 4.3 0 0115.5 3h-3.2v12.9a2.6 2.6 0 01-2.6 2.5 2.6 2.6 0 01-2.6-2.6 2.6 2.6 0 013.4-2.5V10a5.8 5.8 0 00-.8-.1 5.8 5.8 0 00-5.8 5.8 5.8 5.8 0 005.8 5.8 5.8 5.8 0 005.8-5.8V9.1a7.4 7.4 0 004.3 1.4V7.3a4.3 4.3 0 01-3.2-1.5z',
        ],
        'youtube'   => [
            'label' => 'YouTube',
            'placeholder' => 'https://www.youtube.com/@yourchannel',
            'icon' => 'M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 00.5 6.2 31 31 0 000 12a31 31 0 00.5 5.8 3 3 0 002.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 002.1-2.1A31 31 0 0024 12a31 31 0 00-.5-5.8zM9.6 15.6V8.4l6.3 3.6-6.3 3.6z',
        ],
        'x'         => [
            'label' => 'X (Twitter)',
            'placeholder' => 'https://x.com/yourpage',
            'icon' => 'M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z',
        ],
        'linkedin'  => [
            'label' => 'LinkedIn',
            'placeholder' => 'https://www.linkedin.com/company/yourpage',
            'icon' => 'M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2zM7.1 20.5H3.6V9h3.5v11.5z',
        ],
        'whatsapp'  => [
            'label' => 'WhatsApp',
            'placeholder' => '+94 77 123 4567 or https://wa.me/94771234567',
            'icon' => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 01-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 00-.7.3 3 3 0 00-.9 2.2 5.2 5.2 0 001.1 2.7 11.8 11.8 0 004.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 001.8-1.2 2.2 2.2 0 00.2-1.2c-.1-.1-.3-.2-.6-.3z',
        ],
    ];
}

/**
 * Clean up a URL typed into the admin form. Returns ['url' => string, 'error' => ?string].
 * Empty input is valid (it hides that platform). A missing scheme gets
 * https:// added; a bare phone number for WhatsApp becomes a wa.me link.
 */
function normalize_social_url(string $key, string $value): array
{
    $value = trim($value);
    if ($value === '') {
        return ['url' => '', 'error' => null];
    }

    if ($key === 'whatsapp' && preg_match('/^\+?[\d\s\-()]{7,}$/', $value)) {
        return ['url' => 'https://wa.me/' . preg_replace('/\D/', '', $value), 'error' => null];
    }

    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . ltrim($value, '/');
    }

    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return ['url' => $value, 'error' => social_platforms()[$key]['label'] . ' link is not a valid web address.'];
    }
    return ['url' => $value, 'error' => null];
}

/**
 * The platforms that have a URL set, in display order. Returns [] if the
 * DB is unreachable so pages still render.
 */
function social_links(): array
{
    $links = [];
    try {
        foreach (social_platforms() as $key => $platform) {
            $url = get_setting('social_' . $key);
            if ($url !== '') {
                $links[] = ['key' => $key, 'label' => $platform['label'], 'icon' => $platform['icon'], 'url' => $url];
            }
        }
    } catch (Throwable $ex) {
        return [];
    }
    return $links;
}

/**
 * Echo an <a> per link. Icon-only links get an aria-label; $withLabel adds
 * the platform name after the icon (used in the footer).
 */
function render_social_links(array $links, bool $withLabel = false): void
{
    foreach ($links as $link) {
        $svg = '<svg viewBox="0 0 24 24"><path d="' . e($link['icon']) . '"/></svg>';
        if ($withLabel) {
            echo '<a href="' . e($link['url']) . '" target="_blank" rel="noopener">' . $svg . ' ' . e($link['label']) . '</a>';
        } else {
            echo '<a href="' . e($link['url']) . '" target="_blank" rel="noopener" aria-label="' . e($link['label']) . '">' . $svg . '</a>';
        }
    }
}
