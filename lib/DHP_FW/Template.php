<?php
declare(encoding = "UTF8") ;
namespace DHP_FW;

/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-02-26 20:27
 */

/**
 * This class is a simple template class
 */
class Template implements TemplateInterface{

    /**
     * Delimiters used in the tamplates
     */
    protected $lDelimiter = '{';
    protected $rDelimiter = '}';

    /**
     * The path to the template file
     * @var null
     */
    protected $file = NULL;

    /**
     * Array, ready for extract, in the 'template'
     * @var array
     */
    protected $templateData = array();

    /**
     * Global data, set via ::globalData()...
     *
     * @var array
     */
    static protected $globalData = array();

    /**
     * The contents of the template
     * @var string
     */
    protected $templateString = '';

    /**
     * Sets up the template object
     */
    public function __construct() {
        // TODO: Implement __construct() method.
    }

    /**
     * This sets the data to be used within this template
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data) {
        // TODO: Implement setData() method.
        $this->templateData = array_merge($this->templateData, $data);
    }

    /**
     * Renders the contents, returns it
     * @param array $templateData Optional template data, used only
     * for this particular template
     * @return mixed
     */
    public function render(array $templateData = array()) {
        $data = array_merge(self::$globalData, $templateData,$this->templateData);

        if(isset($this->file)){
            $this->templateString = file_get_contents($this->file);
        }

        $this->templateString = $this->repeats($this->templateString, $data);
        $this->substituteVariables($data);
        $this->removeOptionalVariables();

        return $this->templateString;
    }

    /**
     * Sets globaly available data
     *
     * @param array $data
     * @return $this
     */
    static public function globalData(array $data) {
        self::$globalData = array_merge(self::$globalData, $data);
    }

    /**
     * This will remove all variables that are optional, ie {?<variableName>}
     */
    protected function removeOptionalVariables() {
        $this->templateString =
          preg_replace("#" . $this->lDelimiter .
                       "(\?[^" . $this->rDelimiter . "]+)" . $this->rDelimiter .
                       "#s",
                       '',
                       $this->templateString);
    }

    /**
     * @param $file path to the file to load
     * @return mixed
     */
    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * @param $string the string that will make up this template
     * @return mixed
     */
    public function setString($string) {
        $this->templateString = $string;
    }

    /**
     * This performs the substitution of variableNames to values in the template
     * @param $data
     */
    private function substituteVariables($data) {
        foreach($data as $variableName => $variableValue){
            switch(gettype($variableValue)){
                case 'object':
                case 'array':
                    break;
                default:
                    $this->templateString = str_replace($this->lDelimiter .
                                                          $variableName .
                                                          $this->rDelimiter,
                                                        $variableValue,
                                                        $this->templateString);
                    $this->templateString = str_replace($this->lDelimiter.'?'.
                                                          $variableName .
                                                          $this->rDelimiter,
                                                        $variableValue,
                                                        $this->templateString);
                    break;
            }
        }
    }

    /**
     * This will take care of repeats :
     * {books}
     *  {title} {author}
     * {/books}
     *
     * @param $string
     * @param $data
     * @return mixed
     */
    private function repeats($string, $data) {
        if (preg_match_all('|{(.+)}(.+){\/\\1}|s', $string, $matches) !== FALSE) {
            foreach ($matches[0] as $key => $match) {
                $template     = $matches[2][$key];
                $variableName = $matches[1][$key];
                # replace the values
                if (isset($data[$variableName])) {
                    $newString = '';
                    foreach($data[$variableName] as $row){
                        $newString .=
                          str_replace($template,
                                      $this->findAndReplace($template, $row),
                                      $template);
                        $newString = $this->repeats($newString, $row);
                    }
                    $string = str_replace($match, $newString, $string);
                }
            }
        }
        return $string;
    }

    /**
     * A find n replace for variables found
     *
     * @param $string
     * @param $variables
     * @return mixed
     */
    private function findAndReplace($string, $variables) {
        if(is_array($variables)){
            foreach ($variables as $variableName => $variableValue) {
                if(!is_array($variableValue)){
                    $string =
                      str_replace($this->lDelimiter . '?' .
                                    $variableName .
                                    $this->rDelimiter,
                                  $variableValue,
                                  $string);
                    $string =
                      str_replace($this->lDelimiter .
                                    $variableName .
                                    $this->rDelimiter,
                                  $variableValue,
                                  $string);
                }
            }
        }
        return $string;
    }
}