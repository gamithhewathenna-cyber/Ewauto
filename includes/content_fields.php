<?php
/**
 * Field metadata for the "Page Content" admin screen. Grouped by homepage
 * section, each field maps to a row in the `content` table. Some sections
 * also list `images`, which map to rows in the `images` table (slot key).
 */
function content_field_sections(): array
{
    return [
        'intro' => [
            'title' => 'Intro & lineup',
            'page'  => 'home',
            'images' => [
                'lineup_vehicles' => ['label' => 'Lineup row image', 'size' => '1600 × 500px'],
            ],
            'fields' => [
                'intro_para1'   => ['label' => 'Paragraph 1', 'type' => 'textarea'],
                'intro_para2'   => ['label' => 'Paragraph 2', 'type' => 'textarea'],
                'intro_heading' => ['label' => 'Side heading', 'type' => 'textarea'],
            ],
        ],
        'feature' => [
            'title' => 'Our Bikes section intro (bikes themselves are managed under Bikes)',
            'page'  => 'home',
            'fields' => [
                'feature_title' => ['label' => 'Section title', 'type' => 'text'],
                'feature_sub'   => ['label' => 'Section subtitle', 'type' => 'textarea'],
            ],
        ],
        'world' => [
            'title' => 'World wide reach',
            'page'  => 'home',
            'images' => [
                'world_map'          => ['label' => 'World map image', 'size' => '1000 × 700px'],
                'testimonial_avatar' => ['label' => 'Testimonial avatar', 'size' => '200 × 200px, square'],
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
            'page'  => 'home',
            'images' => [
                'cta_rider' => ['label' => 'CTA background image', 'size' => '1600 × 900px'],
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
            'title' => 'Footer & contact (shown on every page)',
            'page'  => 'home',
            'fields' => [
                'footer_about'  => ['label' => 'About paragraph', 'type' => 'textarea'],
                'contact_email' => ['label' => 'Contact email', 'type' => 'text'],
                'contact_phone' => ['label' => 'Contact phone', 'type' => 'text'],
                'footer_bottom' => ['label' => 'Copyright line', 'type' => 'text'],
            ],
        ],
        'about_page' => [
            'title' => 'About page (about.php)',
            'page'  => 'about',
            'images' => [
                'about_story_image'    => ['label' => 'Our Story photo', 'size' => '1000 × 700px'],
                'about_stats_bg'       => ['label' => 'Stats band background (optional)', 'size' => '1600 × 500px'],
                'about_whatwedo_image' => ['label' => 'What We Do photo', 'size' => '600 × 800px, portrait'],
                'about_vision_image_1' => ['label' => 'Vision/Mission photo 1', 'size' => '500 × 500px, transparent PNG recommended'],
                'about_vision_image_2' => ['label' => 'Vision/Mission photo 2', 'size' => '500 × 500px, transparent PNG recommended'],
                'about_team_1'         => ['label' => 'Team photo 1', 'size' => '600 × 800px, portrait (3:4)'],
                'about_team_2'         => ['label' => 'Team photo 2', 'size' => '600 × 800px, portrait (3:4)'],
                'about_team_3'         => ['label' => 'Team photo 3', 'size' => '600 × 800px, portrait (3:4)'],
                'about_team_4'         => ['label' => 'Team photo 4', 'size' => '600 × 800px, portrait (3:4)'],
                'about_world_map'      => ['label' => 'World reach map', 'size' => '1000 × 700px'],
            ],
            'fields' => [
                'about_eyebrow' => ['label' => 'Eyebrow text', 'type' => 'text'],
                'about_title'   => ['label' => 'Page title', 'type' => 'text'],
                'about_intro'   => ['label' => 'Intro paragraph', 'type' => 'textarea'],

                'story_heading' => ['label' => 'Our Story heading', 'type' => 'text'],
                'story_para1'   => ['label' => 'Our Story paragraph 1', 'type' => 'textarea'],
                'story_para2'   => ['label' => 'Our Story paragraph 2', 'type' => 'textarea'],

                'about_stat1_num' => ['label' => 'Stat 1 number', 'type' => 'text'], 'about_stat1_cap' => ['label' => 'Stat 1 caption', 'type' => 'text'],
                'about_stat2_num' => ['label' => 'Stat 2 number', 'type' => 'text'], 'about_stat2_cap' => ['label' => 'Stat 2 caption', 'type' => 'text'],
                'about_stat3_num' => ['label' => 'Stat 3 number', 'type' => 'text'], 'about_stat3_cap' => ['label' => 'Stat 3 caption', 'type' => 'text'],
                'about_stat4_num' => ['label' => 'Stat 4 number', 'type' => 'text'], 'about_stat4_cap' => ['label' => 'Stat 4 caption', 'type' => 'text'],

                'whatwedo_heading' => ['label' => 'What We Do heading', 'type' => 'text'],
                'whatwedo_item1' => ['label' => 'What We Do item 1', 'type' => 'text'],
                'whatwedo_item2' => ['label' => 'What We Do item 2', 'type' => 'text'],
                'whatwedo_item3' => ['label' => 'What We Do item 3', 'type' => 'text'],
                'whatwedo_item4' => ['label' => 'What We Do item 4', 'type' => 'text'],
                'whatwedo_item5' => ['label' => 'What We Do item 5', 'type' => 'text'],
                'whatwedo_item6' => ['label' => 'What We Do item 6', 'type' => 'text'],

                'vision_heading' => ['label' => 'Vision heading', 'type' => 'text'],
                'vision_text'    => ['label' => 'Vision text', 'type' => 'textarea'],
                'mission_heading' => ['label' => 'Mission heading', 'type' => 'text'],
                'mission_text'    => ['label' => 'Mission text', 'type' => 'textarea'],

                'team_member1_title' => ['label' => 'Team 1 title', 'type' => 'text'], 'team_member1_text' => ['label' => 'Team 1 text', 'type' => 'text'],
                'team_member2_title' => ['label' => 'Team 2 title', 'type' => 'text'], 'team_member2_text' => ['label' => 'Team 2 text', 'type' => 'text'],
                'team_member3_title' => ['label' => 'Team 3 title', 'type' => 'text'], 'team_member3_text' => ['label' => 'Team 3 text', 'type' => 'text'],
                'team_member4_title' => ['label' => 'Team 4 title', 'type' => 'text'], 'team_member4_text' => ['label' => 'Team 4 text', 'type' => 'text'],

                'about_cta_heading'      => ['label' => 'Bottom CTA heading', 'type' => 'text'],
                'about_cta_text'         => ['label' => 'Bottom CTA text', 'type' => 'textarea'],
                'about_cta_button_label' => ['label' => 'Bottom CTA button label', 'type' => 'text'],
            ],
        ],
        'contact_page' => [
            'title' => 'Contact page (contact.php)',
            'page'  => 'contact',
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
