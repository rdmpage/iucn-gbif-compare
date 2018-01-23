<?php

// Given a list of species extract GeoJSON from IUCN shape files and get GBIF GeoJSON

require_once (dirname(__FILE__) . '/lib.php');


$shp_dir = dirname(__FILE__) . '/data';

$filename = dirname(__FILE__) . '/data.csv';
//$filename = dirname(__FILE__) . '/data-new.csv';

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
			
				// get IUCN GeoJSON
				$command = 'ogr2ogr'
				 . ' -f Geojson ' . str_replace(' ', '_', $obj->Species) . '-iucn' . '.geojson'
				 . ' ' . $shp_dir . '/' . $obj->shp . '/' . $obj->shp . '.shp'
				 . ' -sql "SELECT * FROM ' . $obj->shp . ' WHERE binomial=\'' . $obj->Species . '\'"'
				 . '  -simplify 0.1';
			 
				 echo $command . "\n";
				 system($command);
			 
				 // get GBIF GeoJSON
				 
				 if (isset($obj->GBIF) && ($obj->GBIF != ''))
				 {
				 	$url = 'https://scarlet-broccoli.glitch.me/' . $obj->GBIF . '.geojson';

					$json = get($url);
					file_put_contents(str_replace(' ', '_', $obj->Species) . '-gbif' . '.geojson', $json);
				}

			 
				 // figure out how to merge and compare
			
			
			}
		}
	}
	$count++;
}

?>

