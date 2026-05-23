<?php

/**
 * FMI API
 * Read the temperature and the time from file
 * and register the shortcode
 */

add_shortcode('fmi_temp', 'read_fmi_file_87290');

function read_fmi_file_87290($atts) {
    // Path to where the cron job saves the file
    $file_path = ABSPATH . 'wp-content/fmi-temp.json';

    if (file_exists($file_path)) {
        $obj = json_decode(trim(file_get_contents($file_path)));

		// Date/Time is wanted
		if (in_array('datetime', $atts) || isset($atts['datetime'])) {
			$datetime = DateTimeImmutable::createFromFormat(
				DateTimeInterface::ISO8601,
				$obj->time
			);

			$datetime = $datetime->setTimezone(new DateTimeZone('Europe/Helsinki'));

			$formatted_datetime = $datetime->format('j.n. h.i');

			if (($atts['datetime'] ?? '') === 'date') {
				$formatted_datetime = $datetime->format('j.n.');
			}

			if (($atts['datetime'] ?? '') === 'time') {
				$formatted_datetime = $datetime->format('h.i');
			}

			return '<span class="fmi-11am-time" data-time="' . $datetime->format(DateTimeInterface::ISO8601) . '">' . esc_html($formatted_datetime) . '</span>';
		}

		// Temperature is wanted
		if (in_array('temp', $atts)) {
			$formatted_temp = number_format($obj->temp, 1, decimal_separator: ",");

			return '<span class="fmi-11am-temp">' . esc_html($formatted_temp) . ' °C</span>';
		}

		// Price is wanted
		if (in_array('price', $atts)) {
			$price = 35 - $obj->temp;
			$formatted_price = number_format($price, 2, decimal_separator: ",");

			return '<span class="fmi-11am-price">' . esc_html($formatted_price) . ' €</span>';
		}

		return;
    }

    return '--';
}
