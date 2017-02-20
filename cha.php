<?php
//Project: 	C header analysis
//Author: 	Jakub Svoboda	
//Login:	xsvobo0z                                            
//Date:		20.2.2017
//Email:	xsvobo0z@stud.fit.vutbr.cz

function printHelp(){
	echo "\n";
	echo "C header analysis\n";
	echo "Usage: \nphp5.6 [--help] [--input=fileordir] [--output=filename] [pretty-xml[=k]] [--no-inline] [--max-par=n] [--remove-whitespace]\n";
	echo "\n";
	echo "--help: Prints out the instructions.\n";
	echo "--input=fileordir: The file or the directory for the analysis. In case of a folder, all the files and subfolders are analysed.\n";
	echo "--output=filename: Specifies the name of XML file that will be created as an outup. If no file is specified, the table will be printed on standard output.\n";
	echo "--pretty-xml=k: Formats the XML output file into more readable form. Files in subdirectories get a k-spaces indentation.\n";
	echo "--no-inline: Skips inline functions.\n";
	echo "--max-par=n: Only functions with l parameters get processed.\n";
	echo "--no-duplicates: Duplicate function prototypes will be processed only once.\n";
	echo "--remove-whitespace: Replaces all white spaces with spaces and removes obsolete spaces.\n";
	echo "\n";
}
$longops=array(
	"help::",
	"input:",
	"output:",
	"pretty-xml::",
	"no-inline::",
	"max-par:",
	"no-duplicates::",
	"remove-whitespace::"
);

$flags = getopt(NULL, $longops);
$flagCount= count($flags);
for($x = 0; $x < $flagCount; $x++) {
    echo $flags[$x];
    echo "\n";
}


//if ($flags == false){
//	echo "No params\n\n";
//}else{
//	echo array_key_exists(0, $flags) == false;
//	echo "\nhello\n";
//}


?>