<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 13:59
 */

namespace bulk\actions;

use common\components\MainModel;
use notifier\helpers\HistoryHelper;
use notifier\helpers\SendHelper;
use notifier\models\db\NotifierTemplates;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\web\Controller;
use Yii;
/**
 * Class NotifierAction
 * @package bulk\actions
 */
class NotifierAction extends Action
{
    /**
     * @var string
     */
    public $attribute = 'bulk';

    /**
     * @var $_ids
     */
    private $_ids;

    /**
     * @var $_templateID
     */
    private $_templateID;

    /**
     * @var array
     */
    public $callbacks = [];

    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->_templateID = $_GET['template'];
        $this->_ids = $this->preparePost();
    }

    /**
     * @return mixed
     */
    public function run(){
        foreach ($this->prepareModels()->all() as $model){
            //if($model->is_sms_send !== 1){
                /* @var $model MainModel */
                $service = new SendHelper(new HistoryHelper(), $this->loadTemplate($model,$model->language));
                $sms = $service->send($model);
                if($sms->code == '100'){
                    $model->detachBehaviors();
                    $model->updateAttributes(['is_sms_send'=>1]);
                }else{
                    return s($sms->code,$sms);
                    exit;
                }
            //}
        }
    }




    /**
     * @param $model MainModel
     * @param $lang string
     * @return static
     */
    public function loadTemplate($model, $lang){

        $template = NotifierTemplates::find()->localized($lang)->where(['id'=>$this->_templateID])->one();

        if($template->label == 'Форма посетителя'){
            $template->message = str_replace( '{{$ticket_url}}', $model->pdfPath, $template->message );
        }

        return $template;

    }

    /**
     * @return mixed
     */
    private function prepareModels(){
        $class = $this->controller->getModelClass();
        $searcher = new $class;
        return $searcher::find()->andFilterWhere(['in','id',$this->_ids]);
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