<?php
namespace DHP_FW;
/**
 * User: Henrik Pejer mr@henrikpejer.com
 * Date: 2013-02-26 20:33
 */
interface TemplateInterface {
    /**
     * Sets up the template object
     */
    public function __construct();

    /**
     * This sets the data to be used within this template
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * Renders the contents, returns it
     * @param array $templateData Optional template data, used only
     * for this particular template
     * @return mixed
     */
    public function render(array $templateData = array());

    /**
     * @param $file path to the file to load
     * @return mixed
     */
    public function setFile($file);

    /**
     * @param $string the string that will make up this template
     * @return mixed
     */
    public function setString($string);

    /**
     * Sets globaly available data
     *
     * @param array $data
     * @return $this
     */
    static public function globalData(array $data);
}
