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
    protected $numPerPage = 10;

    /**
     * @param  String $model the model to use for Propel
     * @param null    $dataId
     * @param null    $columnName
     * @internal param null $id
     */
    public function __construct($model, $dataId = null, $columnName = null)
    {

        $this->model      = $model;
        $this->dataId     = $dataId;
        $this->columnName = $columnName;
        if (isset($this->columnName) && !is_object($this->dataId)) {
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
        switch (true) {
            case !isset($this->dataId): # pagination
            case is_string($this->dataId) && strpos($this->dataId, 'page_') !== false: # what page we should get
                $this->fetchPage();
                break;
            default:
                $this->fetchObject();
                break;
        }
    }

    protected function fetchPage()
    {
        $pageNumber = strpos($this->dataId, 'page_') !== false ? str_replace('page_', '', $this->dataId) : 1;
        $dataObject = $this->getQuery();
        $res        = $dataObject->paginate($pageNumber, $this->numPerPage);
        if ($pageNumber > 0 && $pageNumber <= $res->getLastPage()) {
            $postPks = array();
            foreach ($res as $post) {
                $postPks[] = $post->getPrimaryKey();
            }
            $this->data = $this->getQuery()->findPks($postPks);
        } else {
            $this->data = $this->getQuery()->findPks(array(0));
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
    protected function getPropelClassForModel($getRecord = false)
    {
        $class = $this->model;
        if ($getRecord === false) {
            $class .= 'Query';
        }
        if (class_exists($class)) {
            return $class;
        } else {
            throw new \RuntimeException("Model not found");
        }
    }

    protected function fetchObject()
    {
        $numeric = true;
        if (preg_match("#[^0-9,]+#", $this->dataId)) {
            $numeric = false;
        }
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
            if ($numeric) {
                $res = $dataObject->findPks($dataId);
            } else {
                $res = $dataObject->filterBySlug($dataId)->find();
            }
            # $res = is_array($dataId) ? $dataObject->findPks($dataId) : $dataObject->findBySlug($dataId);
        }
        $this->data = $res;
    }

    public function setNumPerPage($numPerPage)
    {
        $this->numPerPage = $numPerPage;
    }

    # todo : perhaps skipping this smart way and just take every-other

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        foreach ($this->data as $post) {
            $post->fromArray($data);
            $post->save();
        }
    }

    /**
     * Returns a propel record object for $model
     * @return
     */
    protected function getRecord()
    {
        $class = $this->getPropelClassForModel(true);
        return new $class();
    }
}