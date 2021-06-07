ini_set('display_errors', 'on');

function convertCsvToXmlFile($input_file, $output_file) {
	// Open csv file for reading
	$inputFile  = fopen($input_file, 'rt');
	// print_r($input_file);
	// Get the headers of the file
	$headers = fgetcsv($inputFile);
	echo "<pre>";
	print_r($headers);
	echo "</pre>";
	// Create a new dom document with pretty formatting
	$doc  = new DomDocument("1.0","UTF-8");
	$doc->formatOutput = true;

	echo "<pre>";
	print_r($doc);
	echo "</pre>";
	
	// Add a root node to the document
	$root = $doc->createElement('titles_rus');
	$root = $doc->appendChild($root);
	
	// Loop through each row creating a <policy> node with the correct data
	while (($row = fgetcsv($inputFile)) !== FALSE)
	{
		$container = $doc->createElement('title_ru');
		
		foreach($headers as $i => $header)
		{
			$child = $doc->createElement($header);
			$child = $container->appendChild($child);
			$value = $doc->createTextNode($row[$i]);
			$value = $child->appendChild($value);
		}
		$root->appendChild($container);
	}
	
	$strxml = $doc->saveXML();
	
	$handle = fopen($output_file, "w");
	fwrite($handle, $strxml);
	fclose($handle);
}

$input = __DIR__ .'/' .'Kasan1'. '.xls';
print_r($input);
$output = __DIR__ .'/' .'Kasan'. '.xml';

convertCsvToXmlFile($input , $output);