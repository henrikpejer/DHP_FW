<?php
declare(encoding = "UTF8");
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2013-07-18 20:11
 */

namespace DHP\modules\propelJson;
use DHP\Request;
use DHP\Response;

/**
 * Class PropelJsonApi
 *
 * An attempt to make a smart API with the aid of Propel.
 *
 * We will try to implement most things from jsonapi.org : it looks good
 * @package DHP\modules
 */
class API
{

    protected $propelNamespace = '';
    protected $dataMap = array();
    protected $dataCommands = array();
    protected $uri;
    private $method;
    /**
     * @var null
     */
    private $bodyData;
    /**
     * @var \DHP\Response
     */
    private $response;
    /**
     * @var \DHP\Request
     */
    private $request;

    /**
     * @param \DHP\Response $response
     * @param \DHP\Request  $request
     * @param array         $dataMap an array containing the data-mapp
     * @param string        $propelNamespace
     */
    public function __construct(Response $response, Request $request, array $dataMap, $propelNamespace = '')
    {
        $this->dataMap         = $dataMap;
        $this->propelNamespace = $propelNamespace;
        $this->uri             = $request->uri;
        $this->method          = $request->method;
        $this->bodyData        = $request->body;
        $this->response        = $response;
        $this->request         = $request;
        $this->parseUri();
        $this->run();
    }

    /**
     * This method parses the uri for models and values
     */
    protected function parseUri()
    {
        $uriParts = explode('/', $this->uri);
        $maxKey   = count($uriParts) - 1;
        for ($i = 0; $i <= $maxKey; $i++) {
            # is this something for a model?
            if (in_array($uriParts[$i], array_keys($this->dataMap))) {
                if ($i == $maxKey) {
                    $this->dataCommands[$uriParts[$i]] = NULL;
                } else {
                    $this->dataCommands[$uriParts[$i]] = $uriParts[++$i];
                }
            }
        }
    }

    public function run()
    {
        $data            = array();
        $previousCommand = NULL;
        $gotResults = false;
        foreach ($this->dataCommands as $command => $value) {
            $value = $value == '-'?NULL:$value;
            if ( !isset($value) && isset($previousCommand) ){
                $value = $data[$previousCommand];
            }
            try{
                $dataApiObject   = new Data($this->propelNamespace . ucfirst($command), $value, $previousCommand);
                $requestBody = $this->request->body;
                if (!empty($requestBody)){
                    $dataApiObject->setData($this->request->body);
                }
                $data[$command]  = $dataApiObject->getData();
                /** @noinspection PhpUndefinedMethodInspection */
                if ($gotResults == false && $data[$command]->count() > 0){
                    $gotResults = true;
                }
                $previousCommand = $command;
            }catch(\Exception $e){
                var_dump($e->getMessage());
            }
        }
        if ( $gotResults == false){
            $this->response->setStatus(404);
        } else {
            $this->response->addHeader('Content-Type','application/json');
            $this->response->setContent($this->formatDataForResponse($data));
        }
    }

    public function returnDataCommands()
    {
        return $this->dataCommands;
    }

    protected function formatDataForResponse($dataToFormat)
    {
        $return = array();
        foreach ($dataToFormat as $model => $realData) {
            $return[$model] = array();
            if (is_a($realData,'PropelModelPager')){
                $tempData = array();
                foreach($realData as $post){
                    /** @noinspection PhpUndefinedMethodInspection */
                    $tempData[] = $post->toArray();
                }
                $realData = $tempData;
            }
            if (is_array($realData)) {
                $tempData = $realData;
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $tempData = $realData->toArray();
            }
            foreach ($tempData as $post) {
                $data = array();
                $primaryKey = null;
                if(!is_array($post)){
                    /** @noinspection PhpUndefinedMethodInspection */
                    $primaryKey = $post->getPrimaryKey();
                    $post = $post->toArray();
                } else {
                    $primaryKey = $post['Id'];
                }
                foreach ($this->dataMap[$model] as $key => $value) {
                    $data[$value] = is_numeric($key) ? $post[$value] : $post[$key];
                }
                $return[$model][$primaryKey] = (object)$data;
            }
        }
        return $return;
    }
}