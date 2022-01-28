<?php

namespace app\controllers;

use app\models\apiModel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * реализация методов api 'rates' и 'convert' api/v1
     */
    public function actionApi($method=NULL, $currency=NULL, $currency_from=NULL, $currency_to=NULL, $value=NULL)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!ApiModel::checkToken(substr(Yii::$app->request->headers->get('Authorization'), 7))){
            return self::apiRender(['error'=>'invalid token']);
        }
        if (!get_headers('https://blockchain.info/ticker')[0]=='HTTP/1.1 200 OK'){
            return self::apiRender(['error'=>'blockchain.info is offline']);
        }
        if (!array_key_exists('last', ApiModel::getTicker()['RUB'])){
            return self::apiRender(['error'=>'invalid input data']);
        }
        $input = json_decode(
            file_get_contents('https://blockchain.info/ticker'),
            true
        );
        


        if (Yii::$app->request->isGet&&$method == 'rates') {
            if (!$currency) {
                return self::apiRender(ApiModel::getAllData($input));
            }
            if (!array_key_exists($currency, ApiModel::getAllData($input))){
                return self::apiRender(['error'=>'invalid currency']);
            }
            else {
                return self::apiRender([$currency=>ApiModel::getAllData($input)[$currency]]);
            }
        }
        if (Yii::$app->request->isPost&&$method == 'convert') {
            if ($value<0.01){
                return self::apiRender(['error'=>"invalid value, need min 0.01 $currency_from"]);
            }
            if (is_float($value)){
                return self::apiRender(['error'=>"invalid value"]);
            }
            if (!(array_key_exists($currency_from, ApiModel::getAllData($input))||$currency_from=='BTC')){
                return self::apiRender(['error'=>'invalid currency_from']);
            }
            if (!(array_key_exists($currency_to, ApiModel::getAllData($input))||$currency_to=='BTC')){
                return self::apiRender(['error'=>'invalid currency_to']);
            }
            if ((($currency_from==$currency_to)||($currency_from!=='BTC'&&$currency_to!=="BTC"))){
                return self::apiRender(['error'=>'invalid currency_to or currency_from']);
            }
            if (isset($currency_to)&&isset($currency_from)&&isset($value)){
                return self::apiRender(ApiModel::CurrencyConvert($input, $currency_to, $currency_from, $value));
            }
            else{
                return self::apiRender(['error'=>'bad request']);
            }
        }
        return self::apiRender(['error'=>'bad request']);
    }
    public function apiRender($output)
    {
        if (isset($output['error'])){
            Yii::$app->response->setStatusCode(403);
            return [
                'status'=>'error',
                'code'=>Yii::$app->response->getStatusCode(),
                'message'=>$output['error']
            ];
        }
        return [
                'status'=>'success',
                'code'=>Yii::$app->response->getStatusCode(),
                'data'=>$output
            ];

    }

}
