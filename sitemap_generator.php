<?php

	/* Require essential libs */
	require 'php-firebase-lib/firebaseInterface.php';
	require 'php-firebase-lib/firebaseLib.php';
	require 'php-firebase-lib/firebaseStub.php';

	const DATABASE_URL = 'https://example.firebaseio.com/';
	const SITE_URL = 'http://example.com/';
	const DEFAULT_TOKEN = '';
	const STATES_PATH = '/states';

	$firebase = new \Firebase\FirebaseLib(DATABASE_URL, DEFAULT_TOKEN);
	$fetched_states = $firebase->get(STATES_PATH);
	$fetched_states = json_decode($fetched_states);
	$urls = array();

	array_push($urls, SITE_URL);

	$additionals_parent = array(
		array("path" => "categories", "start_url" => "category/", "end_url" => "seo_address", null),
		array(
			"path" => "posts",
			"start_url" => "product/",
			"end_url" => "seo_address",
			"condition" => array(
				array(
					"first_operand" => "status",
					"operator" => "==",
					"second_operand" => "active"
				)
			)
		),
		array(
			"path" => "tags",
			"start_url" => "offers/",
			"end_url" => "content",
			"condition" => array(
				array(
					"first_operand" => "type",
					"operator" => "==",
					"second_operand" => "relative"
				)
			)
		)
	);

	$additionals_child = array(
		array(
			"parent_path" => "categories",
			"selector_path" => array(
				"subcategories"
			),
			"start_url" => "category/",
			"end_url" => "seo_address"/*,
			"condition" => array(
				array(
					"first_operand" => "title",
					"operator" => "==",
					"second_operand" => "Metal Grinding"
				)
			)*/
		)
	);

	if ($fetched_states) {
		foreach ($fetched_states as $key => $value) {
			if ($key != "creation_time") {
				if ($value[0] == "/") {
					$value = substr($value, 1, strlen($value));
				}
				array_push($urls, SITE_URL . $value);
			}
		}
	}

	if ($additionals_parent) {

		for ($i = 0; $i < count($additionals_parent); $i++) {

			$fetched_temp = $firebase->get('/' . $additionals_parent[$i]['path']);
			$fetched_temp = json_decode($fetched_temp);

			foreach ($fetched_temp as $key => $value) {

				if ($additionals_parent[$i]['condition']) {

					$condition_check = array();
					for ($ii = 0; $ii < count($additionals_parent[$i]['condition']); $ii++) {

						$query = compare($value->{$additionals_parent[$i]['condition'][$ii]['first_operand']}, $additionals_parent[$i]['condition'][$ii]['operator'], $additionals_parent[$i]['condition'][$ii]['second_operand']);

						if ($query) {
							array_push($condition_check, true);
						}
					}
					if (count($condition_check) == count($additionals_parent[$i]['condition'])) {
						array_push($urls, SITE_URL . $additionals_parent[$i]['start_url'] . $value->{$additionals_parent[$i]['end_url']});
					}
				} else {
					array_push($urls, SITE_URL . $additionals_parent[$i]['start_url'] . $value->{$additionals_parent[$i]['end_url']});
				}
			}
		}
	}

	if ($additionals_child) {

		for ($i = 0; $i < count($additionals_child); $i++) {

			$fetched_temp = $firebase->get('/' . $additionals_child[$i]['parent_path']);
			$fetched_temp = json_decode($fetched_temp);

			foreach ($fetched_temp as $key => $value) {

				foreach ($value->{$additionals_child[$i]['selector_path'][0]} as $child1_key => $child1_value) {

					if ($additionals_child[$i]['condition']) {

						$condition_check = array();
						for ($ii = 0; $ii < count($additionals_child[$i]['condition']); $ii++) {

							$query = compare($child1_value->{$additionals_child[$i]['condition'][$ii]['first_operand']}, $additionals_child[$i]['condition'][$ii]['operator'], $additionals_child[$i]['condition'][$ii]['second_operand']);

							if ($query) {
								array_push($condition_check, true);
							}
						}
						if (count($condition_check) == count($additionals_child[$i]['condition'])) {
							array_push($urls, SITE_URL . $additionals_child[$i]['start_url'] . $value->seo_address . '/' . $child1_value->seo_address);
						}
					} else {
						array_push($urls, SITE_URL . $additionals_child[$i]['start_url'] . $value->seo_address . '/' . $child1_value->seo_address);
					}

						// array_push($urls, SITE_URL . $additionals_child[$i]['start_url'] . $value->seo_address . '/' . $child1_value->seo_address);
				}
			}
		}
	}

	if ($urls) {

		$xml = new DOMDocument();
		$xml->encoding = "UTF-8";

		$xml_urlset = $xml->createElement("urlset");
		$xml_urlset_attrs = $xml->createAttribute('xmlns');
		$xml_urlset_attrs->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
		$xml_urlset->appendChild( $xml_urlset_attrs );
		$current_time = gmdate("Y-m-d\TH:i:s\Z");

		foreach ($urls as $key => $value) {

			echo $value . "<br>";
			
			$xml_url = $xml->createElement("url");
			$xml_loc = $xml->createElement("loc");
			$xml_lastmod = $xml->createElement("lastmod");
			$xml_frequency = $xml->createElement("changefreq");

			$xml_url_value = $xml->createTextNode($value); 
			$xml_lastmod_value = $xml->createTextNode($current_time); 
			$xml_frequency_value = $xml->createTextNode("daily"); 


			$xml_loc->appendChild( $xml_url_value );
			$xml_lastmod->appendChild( $xml_lastmod_value );
			$xml_frequency->appendChild( $xml_frequency_value );
			$xml_url->appendChild( $xml_loc );
			$xml_url->appendChild( $xml_lastmod );
			$xml_url->appendChild( $xml_frequency );
			$xml_urlset->appendChild( $xml_url );
		}
		$xml->appendChild( $xml_urlset );
		$xml->save("sitemap.xml");
	}

	function compare($a, $operator, $b) {
		switch ($operator) {
			case "<":
				if ($a < $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "<=":
				if ($a <= $b) {
					return true;
				} else {
					return false;
				}
				break;
			case ">":
				if ($a > $b) {
					return true;
				} else {
					return false;
				}
				break;
			case ">=":
				if ($a >= $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "==":
				if ($a == $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "===":
				if ($a === $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "!=":
				if ($a != $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "!==":
				if ($a !== $b) {
					return true;
				} else {
					return false;
				}
				break;
			case "<>":
				if ($a <> $b) {
					return true;
				} else {
					return false;
				}
				break;
			default:
			    return false;
		}
	}
?>