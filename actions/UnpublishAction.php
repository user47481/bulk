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
class UnpublishAction extends Action
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
        $this->_ids = $this->preparePost();
    }

    public function run(){
        foreach ($this->prepareModels()->all() as $model){
            /* @var $model MainModel */
            if($model->hasAttribute('status_id')){
                $model->detachBehaviors();
                $model->updateAttributes(['status_id'=>0]);
            }elseif ($model->hasAttribute('status')){
                $model->detachBehaviors();
                $model->updateAttributes(['status'=>0]);
            }
        }

        return $this->prepareModels()->count();


    }

    private function setIds($value){
        $this->_ids = $value;
    }

    private function prepareModels(){
        $class = $this->controller->getModelClass();
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