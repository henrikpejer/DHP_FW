<?php
declare(encoding = "UTF8");
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2013-07-19 22:13
 */

namespace DHP\modules\propelJson;


class Data
{

    protected $model, $dataId, $data, $columnName;

    /**
     * @param  String $model the model to use for Propel
     * @param null    $dataId
     * @param null    $columnName
     * @internal param null $id
     */
    public function __construct($model, $dataId = NULL, $columnName = NULL)
    {

        $this->model      = $model;
        $this->dataId     = $dataId;
        $this->columnName = $columnName;
        if (isset($this->columnName) && !is_object($this->dataId)){
            unset($this->columnName);
        }
        $this->fetchData();
    }

    /**
     * This should read the data commands array and fetch the data accordingly
     *
     * The fun stuff begins when there are several data commands: we'll se what
     * happens then.
     */
    protected function fetchData()
    {
        switch (TRUE) {
            case !isset($this->dataId): # pagination
            case is_string($this->dataId) && strpos($this->dataId, 'page_') !== FALSE: # what page we should get
                $pageNumber = strpos($this->dataId, 'page_') !== FALSE ? str_replace('page_', '', $this->dataId) : 1;
                $dataObject = $this->getQuery();
                $res        = $dataObject->paginate($pageNumber, 2);
                if ( $pageNumber > 0 && $pageNumber <= $res->getLastPage()){
                    $postPks    = array();
                    foreach ($res as $post) {
                        $postPks[] = $post->getPrimaryKey();
                    }
                    $this->data = $this->getQuery()->findPks($postPks);
                } else {
                    $this->data = $this->getQuery()->findPks(array(0));
                }
                break;
            default:
                if (is_string($this->dataId)) {
                    $dataId = explode(',', $this->dataId);
                } else {
                    $dataId = $this->dataId;
                }
                $dataObject = $this->getQuery();
                if (isset($this->columnName)) {
                    $filterMethod = 'filterBy' . ucfirst($this->columnName);
                    $res          = $dataObject->$filterMethod($dataId)->find();
                } else {
                    $res = is_array($dataId) ? $dataObject->findPks($dataId) : $dataObject->findBySlug($dataId);
                }
                $this->data = $res;
                break;
        }
    }

    /**
     * Returns a query for the model
     * @return
     */
    protected function getQuery()
    {
        $class = $this->getPropelClassForModel();
        return $class::create();
    }

    /**
     * Will try to build the namespace for the propel model
     * and returns either a query or a record.
     *
     * If the class does not exist, an exception is thrown that
     * should be caught by you.
     *
     * @param bool $getRecord
     * @throws \RuntimeException
     * @internal param string $model
     * @return string
     */
    protected function getPropelClassForModel($getRecord = FALSE)
    {
        $class = $this->model;
        if ($getRecord === FALSE) {
            $class .= 'Query';
        }
        if (class_exists($class)) {
            return $class;
        } else {
            throw new \RuntimeException("Model not found");
        }
    }

    # todo : perhaps skipping this smart way and just take every-other

    public function getData()
    {
        return $this->data;
    }

    protected function oldfetchData()
    {
        $data = array();
        if (count($this->dataCommands) == 1 && current($this->dataCommands) === NULL) {
            $dataObject = $this->getQuery(key($this->dataCommands));
            $res        = $dataObject->paginate(1, 10);
            $tempData   = array();
            foreach ($res as $post) {
                $tempData[] = $post->toArray();
            }
            $data[key($this->dataCommands)] = $tempData;
        } else {
            foreach ($this->dataCommands as $command => $id) {
                if (isset($id)) {
                    if (!preg_match("#[^0-9,]+#", $id)) {
                        $id = explode(',', $id);
                    }
                    try {
                        $dataObject     = $this->getQuery($command);
                        $res            = is_array($id) ? $dataObject->findPks($id) : $dataObject->findBySlug($id);
                        $data[$command] = $res;
                    } catch (\Exception $e) {
                        var_dump($e);
                    }
                } else {
                    try {
                        $dataObject = $this->getQuery($command);
                        $filterName = 'filterBy' . ucfirst($lastCommand);
                        $dataObject->$filterName($data[$lastCommand]);
                        $res            = $dataObject->find();
                        $data[$command] = $res;
                    } catch (\Exception $e) {
                        var_dump($e);
                    }
                }
                $lastCommand = $command;
            }
        }
        $this->data = $data;
    }

    /**
     * Returns a propel record object for $model
     * @return
     */
    protected function getRecord()
    {
        $class = $this->getPropelClassForModel(TRUE);
        return new $class();
    }
}