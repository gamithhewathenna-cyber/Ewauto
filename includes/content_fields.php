<?php
/**
 * Field metadata for the "Page Content" admin screen. Grouped by homepage
 * section, each field maps to a row in the `content` table. Some sections
 * also list `images`, which map to rows in the `images` table (slot key).
 */
function content_field_sections(): array
{
    return [
        'hero' => [
            'title' => 'Hero banner',
            'images' => [
                'hero_scooter' => 'Hero image (used only if no slider images are set)',
            ],
            'fields' => [
                'hero_eyebrow'    => ['label' => 'Eyebrow text', 'type' => 'text'],
                'hero_title'      => ['label' => 'Big title', 'type' => 'text'],
                'hero_copy'       => ['label' => 'Paragraph', 'type' => 'textarea'],
                'hero_cta_label'  => ['label' => 'Button label', 'type' => 'text'],
                'spec1_label' => ['label' => 'Spec 1 label', 'type' => 'text'], 'spec1_value' => ['label' => 'Spec 1 value', 'type' => 'text'],
                'spec2_label' => ['label' => 'Spec 2 label', 'type' => 'text'], 'spec2_value' => ['label' => 'Spec 2 value', 'type' => 'text'],
                'spec3_label' => ['label' => 'Spec 3 label', 'type' => 'text'], 'spec3_value' => ['label' => 'Spec 3 value', 'type' => 'text'],
                'spec4_label' => ['label' => 'Spec 4 label', 'type' => 'text'], 'spec4_value' => ['label' => 'Spec 4 value', 'type' => 'text'],
                'spec5_label' => ['label' => 'Spec 5 label', 'type' => 'text'], 'spec5_value' => ['label' => 'Spec 5 value', 'type' => 'text'],
            ],
        ],
        'intro' => [
            'title' => 'Intro & lineup',
            'images' => [
                'lineup_vehicles' => 'Lineup row image (used only if no products are set)',
            ],
            'fields' => [
                'intro_para1'   => ['label' => 'Paragraph 1', 'type' => 'textarea'],
                'intro_para2'   => ['label' => 'Paragraph 2', 'type' => 'textarea'],
                'intro_heading' => ['label' => 'Side heading', 'type' => 'textarea'],
            ],
        ],
        'feature' => [
            'title' => 'Kunpeng feature',
            'images' => [
                'kunpeng_scooter' => 'Feature image',
            ],
            'fields' => [
                'feature_title' => ['label' => 'Title', 'type' => 'text'],
                'feature_sub'   => ['label' => 'Subtitle', 'type' => 'textarea'],
                'kfeature1_label' => ['label' => 'Spec 1 label', 'type' => 'text'], 'kfeature1_value' => ['label' => 'Spec 1 value', 'type' => 'text'],
                'kfeature2_label' => ['label' => 'Spec 2 label', 'type' => 'text'], 'kfeature2_value' => ['label' => 'Spec 2 value', 'type' => 'text'],
                'kfeature3_label' => ['label' => 'Spec 3 label', 'type' => 'text'], 'kfeature3_value' => ['label' => 'Spec 3 value', 'type' => 'text'],
                'kfeature4_label' => ['label' => 'Spec 4 label', 'type' => 'text'], 'kfeature4_value' => ['label' => 'Spec 4 value', 'type' => 'text'],
                'kfeature5_label' => ['label' => 'Spec 5 label', 'type' => 'text'], 'kfeature5_value' => ['label' => 'Spec 5 value', 'type' => 'text'],
            ],
        ],
        'world' => [
            'title' => 'World wide reach',
            'images' => [
                'world_map'          => 'World map image',
                'testimonial_avatar' => 'Testimonial avatar',
            ],
            'fields' => [
                'world_heading_prefix'    => ['label' => 'Heading prefix', 'type' => 'text'],
                'world_heading_highlight' => ['label' => 'Heading highlight', 'type' => 'text'],
                'world_copy'              => ['label' => 'Paragraph', 'type' => 'textarea'],
                'testimonial_name'        => ['label' => 'Testimonial name', 'type' => 'text'],
                'testimonial_role'        => ['label' => 'Testimonial role', 'type' => 'text'],
                'testimonial_quote'       => ['label' => 'Testimonial quote', 'type' => 'textarea'],
            ],
        ],
        'cta' => [
            'title' => 'Call to action & stats',
            'images' => [
                'cta_rider' => 'CTA background image',
            ],
            'fields' => [
                'cta_heading'      => ['label' => 'Heading', 'type' => 'text'],
                'cta_copy'         => ['label' => 'Paragraph', 'type' => 'textarea'],
                'cta_button_label' => ['label' => 'Button label', 'type' => 'text'],
                'stat1_num' => ['label' => 'Stat 1 number', 'type' => 'text'], 'stat1_cap' => ['label' => 'Stat 1 caption', 'type' => 'text'],
                'stat2_num' => ['label' => 'Stat 2 number', 'type' => 'text'], 'stat2_cap' => ['label' => 'Stat 2 caption', 'type' => 'text'],
                'stat3_num' => ['label' => 'Stat 3 number', 'type' => 'text'], 'stat3_cap' => ['label' => 'Stat 3 caption', 'type' => 'text'],
                'stat4_num' => ['label' => 'Stat 4 number', 'type' => 'text'], 'stat4_cap' => ['label' => 'Stat 4 caption', 'type' => 'text'],
            ],
        ],
        'footer' => [
            'title' => 'Footer & contact',
            'images' => [
                'logo_header' => 'Header logo',
                'logo_footer' => 'Footer logo',
            ],
            'fields' => [
                'footer_about'  => ['label' => 'About paragraph', 'type' => 'textarea'],
                'contact_email' => ['label' => 'Contact email', 'type' => 'text'],
                'contact_phone' => ['label' => 'Contact phone', 'type' => 'text'],
                'footer_bottom' => ['label' => 'Copyright line', 'type' => 'text'],
            ],
        ],
        'contact_page' => [
            'title' => 'Contact page (contact.php)',
            'fields' => [
                'contact_eyebrow'      => ['label' => 'Eyebrow text', 'type' => 'text'],
                'contact_title'        => ['label' => 'Page title', 'type' => 'text'],
                'contact_intro'        => ['label' => 'Intro paragraph', 'type' => 'textarea'],
                'phone_card_text'      => ['label' => 'Phone card text', 'type' => 'textarea'],
                'email_card_text'      => ['label' => 'Email card text', 'type' => 'textarea'],
                'address_label'        => ['label' => 'Address heading', 'type' => 'text'],
                'address_card_text'    => ['label' => 'Address card text', 'type' => 'textarea'],
                'contact_address'      => ['label' => 'Full address (used for the map)', 'type' => 'text'],
                'form_heading'         => ['label' => 'Form section heading', 'type' => 'text'],
                'form_button_label'    => ['label' => 'Form button label', 'type' => 'text'],
                'form_success_message' => ['label' => 'Form success message', 'type' => 'textarea'],
            ],
        ],
    ];
}
