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
		if($counter == 0){									//Condition for the fist argument (name of the script) to be skipped.
			$counter++;
			continue;
		}
		if ((strpos($argument, 'help') === false) 	&& 		//Seach each argument for substring of valid arguments
			(strpos($argument, 'input') === false) 	&& 
			(strpos($argument, 'output') === false) && 
			(strpos($argument, 'pretty-xml') === false) && 
			(strpos($argument, 'no-inline') === false)&& 
			(strpos($argument, 'max-par') === false)&& 
			(strpos($argument, 'no-duplicate') === false) &&
			(strpos($argument, 'remove-whitespace') === false))	
		{		
			fwrite(STDERR,"Unknown parameter: ");			//Not a valid argument, die.
			fwrite(STDERR, $argument);
			fwrite(STDERR, "\n");	
			exit(ERROR_PARAMETERS);
		}
		$counter++;
	}
	
}

function getFiles($input){
	$fileArray = [];											//initialize empty array for file names with .h ending
	if($input === false){										//opening all .h files in directory and subdirectories	
		$dirIterator = new RecursiveDirectoryIterator(__DIR__,RecursiveDirectoryIterator::SKIP_DOTS);		//based on http://stackoverflow.com/questions/15054997/find-all-php-files-in-folder-recursively
		$iterIter= new RecursiveIteratorIterator($dirIterator);
		foreach($iterIter as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) == "h") {
				array_push($fileArray, $file);						//append to file array
			}
		}
	}else{														//opening single file or dir (--input has been specified)
		if(file_exists($input)){									//if file or dir exists
			if(is_dir($input)){											//input is a subdirectory
				$dirIterator = new RecursiveDirectoryIterator($input,RecursiveDirectoryIterator::SKIP_DOTS);		//based on http://stackoverflow.com/questions/15054997/find-all-php-files-in-folder-recursively
				$iterIter= new RecursiveIteratorIterator($dirIterator);
				foreach($iterIter as $file) {
					if (pathinfo($file, PATHINFO_EXTENSION) == "h") {
						array_push($fileArray, $file);						//append to file array
					}
				}
			}else{														//input is a single file
				array_push($fileArray, $input);						//append to file array
			}	
		}else{
			fwrite(STDERR,"Nonexistant input file or folder.\n");			
			exit(ERROR_INPUT);
		}
	}
	return $fileArray;
}

function getDir($input){
	if($input === false){		//no --input, dir should be ./
		return "./";
	}else{
		if(is_dir($input)){
			$dir=$input;		//dir is route to subfolder
		}else{					//its a file, dir should be empty
			$dir = "";
		}
	}
	return $dir;
}

function format($str, $prettyXml){ //TODO there is no \n on the end of the output file, maybe its a problem? TODO pretty-xml without number
	if ($prettyXml === false){
		$str = str_replace("\n", '', $str);						//delete new lines
	}else{
		$str = str_replace("\n", '', $str);					
		$str = str_replace(">", ">\n", $str);					//add correct new lines
		for ($i = 0; $i < $prettyXml; $i++) {
			$str = str_replace("<function", " <function", $str);
			$str = str_replace("</function", " </function", $str);	
			$str = str_replace(" <functions", "<functions", $str);
			$str = str_replace(" </functions", "</functions", $str);			
		}	
	}	
	return $str;
}

function removeUnnecessary($text){
	$text=str_replace("\r\n",' ',$text);												//remove newlines (for windows newlines....)
	$text=str_replace("\n",' ',$text);													//remove newlines
	$text=str_replace("\t",' ',$text);													//remove tabs	
	$text=preg_replace("(\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/)","",$text);		//remove block comments
	$text=preg_replace('/"([^"]+)"/',"",$text);			//remove strings and replace with "" This is done for sneaky function-like strings.
	$text=preg_replace('/{(?:[^{}]+|(?R))*}/',"@",$text);		//remove all {} recursively, replace with @
	$text=preg_replace("/(typedef.*?;)/","",$text);				//remove typedefs
	$text=preg_replace("/(struct.*?;)/","",$text);				//remove structs
	$text=preg_replace("/(enum.*?;)/","",$text);				//remove enums
	return $text;
}

function removeWhitespace($text){
	$text=preg_replace('/\s+/',' ',$text);										//replaces double spaves with single one
	return $text;
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
$prettyXml=false;			
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
			if( in_array("--pretty-xml", $argv)){			
				$prettyXml=4;;
			}else{
				$prettyXml=$flags[$parameter];
			}
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

$targetfile;
$fileArray = getFiles($input);								//get all .h files to a single array
//TODO READ AND WRITE PERMISSIONs
if($output === false){										//output to stdout
	$targetFile=STDOUT;
}else{														//output to a file
	$targetFile=fopen($output,'w');
}

$xml = new DOMDocument('1.0', 'utf-8');						//create the XML document
$xmlFunctions = $xml->createElement( "functions" );			//create functions element (root)

$dir=getDir($input);										
$xmlFunctions->setAttribute("dir",$dir);					//TODO dir is not correct
$xmlFunctions= $xml->appendChild($xmlFunctions);			//appen root to xml
$text="";
$varargs="no";
$functionNamePattern = "#[_a-zA-Z][0-9_a-zA-Z]*#";			//first char cannot be numerical

foreach($fileArray as $currentFile){						//for each file with .h extension
	$file=fopen($currentFile,'r');
	if(!($file)){											//open file failsafe
		fwrite(STDERR,"Cannot open file. \n");			
		exit(ERROR_INPUT);	
	}
	while (($line = fgets($file)) !== false) {
		if((($line[0] != '/') || ($line[1] != '/')) && ($line[0] != '#')){			//remove single line comments and macros
			$text.=$line;
		}
	}
	
	$text=removeUnnecessary($text);							//remove comments,newlines, macros, code
	if($removeWhitespace === true){							//--remove-whitespace formating
		$text=removeWhitespace($text);
	}
	$funcArray = preg_split('/[;@]/', $text);				//split the array of functions by ; and @
	array_pop($funcArray);									//remove string after the last ;
	foreach(array_keys($funcArray) as $func){
		$funcArray[$func]=trim($funcArray[$func]);			//remove spaces in front and behind the prototype
	}	
	foreach($funcArray as $function){
		preg_match("#((extern|inline|static|unsigned|int|void|double|const|float|signed|short|long|\*)\s|\*)+#",$function, $rettype);		//get the return type
		$function=preg_replace("#((extern|inline|static|unsigned|int|void|double|const|float|signed|short|long|\*)\s|\*)+#","",$function);
		$rettype[0]=trim($rettype[0]);						//remove spaces in front and behind the rettype
		$function=trim($function);							//remove spaces in front and behind the function		
		preg_match($functionNamePattern,$function, $name);	//get the name

		$function=preg_replace($functionNamePattern,"",$function,1);
		$name[0]=trim($name[0]);					 		//remove spaces in front and behind the name
		$function=trim($function);							//remove spaces in front and behind the function
		$function=substr($function,1,-1);					//remove argument parentheses
		$args=preg_split('#,#',$function);					//split the arguments by comma,creates an array !!with potentialy empty arguments and void
		if(preg_grep("#\.\.\.#",$args)){					//search for ..., if found, varags is true
			$varargs="yes";
		}else{
			$varargs="no";
		}
		
		$functionElement=$xml->createElement( "function" );	//create function element 
		$functionElement=$xmlFunctions->appendChild($functionElement);	//append the function to the Functions element
		$functionElement->setAttribute("file",$currentFile);			//set currentfile
		$functionElement->setAttribute("name",$name[0]);				//set name
		$functionElement->setAttribute("varargs",$varargs);				//set varargs
		$functionElement->setAttribute("rettype",$rettype[0]);				//set varargs
		
		foreach($args as $arg){
			if(preg_match("#\.\.\.#",$arg) || !preg_match("#[_a-zA-Z][0-9_a-zA-Z]*#",$arg)){		// ... as argument or no word in there
				continue;
			}else{
				
			}
			
		}
		
	}
	
	
	
	
	
	$text="";												//reset the variable
}









$xmlFunctions->appendChild($xml->createTextNode(''));		//create new node for the functions element to be properly closed

$str=$xml->saveXML();
$str=format($str,$prettyXml);								//pretty-xml formating

fwrite($targetFile,$str);


?>