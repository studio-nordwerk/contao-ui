<?php

declare(strict_types=1);

use Nordwerk\TestimonialsBundle\Model\TestimonialArchiveModel;
use Nordwerk\TestimonialsBundle\Model\TestimonialModel;

$GLOBALS['BE_MOD']['content']['nw_testimonials'] = ['tables' => ['tl_nw_testimonial_archive', 'tl_nw_testimonial']];
$GLOBALS['TL_MODELS']['tl_nw_testimonial_archive'] = TestimonialArchiveModel::class;
$GLOBALS['TL_MODELS']['tl_nw_testimonial'] = TestimonialModel::class;
