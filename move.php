<?php

// Given a list of species extract GeoJSON from IUCN shape files and get GBIF GeoJSON

require_once (dirname(__FILE__) . '/lib.php');


$shp_dir = dirname(__FILE__) . '/data';

$filename = dirname(__FILE__) . '/data.csv';
$filename = dirname(__FILE__) . '/mammals.csv';
//$filename = dirname(__FILE__) . '/test.csv';

//$filename = dirname(__FILE__) . '/data-new.csv';
$filename = dirname(__FILE__) . '/amphibia.csv';
$filename = dirname(__FILE__) . '/test.csv';

//$filename = dirname(__FILE__) . '/fish.csv';
$filename = dirname(__FILE__) . '/extra2.csv';


$file_handle = fopen($filename, "r");


$count = 0;
$keys = array();

while (!feof($file_handle)) 
{
	$line = trim(fgets($file_handle));
	
	if (preg_match('/^#/', $line))
	{
		// skip
	}
	else
	{
	
		$parts = explode("\t", $line);
	
		if ($count == 0)
		{
			$keys = $parts;
		}
		else
		{
			$obj = null;
		
			if (count($parts) > 1)
			{
				$obj = new stdclass;
		
				foreach ($parts as $k => $v)
				{
					if (isset($keys[$k]))
					{
						$obj->{$keys[$k]} = $v;
					}
				}
			
				print_r($obj);
				
				// fix species name, some have authorship info
				if (preg_match('/(?<name>\w+ \w+)/', $obj->Species, $m))
				{
					$obj->Species = $m['name'];
				}
				
				$go = true;
				
				$filename = str_replace(' ', '_', $obj->Species) . '-iucn' . '.geojson';
				if (file_exists($filename))
				{
					switch ($obj->shp)
					{
						case 'ANURA':
						case 'CAUDATA':
							rename($filename, 'Amphibia/' . $filename);
							break;

						case 'FW_FISH':
							rename($filename, 'Fish/' . $filename);
							break;
							
						case 'TERRESTRIAL_MAMMALS':
							rename($filename, 'Mammals/' . $filename);
							break;

						case 'MARINE_MAMMALS':
							rename($filename, 'Mammals/' . $filename);
							break;
							
						default:
							break;
					}
				}

				
				$filename = str_replace(' ', '_', $obj->Species) . '-gbif' . '.geojson';
				if (file_exists($filename))
				{
					switch ($obj->shp)
					{
						case 'ANURA':
						case 'CAUDATA':
							rename($filename, 'Amphibia/' . $filename);
							break;

						case 'FW_FISH':
							rename($filename, 'Fish/' . $filename);
							break;

						case 'TERRESTRIAL_MAMMALS':
							rename($filename, 'Mammals/' . $filename);
							break;

						case 'MARINE_MAMMALS':
							rename($filename, 'Mammals/' . $filename);
							break;

						default:
							break;
					}
				}
			
			
			}
		}
	}
	$count++;
}

?>

