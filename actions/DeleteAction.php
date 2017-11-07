<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 13:59
 */

namespace bulk\actions;


use common\components\MainModel;
use yii\base\Action;
use yii\web\Controller;
use Yii;

class DeleteAction extends Action
{
    public $attribute = 'bulk';

    public $modelClass;

    private $_ids;

    public function __construct($id, Controller $controller, array $config = [])
    {

        parent::__construct($id, $controller, $config);

    }
    public function init()
    {
        parent::init();
        $this->_ids = $this->preparePost();
    }

    public function run(){
        foreach ($this->prepareModels()->all() as $model){
            /* @var $model MainModel */
            $model->delete();
        }
        return $this->prepareModels()->count();
    }

    private function prepareModels(){
        if($this->modelClass){
            $class = $this->modelClass;
        }else{
            $class = $this->controller->getModelClass();
        }

        $searcher = new $class;
        return $searcher::find()->filterWhere(['in','id',$this->_ids]);
    }

    private function paramsGuard(){
        if(is_null(\Yii::$app->request->post($this->attribute))){
            throw new \Exception('Айди моделей на удаление не переданы');
        }
    }

    private function preparePost(){
        $this->paramsGuard();
        return Yii::$app->request->post($this->attribute);
    }

}