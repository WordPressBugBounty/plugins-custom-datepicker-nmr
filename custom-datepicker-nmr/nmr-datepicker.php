<?php
/*
Plugin Name: Custom Datepicker NMR
Plugin URI: https://namir.ro/custom-datepicker-for-cf7/
Description: Use [datepicker myFirstDatepicker id:myFirstDatepicker format:dd.mm.yy]
Author: Mircea N.
Text Domain: nmr-datepicker
Domain Path: /languages/
Version: 1.0.9
*/

define('NMR_DATEPICKER_PRO_URL', 'https://namir.ro/downloads/custom-datepicker-plus-nmr/');

function nmr_datepicker_plus_is_active()
{
    return class_exists('NmrDatepickerPlus');
}

// =========================================================================
// CF7 tag
// =========================================================================

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
        $atts['aria-describedby'] = wpcf7_get_validation_error_reference($tag->name);
    } else {
        $atts['aria-invalid'] = 'false';
    }

    $value = (string) reset($tag->values);

    if ($tag->has_option('placeholder') or $tag->has_option('watermark')) {
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

// =========================================================================
// Validation
// =========================================================================

function nmr_is_date($date, $format = 'Y-m-d')
{
    $format = preg_replace("/(y+)/", 'Y', $format);
    $format = preg_replace("/(m+)/", 'm', $format);
    $format = preg_replace("/(d+)/", 'd', $format);
    $d = DateTime::createFromFormat($format, $date);
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

add_filter('wpcf7_validate_datepicker',  'nmr_datepicker_validation_filter', 10, 2);
add_filter('wpcf7_validate_datepicker*', 'nmr_datepicker_validation_filter', 10, 2);

// =========================================================================
// Upsell: plugin action links
// =========================================================================

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nmr_datepicker_action_links');
function nmr_datepicker_action_links(array $links): array
{
    if (!nmr_datepicker_plus_is_active()) {
        $links['upgrade'] = '<a href="' . esc_url(NMR_DATEPICKER_PRO_URL) . '" target="_blank" style="color:#d63638;font-weight:600">Upgrade to Plus</a>';
    }
    $links['settings'] = '<a href="' . esc_url(admin_url('options-general.php?page=nmr-datepicker')) . '">Settings</a>';
    return $links;
}

// =========================================================================
// Upsell: dismissible admin notice
// =========================================================================

add_action('admin_notices', 'nmr_datepicker_admin_notice');
function nmr_datepicker_admin_notice()
{
    if (!current_user_can('manage_options')) return;
    if (nmr_datepicker_plus_is_active()) return;
    if (get_user_meta(get_current_user_id(), 'nmr_datepicker_notice_dismissed', true)) return;

    $dismiss_url = add_query_arg([
        'nmr_datepicker_dismiss' => '1',
        '_wpnonce'               => wp_create_nonce('nmr_datepicker_dismiss'),
    ]);
    ?>
    <div class="notice notice-info" style="display:flex;align-items:center;gap:16px;padding:12px 16px">
        <div style="flex:1">
            <strong>Custom Datepicker Plus NMR</strong> &mdash;
            exclude weekdays, date ranges, multi-select, timepicker, conditional fields, and more.
            <a href="<?php echo esc_url(NMR_DATEPICKER_PRO_URL); ?>" target="_blank" class="button button-primary" style="margin-left:8px">Upgrade to Plus &rarr;</a>
        </div>
        <a href="<?php echo esc_url($dismiss_url); ?>" style="color:#aaa;text-decoration:none;font-size:20px;line-height:1" title="Dismiss">&times;</a>
    </div>
    <?php
}

add_action('admin_init', 'nmr_datepicker_handle_notice_dismiss');
function nmr_datepicker_handle_notice_dismiss()
{
    if (
        isset($_GET['nmr_datepicker_dismiss']) &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'nmr_datepicker_dismiss')
    ) {
        update_user_meta(get_current_user_id(), 'nmr_datepicker_notice_dismissed', '1');
        wp_safe_redirect(remove_query_arg(['nmr_datepicker_dismiss', '_wpnonce']));
        exit;
    }
}

// =========================================================================
// Upsell: settings / upgrade page
// =========================================================================

add_action('admin_menu', 'nmr_datepicker_add_settings_page');
function nmr_datepicker_add_settings_page()
{
    add_options_page(
        'Custom Datepicker NMR',
        'Datepicker NMR',
        'manage_options',
        'nmr-datepicker',
        'nmr_datepicker_render_settings_page'
    );
}

function nmr_datepicker_render_settings_page()
{
    if (!current_user_can('manage_options')) return;

    if (nmr_datepicker_plus_is_active()) {
        echo '<div class="wrap"><h1>Custom Datepicker NMR</h1>';
        echo '<p>Custom Datepicker Plus NMR is active. <a href="' . esc_url(admin_url('options-general.php?page=nmr-datepickerplus')) . '">Go to Plus settings &rarr;</a></p>';
        echo '</div>';
        return;
    }

    $pro_url = NMR_DATEPICKER_PRO_URL;
    $tab     = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'features';
    $tabs    = [
        'features'  => 'Plus Features',
        'exclude'   => 'Date Exclusions',
        'advanced'  => 'Advanced',
    ];
    $base_url = admin_url('options-general.php?page=nmr-datepicker');
    ?>
    <div class="wrap">
        <h1>Custom Datepicker NMR</h1>

        <div style="background:#fff;border:1px solid #c3c4c7;border-left:4px solid #2271b1;padding:12px 16px;margin:16px 0;display:flex;align-items:center;gap:16px;max-width:900px">
            <div style="flex:1">&#128274; <strong>Plus features shown below are locked.</strong> Upgrade to unlock all settings and tags on this site.</div>
            <a href="<?php echo esc_url($pro_url); ?>" target="_blank" class="button button-primary" style="white-space:nowrap">Get Plus &rarr;</a>
        </div>

        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $slug => $label) : ?>
                <a href="<?php echo esc_url($base_url . '&tab=' . $slug); ?>"
                   class="nav-tab<?php echo $tab === $slug ? ' nav-tab-active' : ''; ?>">
                    <?php echo esc_html($label); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php
        switch ($tab) {
            case 'exclude':
                nmr_datepicker_upsell_exclude_tab($pro_url);
                break;
            case 'advanced':
                nmr_datepicker_upsell_advanced_tab($pro_url);
                break;
            default:
                nmr_datepicker_upsell_features_tab($pro_url);
        }
        ?>
    </div>
    <?php
}

function nmr_datepicker_upsell_features_tab(string $pro_url): void
{
    $features = [
        ['&#128197; Timepicker tag',           '[timepickerplus* myTime id:myTime min:09:00 max:17:00 step:30]',                   'Separate CF7 tag for time input (HTML5 native, min/max/step in minutes).'],
        ['&#128203; Multi-date selection',      '[datepickerplus sessions multiselect maxselect:3]',                                'Let users pick multiple dates. Comma-separated output. Optional cap.'],
        ['&#8644; Date range pair',             '[datepickerplus checkIn nmrrangepair:checkOut]',                                   'Link two date fields: start caps the end field\'s minimum and vice versa.'],
        ['&#9989; First available date',        '[datepickerplus delivery firstopen]',                                              'Auto-selects the first non-excluded date on page load.'],
        ['&#128065; Conditional fields',        '[datepickerplus eventDate nmrshowfield:event_notes]',                              'Show or hide other CF7 fields depending on whether a date is selected.'],
        ['&#128200; Year / month range',        '[datepickerplus myDate yearstart:2020 yearend:2030]',                              'Restrict the year dropdown range. Month/year dropdowns configurable globally.'],
        ['&#128196; Custom error messages',     '[datepickerplus myDate errmsg_required:"Pick a date"]',                            'Override CF7 validation messages per field.'],
        ['&#9881; Global settings page',        '',                                                                                 'Set site-wide defaults: date format, year range, first day of week.'],
    ];
    ?>
    <table class="widefat striped" style="max-width:900px;margin-top:1px">
        <thead>
            <tr>
                <th style="width:220px">Feature</th>
                <th style="width:340px">Example shortcode</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($features as [$name, $shortcode, $desc]) : ?>
            <tr>
                <td><strong><?php echo $name; ?></strong></td>
                <td><?php if ($shortcode) echo '<code style="font-size:11px">' . esc_html($shortcode) . '</code>'; ?></td>
                <td style="color:#555"><?php echo esc_html($desc); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin-top:20px">
        <a href="<?php echo esc_url($pro_url); ?>" target="_blank" class="button button-primary button-large">Get Custom Datepicker Plus NMR &rarr;</a>
    </p>
    <?php
}

function nmr_datepicker_upsell_exclude_tab(string $pro_url): void
{
    ?>
    <p style="margin-top:1em;color:#555;max-width:700px">
        In the Plus version you can block specific dates, date ranges, and entire weekdays directly from the shortcode.
        The <code>nmrexcludecpt</code> param also lets you pull blocked dates dynamically from a Custom Post Type.
    </p>
    <table class="form-table" style="max-width:900px">
        <tr>
            <th>Exclude specific dates / ranges</th>
            <td>
                <input type="text" disabled value="2024-12-24|2024-12-25|2024-12-26_2025-01-01" style="width:380px" />
                <p class="description">Pipe-separated. Use <code>start_end</code> for ranges. Param: <code>nmrexclude</code></p>
            </td>
        </tr>
        <tr>
            <th>Exclude weekdays</th>
            <td>
                <fieldset>
                    <label><input type="checkbox" disabled> Monday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled> Tuesday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled> Wednesday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled> Thursday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled> Friday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled checked> Saturday</label>&nbsp;&nbsp;
                    <label><input type="checkbox" disabled checked> Sunday</label>
                </fieldset>
                <p class="description">Param: <code>nmrexcludeweekdays:saturday|sunday</code></p>
            </td>
        </tr>
        <tr>
            <th>Blackout dates from CPT</th>
            <td>
                <input type="text" disabled value="blocked_dates" style="width:200px" />
                <p class="description">Post type slug. Each published post whose title is <code>YYYY-MM-DD</code> (or has a <code>nmr_blocked_date</code> meta) is blocked. Param: <code>nmrexcludecpt</code></p>
            </td>
        </tr>
        <tr>
            <th>Relative min / max</th>
            <td>
                <label>Min &nbsp;<input type="text" disabled value="+1" style="width:60px" /></label>
                &nbsp;&nbsp;
                <label>Max &nbsp;<input type="text" disabled value="+90" style="width:60px" /></label>
                <p class="description">Days from today. Accepts <code>+N</code>, <code>-N</code>, <code>today</code>. Params: <code>min</code> / <code>max</code></p>
            </td>
        </tr>
    </table>
    <p style="margin-top:20px">
        <a href="<?php echo esc_url($pro_url); ?>" target="_blank" class="button button-primary button-large">Unlock date exclusions &rarr;</a>
    </p>
    <?php
}

function nmr_datepicker_upsell_advanced_tab(string $pro_url): void
{
    $rows = [
        [
            'Default date format',
            '<input type="text" disabled value="dd.mm.yy" style="width:140px" />
             <p class="description">Site-wide default. Overridden per field with <code>format:</code> param.</p>',
        ],
        [
            'Year range',
            '<label>From &nbsp;<input type="number" disabled value="2020" style="width:80px" /></label>
             &nbsp;&nbsp;
             <label>To &nbsp;<input type="number" disabled value="2035" style="width:80px" /></label>
             <p class="description">Controls the year dropdown range on all datepicker fields.</p>',
        ],
        [
            'First day of week',
            '<select disabled><option>Monday</option></select>
             <p class="description">0 = Sunday, 1 = Monday, etc.</p>',
        ],
        [
            'Navigation dropdowns',
            '<label><input type="checkbox" disabled checked> Show month dropdown</label><br>
             <label><input type="checkbox" disabled checked> Show year dropdown</label>',
        ],
        [
            'Multi-date selection',
            '<label><input type="checkbox" disabled> Enable multi-select on this field</label><br>
             <label>Max dates &nbsp;<input type="number" disabled value="3" style="width:60px" /></label>
             <p class="description">Params: <code>multiselect</code> and <code>maxselect:N</code></p>',
        ],
        [
            'Date range pair',
            '<input type="text" disabled placeholder="End field name" style="width:200px" />
             <p class="description">CF7 name of the paired end-date field. Param: <code>nmrrangepair</code></p>',
        ],
        [
            'Conditional field visibility',
            '<label>Show field &nbsp;<input type="text" disabled placeholder="field_name" style="width:160px" /></label><br>
             <label>Hide field &nbsp;<input type="text" disabled placeholder="field_name" style="width:160px" /></label>
             <p class="description">Params: <code>nmrshowfield</code> / <code>nmrhidefield</code></p>',
        ],
    ];
    ?>
    <table class="form-table" style="max-width:900px">
        <?php foreach ($rows as [$label, $field]) : ?>
        <tr>
            <th><?php echo esc_html($label); ?></th>
            <td><?php echo $field; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p style="margin-top:20px">
        <a href="<?php echo esc_url($pro_url); ?>" target="_blank" class="button button-primary button-large">Unlock all of this &rarr;</a>
    </p>
    <?php
}
