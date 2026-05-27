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

		$temperature = $obj->temp;
		$rounded_temperature = ceil($temperature);
		$price = 35 - $rounded_temperature;
		$measurement_time = $obj->time;

		// Date/Time is wanted
		if (in_array('datetime', $atts) || isset($atts['datetime'])) {
			$datetime = DateTimeImmutable::createFromFormat(
				DateTimeInterface::ATOM,
				$measurement_time
			);

			$datetime = $datetime->setTimezone(new DateTimeZone('Europe/Helsinki'));

			$formatted_datetime = $datetime->format('j.n. h.i');

			if (($atts['datetime'] ?? '') === 'date') {
				$formatted_datetime = $datetime->format('j.n.');
			}

			if (($atts['datetime'] ?? '') === 'time') {
				$formatted_datetime = $datetime->format('h.i');
			}

			return '<span class="measurement-time" data-time="' . $datetime->format(DateTimeInterface::ATOM) . '">' . esc_html($formatted_datetime) . '</span>';
		}

		// Temperature is wanted
		if (in_array('temp', $atts)) {
			$formatted_temperature = number_format($rounded_temperature, 0);

			return '<span data-temperature="' . esc_attr($temperature) . '" class="measurement-temperature">' . esc_html($formatted_temperature) . ' °C</span>';
		}

		// Price is wanted
		if (in_array('price', $atts)) {
			$formatted_price = number_format($price, 0);

			return '<span class="measurement-price">' . esc_html($formatted_price) . ' €</span>';
		}

		return;
    }

    return '--';
}

/* */