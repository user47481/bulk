<?php
/**
 * Created by PhpStorm.
 * User: Mopkau
 * Date: 12.10.2017
 * Time: 13:59
 */

namespace bulk\actions;


use common\components\MainModel;
use common\models\PeopleForms;
use kartik\mpdf\Pdf;
use yii\base\Action;
use yii\db\ActiveRecord;
use yii\web\Controller;

class SendTicketAction extends Action
{
    public $attribute = 'bulk';

    private $_ids;

    public function init()
    {
        parent::init();
        $this->setIds(\Yii::$app->request->post($this->attribute));
    }

    public function run(){
       foreach ($this->prepareModels()->all() as $model){

           if($model->is_ticket_send === 0){
               $send = \Yii::$app->mailer->compose('@backend/views/emails/ticket',['model'=>$model])
                   ->setFrom('no-reply@mhe.su')
                   ->setTo($model->email)
                   ->setSubject(\Yii::t('membership','Билет участника'))
                   ->attach($this->generateTicket($model->id))
               ;
               if($send->send()){
                   /* @var $model PeopleForms */
                   $model->detachBehaviors();
                   $model->is_ticket_send = 1;
                   $model->update();
               }
           }

        }
        return $this->prepareModels()->count();
    }

    public function generateTicket($id){
        $class = $this->controller->getModelClass();
        $model = $class::find()->where(['id'=>$id])->one();
        // get your HTML raw content without any layouts or scripts
        $content = $this->controller->renderPartial('@backend/views/pdf/ticket',['model'=>$model]);
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => '',
            // A4 paper format
            'format' => Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            //'cssFile' => 'css/style-pdf.css',
            // any css to be embedded if required
            // set mPDF properties on the fly
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader'=>[''],
                'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        // return the pdf output as per the destination setting
        $pdf->output($content,\Yii::getAlias('@backend').'/web/tickets/ticket_'.$model->id.'.pdf','F');

        return \Yii::getAlias('@backend').'/web/tickets/ticket_'.$model->id.'.pdf';
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