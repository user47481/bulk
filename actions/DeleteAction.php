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
use yii\db\ActiveRecord;
use yii\web\Controller;

class DeleteAction extends Action
{
    public $attribute = 'bulk';

    private $_ids;

    public function __construct($id, Controller $controller, array $config = [])
    {
        parent::__construct($id, $controller, $config);

    }
    public function init()
    {
        parent::init();
        $this->setIds(\Yii::$app->request->post($this->attribute));
    }

    public function run(){
        foreach ($this->prepareModels()->all() as $model){
            /* @var $model MainModel */
            $model->delete();
        }

        return $this->prepareModels()->count();


    }

    private function setIds($value){
        $this->_ids = $value;
    }

    private function prepareModels(){
        $class = $this->controller->getModelClass();
        $searcher = new $class;
        return $searcher::find()->andFilterWhere(['in','id',$this->_ids]);
    }

}