<?php


$filename = dirname(__FILE__) . '/data.csv';

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
					$obj->{$keys[$k]} = $v;
				}
			
				print_r($obj);
			
				// get IUCN GeoJSON
				$command = 'ogr2ogr'
				 . ' -f Geojson ' . str_replace(' ', '_', $obj->Species) . '.geojson'
				 . ' ' . $obj->shp . '/' . $obj->shp . '.shp'
				 . ' -sql "SELECT * FROM ' . $obj->shp . ' WHERE binomial=\'' . $obj->Species . '\'"'
				 . '  -simplify 0.1';
			 
				 echo $command . "\n";
				 system($command);
			 
				 // get GBIF GeoJSON
			 
			 
			 
				 // figure out how to merge and compare
			
			
			}
		}
	}
	$count++;
}

?>

