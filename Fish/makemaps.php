<?php

// Parse data file that has bene processed and generate drawings


function geojson_to_svg($filename)
{
	$svg_filename = str_replace('geojson', 'svg', $filename);

	$json = file_get_contents($filename);

	$obj = json_decode($json);

	//print_r($obj);

	$coordinates = array();

	// polygons
	$polygons = array();


	// pts
	foreach ($obj->features as $feature)
	{
		//print_r($feature);exit();
		if ($feature->geometry->type == 'Point')
		{
			$coordinates[] = $feature->geometry->coordinates;
		}
	
		if ($feature->geometry->type == 'Polygon')
		{
			$polygons[] = $feature->geometry->coordinates[0];
		}

		if ($feature->geometry->type == 'MultiPolygon')
		{
			foreach ($feature->geometry->coordinates as $mp)
			{
				foreach ($mp as $p)
				{
					$polygons[] = $p;
				}
			}
		}
	}



	//<g transform="translate(180,90) scale(1,-1)">';

	$xml = '<?xml version="1.0" encoding="UTF-8"?>
	<svg xmlns:xlink="http://www.w3.org/1999/xlink" 
	xmlns="http://www.w3.org/2000/svg" 
	width="360px" height="180px">
	   <style type="text/css">
		  <![CDATA[     
		  .region 
		  { 
			fill:blue; 
			opacity:0.4; 
			stroke:blue;
		  }
		  ]]>
	   </style>

	<circle id="dot" x="-2" y="-2" r="2" style="stroke:none; stroke-width:0; fill:black; opacity:0.7;"/>


	 <image x="0" y="0" width="360" height="180" xlink:href="' . 'map.jpg"/>

 
	 <g transform="translate(180,90) scale(1,-1)">';
 

	foreach ($coordinates as $loc)
	{
		$xml .= '   <use xlink:href="#dot" transform="translate(' . $loc[0] . ',' . $loc[1] . ')" />';
	}

	foreach ($polygons as $p)
	{
		$xml .= '<polygon class="region" points="';
		foreach ($p as $pt)
		{
			$xml .= $pt[0] . ',' . $pt[1] . ' ';
		}
		$xml .= '" />'; 
	}

	/*
			$xml .= '<polygon class="region" points="';
			foreach ($hull as $p)
			{
				$xml .= $p[0] . ',' . $p[1] . ' ';
			}
			$xml .= '" />'; 



	*/


	$xml .= '
		  </g>
		</svg>';
	

	file_put_contents($svg_filename, $xml);

}


$filename =  '../fish.csv';

$file_handle = fopen($filename, "r");


$html = '<html>
<body>';

$keys = array();

$count = 0;

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
				
				// fix species name, some have authorship info
				if (preg_match('/(?<name>\w+ \w+)/', $obj->Species, $m))
				{
					$obj->Species = $m['name'];
				}
				
				$basefilename = str_replace(' ', '_', $obj->Species);
				
				
				// generate SVG
				
				geojson_to_svg($basefilename  . '-gbif.geojson');
				geojson_to_svg($basefilename  . '-iucn.geojson');
				
				// html
				$html .= '<div>' . "\n";
				$html .= '<h3>' . $obj->Species . '</h3>' . "\n";
				$html .= '<p><a href="http://www.iucnredlist.org/details/' . $obj->IUCN . '" target="_new">IUCN</a> <a href="https://gbif.org/species/' . $obj->GBIF . '" target="_new">GBIF</a></p>' . "\n";
				$html .= '<img style="background-image:url(map.jpg)" src="' . $basefilename  . '-iucn.svg" />'. "\n";
				$html .= '<img style="background-image:url(map.jpg)" src="' . $basefilename  . '-gbif.svg" />'. "\n";
				
				$html .= '</div>' . "\n";
				
			
			}
		}
	}
	$count++;
}

$html .= '</body>
</html>';

echo $html;


?>