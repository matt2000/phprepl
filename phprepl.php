<?php
/**
 * @file PHP-REPL. A simple REPL for PHP written in PHP.
 *
 * Inspiried by http://www.php.net/manual/en/features.commandline.interactive.php#98642
 *
 * @author Matt Chapman <Matt@NinjitsuWeb.com>
 *
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 */
error_reporting(E_ALL ^ E_NOTICE);

//Set-up the prompt
$phprepl_prompt = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'php>';

//A command-line argument can be passed identifying a file to include before starting the REPL.
if (isset($argv[2]) && !empty($argv[2])) {
  require($argv[2]);
}

$phprepl_fp = fopen("php://stdin", "r");
$phprepl_in = '';
$phprepl_dont_echo = '/^(global|function|class|echo|print|var_dump|var_export|return)/';
$phprepl_bracket_matches = array( ')' => '(', '}' => '{');
$phprepl_code_blocks = array();

function phprepl_help() {
  echo "
Tips:
- The semi-colon is option for one-line commands.
- You can run a function that doesn't require arguments with just the function name, e.g. 'phpinfo'.
- Tab completion of previously used commands is available if you have rlwrap.
- Just type \$var to get print_r(\$var)
- The last output variable/return is available as \$out. So don't use that variable name yourself in the global scope if you value your sanity.
- Input code is not validated. It's really easy to crash this shell.
- Type CTRL-c to quit.
";
}

echo "Type CTRL-c to quit. Type 'help' if you want it.\n";

$phprepl_multiline = FALSE;
//Main REPL Loop
while(TRUE) {
  echo "$phprepl_prompt ";
  if ($phprepl_multiline) {
    //Indentation
    foreach ($phprepl_code_blocks AS $phprepl_count) {
      $phprepl_incr = 0;
      while ($phprepl_incr++ < $phprepl_count) echo "..";
    }
    echo " ";
    $phprepl_in .= trim(fgets($phprepl_fp));
  } else {
    $phprepl_in=trim(fgets($phprepl_fp));
    
    if (empty($phprepl_in)) continue;
    if ($phprepl_in == 'help') {
      phprepl_help();
      continue;
    }
  }
  
  # for multi-line support @todo doesn't work yet
  
  //regex: ends in { or (

  if (preg_match('/.*(\{|\()$/', $phprepl_in, $matches)) {
      $phprepl_code_blocks[$matches[1]]++;
      $phprepl_multiline = TRUE;
  }
  //regex: ends in } or ) or ); or ),
  if (preg_match('/.*(\}|\)|\);|\),)$/', $phprepl_in, $matches)) {
    //get the matching opening bracket
    //echo "match end bracket: {$matches[1][0]}\n";//debug
    $phprepl_open_bracket = $phprepl_bracket_matches[$matches[1][0]];
    
    $phprepl_code_blocks[$phprepl_open_bracket]--;
    //check to see if we've closed all our blocks
    foreach ($phprepl_code_blocks AS $bracket => $count) {
      if ($count <= 0) unset($phprepl_code_blocks[$bracket]);
    }
    if (empty($phprepl_code_blocks)) $phprepl_multiline = FALSE;
  }
  //print_r($phprepl_code_blocks); //debug

  #run simple functions with no args without ()
  if (!$phprepl_multiline && function_exists($phprepl_in)) {
      $phprepl_in .="();";
  } elseif (!$phprepl_multiline) {
    //@todo We ought to be able to make this work in multiline.
    #let's make the semi-colon optional
    //regex: doesn't end in ;
    if (!preg_match('/.*;$/', $phprepl_in)) $phprepl_in .= ';';
  }

  if (!$phprepl_multiline) {
    //regex: doesn't start with one of the keywords in $phprepl_dont_echo
    $echo = !preg_match($phprepl_dont_echo, $phprepl_in);
  
    if ($echo) $phprepl_in = '$out=' .$phprepl_in;

    //@todo replace with code to parse & validate with is_callable, etc
    $return = eval($phprepl_in);
    
    if ($echo) {
      print_r($out);
    } elseif (!empty($return)) {
        print_r($return);
        $out = $return;
    } elseif ($return !== FALSE) {
        echo "OK.";
    }
    echo "\n";
  }
  
}