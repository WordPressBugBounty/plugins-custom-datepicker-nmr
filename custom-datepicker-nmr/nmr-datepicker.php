<?php
/*
Plugin Name: Custom Datepicker NMR
Plugin URI: https://namir.ro/custom-datepicker-for-cf7/
Description: Use [datepicker myFirstDatepicker id:myFirstDatepicker format:dd.mm.yy]
Author: Mircea N.
Text Domain: nmr-datepicker
Domain Path: /languages/
Version: 1.0.8
*/


add_action('wpcf7_init', 'nmr_add_jqueryui_datepicker');
function nmr_add_jqueryui_datepicker()
{
    wpcf7_add_form_tag(
        array('datepicker', 'datepicker*'),
        'nmr_datepicker_form_tag_handler',
        array('name-attr' => true)
    );
}

function nmr_datepicker_form_tag_handler($tag)
{
    if (empty($tag->name)) {
        return '';
    }
    $validation_error = wpcf7_get_validation_error($tag->name);

    $class = wpcf7_form_controls_class($tag->type);

    $class .= ' wpcf7-validates-as-date';

    if ($validation_error) {
        $class .= ' wpcf7-not-valid';
    }

    $atts = array();
    $atts['class'] = $tag->get_class_option($class) . ' nmr-datepicker';
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option('tabindex', 'signed_int', true);
    $atts['min'] = $tag->get_date_option('min');
    $atts['max'] = $tag->get_date_option('max');
    $atts['step'] = $tag->get_option('step', 'int', true);
    $atts['data-format'] = $tag->get_option('format', '', true);
    $atts['data-datepickerjson'] = $tag->get_option('datepickerjson', '', true);

    if ($tag->has_option('readonly')) {
        $atts['readonly'] = 'readonly';
    }

    if ($tag->is_required()) {
        $atts['aria-required'] = 'true';
    }

    if ($validation_error) {
        $atts['aria-invalid'] = 'true';
        $atts['aria-describedby'] = wpcf7_get_validation_error_reference(
            $tag->name
        );
    } else {
        $atts['aria-invalid'] = 'false';
    }

    $value = (string) reset($tag->values);

    if (
        $tag->has_option('placeholder')
        or $tag->has_option('watermark')
    ) {
        $atts['placeholder'] = $value;
        $value = '';
    }

    $value = $tag->get_default_option($value);

    $value = wpcf7_get_hangover($tag->name, $value);

    $atts['value'] = $value;
    $atts['type'] = 'text';
    $atts['name'] = $tag->name;
    $atts = wpcf7_format_atts($atts);

    $html = sprintf(
        '<span class="wpcf7-form-control-wrap %1$s" data-name="%1$s"><input %2$s />%3$s</span>',
        sanitize_html_class($tag->name),
        $atts,
        $validation_error
    );
    return $html;
}

function nmr_datepicker_enqueue_script()
{
    $path = plugin_dir_url(__FILE__);
    wp_enqueue_style('jquery-ui-css', $path . 'css/jquery-ui.css');
    wp_enqueue_script('nmr_datepicker', $path . 'js/nmr-datepicker.js', array('jquery', 'jquery-ui-datepicker'));
}
add_action('wp_enqueue_scripts', 'nmr_datepicker_enqueue_script');

/* Validation filter */
function nmr_is_date($date, $format = 'Y-m-d')
{
    $format = preg_replace("/(y+)/", 'Y', $format);
    $format = preg_replace("/(m+)/", 'm', $format);
    $format = preg_replace("/(d+)/", 'd', $format);
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    $result = $d && $d->format($format) === $date;
    return apply_filters('nmr_is_date', $result, $date);
}

function nmr_create_date($date, $format = 'Y-m-d')
{
    $format = preg_replace("/(y+)/", 'Y', $format);
    $format = preg_replace("/(m+)/", 'm', $format);
    $format = preg_replace("/(d+)/", 'd', $format);
    $d = DateTime::createFromFormat($format, $date);
    return $d;
}

function nmr_datepicker_validation_filter($result, $tag)
{
    $name = $tag->name;

    $min = $tag->get_date_option('min');
    $max = $tag->get_date_option('max');
    $format = $tag->get_option('format', '', true);

    $value = isset($_POST[$name])
        ? trim(strtr((string) $_POST[$name], "\n", " "))
        : '';

    if ($tag->is_required() and '' === $value || !nmr_is_date($value, $format)) {
        $result->invalidate($tag, wpcf7_get_message('invalid_required'));
    } elseif ('' !== $value and !nmr_is_date($value, $format)) {
        $result->invalidate($tag, wpcf7_get_message('invalid_date'));
    } elseif ('' !== $value and !empty($min) and nmr_create_date($value, $format) < nmr_create_date($min)) {
        $result->invalidate($tag, wpcf7_get_message('date_too_early'));
    } elseif ('' !== $value and !empty($max) and nmr_create_date($max) < nmr_create_date($value, $format)) {
        $result->invalidate($tag, wpcf7_get_message('date_too_late'));
    }

    return $result;
}

add_filter('wpcf7_validate_datepicker', 'nmr_datepicker_validation_filter', 10, 2);
add_filter('wpcf7_validate_datepicker*', 'nmr_datepicker_validation_filter', 10, 2);
