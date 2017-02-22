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

function checkDuplicates(array $flags, $parameter){		//TODO
	if (is_array($flags[$parameter])) {
			fwrite(STDERR,"Duplicite parameter. \n"  );
			die(ERROR_PARAMETERS);
		}
}

function checkValidArguments(array $argv){
	$counter =0;
	foreach($argv as $argument){
		if($counter == 0){
			$counter++;
			continue;
		}
		if ((strpos($argument, 'help') === false) 	&& 
			(strpos($argument, 'input') === false) 	&& 
			(strpos($argument, 'output') === false) && 
			(strpos($argument, 'pretty-xml') === false) && 
			(strpos($argument, 'no-inline') === false)&& 
			(strpos($argument, 'max-par') === false)&& 
			(strpos($argument, 'no-duplicate') === false) &&
			(strpos($argument, 'remove-whitespace') === false))	
		{		
			fwrite(STDERR,"Unknown parameter: ");
			fwrite(STDERR, $argument);
			fwrite(STDERR, "\n");	
			exit(ERROR_PARAMETERS);
		}
		$counter++;
	}
	
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

define("ERROR_PARAMETERS",1);
define("ERROR_INPUT",2);
define("ERROR_OUTPUT",3);

$flags = getopt(NULL, $longops);
$flagCount= count($flags);
checkValidArguments($argv);								//Makes sure that a nondefined parameter cannot be entered.

//parameters default values:
$input = false;
$output = false;
$prettyXml=4;			//default value
$noInline = false;
$maxPar=false;
$noDuplicates=false;
$removeWhitespace=false;


foreach(array_keys($flags) as $parameter){	
	checkDuplicates($flags, $parameter);					//Duplicit argument control, aka --input --input		//TODO
	switch ($parameter) {
		case "help":										//help parameter recognized
			if($flagCount !== 1){
				fwrite(STDERR,"Parameter --help received in combination with another parameter.\n");
				exit(ERROR_PARAMETERS);
			}else{
				printHelp();
				exit(0);
			}
			break;
		case "input":										//input parameter recognized	
			$input = $flags[$parameter];
			break;
		case "output":										//output parameter recognized
			$output=$flags[$parameter];
			break;
		case "pretty-xml":									//pretty-xml parameter recognized
			$prettyXml=$flags[$parameter];
			break;
		case "no-inline":									//no-inline parameter recognized
			$noInline=true;
			break;
		case "max-par":										//max-par parameter recognized
			$maxPar=$flags[$parameter];
			break;
		case "no-duplicates":								//no-duplicates parameter recognized
			$noDuplicates=true;
			break;
		case "remove-whitespace":							//remove-whitespace parameter recognized
			$removeWhitespace=true;
			break;		
		default:
			fwrite(STDERR,"Unknown parameter. \n");			//obsolete
			exit(ERROR_PARAMETERS);
			
	}		
}

?>