<?php

/**
 * Tested with PHP version 7.0
 * Needs Twig templates support
 *   Running '$ composer require twig/twig' in the application folder should
 *   work correctly.
 *
 *
 * @version    1.1
 */

/*
 * Local variables:
 * $autoload_dir       The directory where the autoload.php file is stored
 * $tmpl_dir           The directory where the templates are stored
 * $config_dir         The directory where the config files are stored
 * $dist_dir           The directory of the distribution files will be stored
 * $array_templates    The array used for store all the configuration filenames
*/

if ( $argc != 1 &&
     ( ($argc > 2 ||
        in_array($argv[1], array('--help', '-help', '-h', '-?'))
       ) ||
       ( $argc == 1 && !in_array($argv[1], array('--htmml')) )
     )
   ) {
?>

This is a command line PHP script with zero or one option.
<?php echo $argc; ?>
  Usage: <?php echo $argv[0]; ?> <option>

    --htmml                    you can generate templates from HTMML template
    --help, -help, -h, or -?   you can get this help

<?php
}
else {

  $htmml = ($argc == 1) ? 0 : 1;

  $autoload_dir = 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
  $tmpl_dir = 'templates';
  $config_dir = 'config';
  $dist_dir = 'dist';

  /*
   * If the vendor/autoload.php file exists
   */
  if(file_exists($autoload_dir)){
      require_once $autoload_dir;

      /*
       * If the templates directory exists
       */
      if(file_exists($tmpl_dir)){
          $array_templates = getDirContents($tmpl_dir,$array_templates);

          /*
           * If the templates directory is empty
           */
          if(empty($array_templates)){
              echo 'The templates directory ('.$tmpl_dir.') is empty.'.PHP_EOL;
              exit();
          }else{
              $loader = new Twig_Loader_Filesystem($tmpl_dir);
              $twig = new Twig_Environment($loader, array('debug' => true));
              foreach($array_templates as $tmpl_file){

                  /*
                   * Checks that for each template file there is a '/config/[something]' directory with THE SAME name
                   * For example:
                   *   If the template file is called 'sample_file', inside the /config directory should be
                   *   a '/sample_file' directory, containing the configuration files life styles or languages,
                   *   like '/config/sample_file/[configuration items]'
                   */
                  if(file_exists($config_dir.DIRECTORY_SEPARATOR.pathinfo($tmpl_file,PATHINFO_FILENAME))){ // Ex: config/versafix-1

                      /*
                       * Arrays where the configuration variables are stored
                       *
                       * $array_lang_files    Stores the path string of the file with the languages and translation variables for each template
                       * $array_vars_files    Stores the path string of the file with the styles variables for each template
                       */
                      $array_lang_files = getConfigFileNames($tmpl_file,'/langs');
                      $array_vars_files = getConfigFileNames($tmpl_file,'/vars');

                      /*
                       * If there are no configuration files for the current template
                       */
                      if(empty($array_lang_files)||empty($array_vars_files)){
                          echo 'There are missing configuration files in '.$config_dir.' directory'.PHP_EOL;
                      }else{
                          if ($htmml) {
                            $lexer = new Twig_Lexer($twig, array(
                                      'tag_comment' => array('[#', '#]'),
                                      'tag_variable' => array('[[', ']]'),
                            ));
                            $twig->setLexer($lexer);
                          }
                          $template = $twig->load(pathinfo($tmpl_file,PATHINFO_BASENAME));
                          foreach ($array_vars_files as $var_file){ // for each file inside /vars

                              /*
                               * If exists the subarray 'langs' and it's not empty in the variables file for the current template
                               */
                              if(getSubArrayLangs($var_file,'langs')){
                                  $array_vars_langs = getSubArrayLangs($var_file,'langs'); //return the found strings
                                  $array_vars_langs = preg_filter('/^/',$config_dir.DIRECTORY_SEPARATOR.pathinfo($tmpl_file,PATHINFO_FILENAME).'/langs/',$array_vars_langs);
                                  $array_vars_langs = preg_filter('/$/','.php',$array_vars_langs); // format the strings in order to get a /config/[template]/langs/[string].php
                              }else{
                                  $array_vars_langs = array_map("formatSubArrayLangs",$array_lang_files); // calls the formatSubArrayLangs() function for each array item
                              }

                              if(!file_exists($dist_dir)){
                                  createDir($dist_dir);
                              }

                              $custom_strs = getSubArrayLangs($var_file,'vars'); // array with the customization strings

                              foreach ($array_vars_langs as $language){ // for each language found in congfiguration files

                                  /*
                                   * If exists the file 'config/[template name]/langs/[language].php'
                                   */
                                  if(file_exists($language)){
                                    $template_prefix = 'template-';
                                    $templateDirName = pathinfo($tmpl_file,PATHINFO_FILENAME);

                                    if (substr($templateDirName, 0, strlen($template_prefix)) == $template_prefix) {
                                        $templateDirName = substr($templateDirName, strlen($template_prefix));
                                    }
                                    $PATH_TO_FILE = $dist_dir . DIRECTORY_SEPARATOR . $templateDirName .'-'. pathinfo($var_file,PATHINFO_FILENAME).'-'.pathinfo($language,PATHINFO_FILENAME);
                                    if(!file_exists($PATH_TO_FILE)){
                                        createDir($PATH_TO_FILE);
                                    }
                                    $file_extension = ($htmml == 1) ? 'htmml' : 'html';
                                    $FINAL_FILENAME = pathinfo($tmpl_file,PATHINFO_FILENAME).'-'.pathinfo($var_file,PATHINFO_FILENAME).'-'.pathinfo($language,PATHINFO_FILENAME). '.' . $file_extension;
                                    $lang_strs = include $language; // array with the language strings

                                    $array_total_variables = array_merge($lang_strs,$custom_strs); // array with both language and customization strings
                                    /*
                                     * Creates the file in '/dist/[template name]/[variable]/[template name]-[language]-[variable].html'
                                     */
                                     $template2string = $template->render($array_total_variables);
                                     $template2 = $twig->createTemplate($template2string);
                                    file_put_contents($PATH_TO_FILE.DIRECTORY_SEPARATOR.$FINAL_FILENAME,$template2->render($lang_strs));
                                  }else{
                                      echo "The configuration file ".$language.' does not exist in the '.DIRECTORY_SEPARATOR.$config_dir.' directory'.PHP_EOL;
                                  }
                              }
                          }
                      }
                  }else{
                      echo 'The configuration directory '.$config_dir.DIRECTORY_SEPARATOR.pathinfo($tmpl_file,PATHINFO_FILENAME).' does not exist. Omitting.'.PHP_EOL;
                  }
              }
          }
      }else{
          echo 'Directory '.$tmpl_dir.' does not exist.'.PHP_EOL;
          exit();
      }
  }else{
      echo 'File '.$autoload_dir.' does not exist. Try running \'composer require twig/twig\' in your project folder.'.PHP_EOL;
  }
}

/**
 * Gets all file names in an array.
 * This recursive function stores in an array all the files in the directory
 * name passed as the first parameter of the function and in it's subdirectories.
 *
 * @param    string $dir the name of the directory where to search
 *                              for files
 * @param    array $results the array where all the filenames will be stored
 * @return   array $results the array where all the filenames are stored
 */
function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
        }
    }
    return $results;
}

/**
 * Creates the directory passed as the first parameter with 777 permissions
 *
 * @param   string $dir the name of the directory to create
 */
function createDir($dir){
    echo 'Creating directory '.$dir.'...'.PHP_EOL;
    mkdir($dir,0777);
}

/**
 * Gets all the filenames inside a /config directory depending on the name
 * passed as the second parameter.
 *
 * @param   string $tmpl_file the name of the current template file
 * @param   string $dir_name the name of the directory to look for
 *
 * @return  array $array the array where all the filenames are stored
 */
function getConfigFileNames($tmpl_file,$dir_name){
    global $config_dir;
    $array = getDirContents($config_dir.DIRECTORY_SEPARATOR.pathinfo($tmpl_file,PATHINFO_FILENAME).$dir_name,$array);
    return $array;
}

/**
 * Gets, if exists, the subarray where the desired languages to translate are stored.
 * Otherwise returns false.
 *
 * @param   string $var_file the name of the configuration file to include
 * @param   string $sel_array the name of the subarray to select from the included file
 * @return  array|bool return the array $array_langs_str if exists, if not, return false
 */
function getSubArrayLangs($var_file,$sel_array){
    $temp_array = array();
    $array_langs_str = array();
    $temp_array = include $var_file;
    $array_langs_str = $temp_array[$sel_array];
    if(!empty($array_langs_str)){
        return $array_langs_str;
    }else{
        return false;
    }
}

/**
 * Formats the string of the path passed as a parameter deleting everything before
 * the substring 'config'.
 *
 * @param   string $lang_file the name of the current language file
 * @return  string $str the original string formated
 */
function formatSubArrayLangs($lang_file){
    $str = substr($lang_file, strrpos($lang_file, 'config'));
    return $str;
}
