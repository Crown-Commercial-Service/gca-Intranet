<?php
use fewbricks\acf AS fewacf;
use fewbricks\acf\fields AS acf_fields;

$location = [
    [
        [
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'blog'
        ]
    ]
];

$fg1 = ( new fewacf\field_group( 'Author image', '202603101125a', $location, 10, [
    'names_of_items_to_hide_on_screen' => [
    ]
]));

$fg1->add_field(new acf_fields\image( 'Image' , 'image' , '202603101125b',[
    'instructions' => 'The height of this image is flexible. Minimum width: 618 px. Recommended size: 1236&times;818.'
] ));


$fg1->register();
