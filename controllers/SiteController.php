<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\UploadForm;
use yii\db\Transaction;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */

    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array(
                'file', 'file',
                'types' => 'csv',
                'maxSize' => 1024 * 1024 * 10, // 10MB
                'tooLarge' => 'The file was larger than 10MB. Please upload a smaller file.',
                'allowEmpty' => false
            ),
        );
    }
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

        if (Yii::$app->user->isGuest) {
            $model = new LoginForm();
            $model->password = '';
            return $this->render('login', [
                'model' => $model,

            ]);
        }






        return $this->render('index', [

            "keywords" => (new \yii\db\Query())->select(['*'])->from('keywords')->all()
        ]);
    }
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


    public function actionUpload()
    {
        $fileName = $_FILES["file"]["tmp_name"];
        if ($_FILES["file"]["size"] > 0) {
            $file = fopen($fileName, "r");
            $index = 0;
            $searchResults = [];
            $fileId = uniqid();

            $apigoogleList = (new \yii\db\Query())
                ->select(['*'])
                ->from('apigoogle')
                ->where(['status' => 'active'])
                ->all();
            $ApiIndex = 0;
            while (($column = fgetcsv($file, 255, ",")) !== FALSE) {
                if ($index != 0) {
                    if ($ApiIndex > sizeof($apigoogleList) - 1) $ApiIndex = 0;
                    $ch = curl_init();
                    $api = urlencode($apigoogleList[$ApiIndex]["api"]);
                    $cx = urlencode($apigoogleList[$ApiIndex]["cx"]);
                    $keyword = urlencode(trim($column[0]));
                    $url = 'https://www.googleapis.com/customsearch/v1?key=' . $api . '&cx=' . $cx . '&q=' .  $keyword;
                    $optArray = array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true
                    );
                    curl_setopt_array($ch, $optArray);
                    $result = curl_exec($ch);
                    $item = json_decode($result, true);
                    array_push(
                        $searchResults,
                        [
                            "keyword" => trim($column[0]),
                            //"totalAdwords"=>, // not found
                            "totalLinks" => substr_count($result, 'http://') + substr_count($result, 'https://'),
                            "totalResults" => $item["searchInformation"]["totalResults"],
                            "searchTime" => $item["searchInformation"]["searchTime"],
                            "lastUpdate" => date('Y-m-d H:i:s')
                        ]
                    );
                    $ApiIndex++;
                }
                $index++;
            }
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                foreach ($searchResults as $key => $value) {
                    //check condition for insert or update
                    if (sizeof((new \yii\db\Query())->select(['keyword'])->from('keywords')->where(['keyword' => $value["keyword"]])->all()) > 0)
                        $connection->createCommand()->update('keywords', $value, ['keyword' => $value["keyword"]])->execute();
                    else
                        $connection->createCommand()->insert('keywords',  $value)->execute();
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render(
            'index',
            [
                "keywords" => (new \yii\db\Query())->select(['*'])->from('keywords')->all()
            ]
        );
    }




    public function actionLogout()
    {
        Yii::$app->user->logout();


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
}
