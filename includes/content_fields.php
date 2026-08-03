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
            'groups' => [
                [
                    'title' => 'Page header',
                    'fields' => [
                        'about_eyebrow' => ['label' => 'Eyebrow text', 'type' => 'text'],
                        'about_title'   => ['label' => 'Page title', 'type' => 'text'],
                        'about_intro'   => ['label' => 'Intro paragraph', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Our Story',
                    'image' => ['slot' => 'about_story_image', 'label' => 'Story photo', 'size' => '1000 × 700px'],
                    'fields' => [
                        'story_heading' => ['label' => 'Heading', 'type' => 'text'],
                        'story_para1'   => ['label' => 'Paragraph 1', 'type' => 'textarea'],
                        'story_para2'   => ['label' => 'Paragraph 2', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Stats band',
                    'image' => ['slot' => 'about_stats_bg', 'label' => 'Background (optional)', 'size' => '1600 × 500px'],
                    'fields' => [
                        'about_stat1_num' => ['label' => 'Stat 1 number', 'type' => 'text'], 'about_stat1_cap' => ['label' => 'Stat 1 caption', 'type' => 'text'],
                        'about_stat2_num' => ['label' => 'Stat 2 number', 'type' => 'text'], 'about_stat2_cap' => ['label' => 'Stat 2 caption', 'type' => 'text'],
                        'about_stat3_num' => ['label' => 'Stat 3 number', 'type' => 'text'], 'about_stat3_cap' => ['label' => 'Stat 3 caption', 'type' => 'text'],
                        'about_stat4_num' => ['label' => 'Stat 4 number', 'type' => 'text'], 'about_stat4_cap' => ['label' => 'Stat 4 caption', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'What We Do',
                    'image' => ['slot' => 'about_whatwedo_image', 'label' => 'Photo', 'size' => '600 × 800px, portrait'],
                    'fields' => [
                        'whatwedo_heading' => ['label' => 'Heading', 'type' => 'text'],
                        'whatwedo_item1' => ['label' => 'Item 1', 'type' => 'text'],
                        'whatwedo_item2' => ['label' => 'Item 2', 'type' => 'text'],
                        'whatwedo_item3' => ['label' => 'Item 3', 'type' => 'text'],
                        'whatwedo_item4' => ['label' => 'Item 4', 'type' => 'text'],
                        'whatwedo_item5' => ['label' => 'Item 5', 'type' => 'text'],
                        'whatwedo_item6' => ['label' => 'Item 6', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Vision',
                    'image' => ['slot' => 'about_vision_image_1', 'label' => 'Photo', 'size' => '500 × 500px, transparent PNG recommended'],
                    'fields' => [
                        'vision_heading' => ['label' => 'Heading', 'type' => 'text'],
                        'vision_text'    => ['label' => 'Text', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Mission',
                    'image' => ['slot' => 'about_vision_image_2', 'label' => 'Photo', 'size' => '500 × 500px, transparent PNG recommended'],
                    'fields' => [
                        'mission_heading' => ['label' => 'Heading', 'type' => 'text'],
                        'mission_text'    => ['label' => 'Text', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Team photo 1',
                    'image' => ['slot' => 'about_team_1', 'label' => 'Photo', 'size' => '600 × 800px, portrait (3:4)'],
                    'fields' => [
                        'team_member1_title' => ['label' => 'Title', 'type' => 'text'],
                        'team_member1_text'  => ['label' => 'Text', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Team photo 2',
                    'image' => ['slot' => 'about_team_2', 'label' => 'Photo', 'size' => '600 × 800px, portrait (3:4)'],
                    'fields' => [
                        'team_member2_title' => ['label' => 'Title', 'type' => 'text'],
                        'team_member2_text'  => ['label' => 'Text', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Team photo 3',
                    'image' => ['slot' => 'about_team_3', 'label' => 'Photo', 'size' => '600 × 800px, portrait (3:4)'],
                    'fields' => [
                        'team_member3_title' => ['label' => 'Title', 'type' => 'text'],
                        'team_member3_text'  => ['label' => 'Text', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Team photo 4',
                    'image' => ['slot' => 'about_team_4', 'label' => 'Photo', 'size' => '600 × 800px, portrait (3:4)'],
                    'fields' => [
                        'team_member4_title' => ['label' => 'Title', 'type' => 'text'],
                        'team_member4_text'  => ['label' => 'Text', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Bottom CTA & world map',
                    'image' => ['slot' => 'about_world_map', 'label' => 'World map', 'size' => '1000 × 700px'],
                    'fields' => [
                        'about_cta_heading'      => ['label' => 'Heading', 'type' => 'text'],
                        'about_cta_text'         => ['label' => 'Text', 'type' => 'textarea'],
                        'about_cta_button_label' => ['label' => 'Button label', 'type' => 'text'],
                    ],
                ],
            ],
        ],
        'contact_page' => [
            'title' => 'Contact page (contact.php)',
            'page'  => 'contact',
            'groups' => [
                [
                    'title' => 'Page header',
                    'fields' => [
                        'contact_eyebrow' => ['label' => 'Eyebrow text', 'type' => 'text'],
                        'contact_title'   => ['label' => 'Page title', 'type' => 'text'],
                        'contact_intro'   => ['label' => 'Intro paragraph', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Phone card',
                    'fields' => [
                        'phone_card_text' => ['label' => 'Card text', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Email card',
                    'fields' => [
                        'email_card_text' => ['label' => 'Card text', 'type' => 'textarea'],
                    ],
                ],
                [
                    'title' => 'Address card & map',
                    'fields' => [
                        'address_label'     => ['label' => 'Address heading', 'type' => 'text'],
                        'address_card_text' => ['label' => 'Card text', 'type' => 'textarea'],
                        'contact_address'   => ['label' => 'Full address (used for the map)', 'type' => 'text'],
                    ],
                ],
                [
                    'title' => 'Enquiry form',
                    'fields' => [
                        'form_heading'         => ['label' => 'Section heading', 'type' => 'text'],
                        'form_button_label'    => ['label' => 'Button label', 'type' => 'text'],
                        'form_success_message' => ['label' => 'Success message', 'type' => 'textarea'],
                    ],
                ],
            ],
        ],
    ];
}

/**
 * Return every field-key => meta pair in a section, whether the section
 * uses the flat `fields` format or the grouped `groups[].fields` format.
 */
function content_section_fields(array $section): array
{
    if (!empty($section['groups'])) {
        $all = [];
        foreach ($section['groups'] as $group) {
            $all += $group['fields'] ?? [];
        }
        return $all;
    }
    return $section['fields'] ?? [];
}
