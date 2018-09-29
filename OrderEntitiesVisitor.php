<?php
/*****************************************************************************
 * OrderEntitiesVisitor.php
 *     Name: Order Entities Visitor
 *     Date: 2008/12/16
 * @author makoto.warashina@gmail.com
 * @version $Id$:
 * @copyright &copy; 2008-2009 finatech inc.
 *****************************************************************************/
require_once 'Entities.php';
require_once 'T_position.php';
require_once 'T_order.php';

/**
 * OrderEntitiesVisitor: 階層構造をもつ実体をトラバースするクラス
 * @author  makoto.warashina@gmail.com
 * @access  public
 * @package Libs
 */
class OrderEntitiesVisitor
{
    private $client    = NULL;/** Zend_Http_Client */
    private $httpConfig= NULL;/** Zend_Xml node */
    private $db        = NULL;/** Zend_Db */
    private $dbConfig  = NULL;/** Zend_Xml node */

    /**
     * コンストラクタ
     * @param: $paramClient: Zend_Http_Client
     */
    public function __construct($paramHttpConfig, $paramDbConfig){
        $this->httpConfig = $paramHttpConfig;
        $this->dbConfig   = $paramDbConfig;
    }

    /**
     * visit: 渡された実体から数珠繋ぎになっている実体を返す
     * @param: $paramEntity: 実体(要素element)
     * @return: entity: 実体(要素element)
     */
    public function visit( $paramEntity )
    {
        if ( $paramEntity instanceof UpdateTPositionEntity){
            return $this->UpdateTPosition( $paramEntity );}
        elseif ( $paramEntity instanceof InsertTOrderEntity){
            return $this->InsertTOrder( $paramEntity );}
        elseif ( $paramEntity instanceof ShinkiKaiExEntity){
            return $this->shinkiKaiEx( $paramEntity );}
        elseif ( $paramEntity instanceof ShinkiKaiConfirmEntity){
            return $this->shinkiKaiConfirm( $paramEntity);}
/*
        elseif ( $paramEntity instanceof ShinkiUriExEntity ){
            return $this->shinkiUriEx( $paramEntity );}
        elseif ( $paramEntity instanceof ShinkiUriConfirmEntity ){
            return $this->shinkiUriConfirm( $paramEntity );}
        elseif ( $paramEntity instanceof HensaiExEntity ){
            return $this->hensaiEx( $paramEntity );}
        elseif ( $paramEntity instanceof HensaiConfirmEntity ){
            return $this->hensaiConfirm( $paramEntity );}
*/
        elseif ( $paramEntity instanceof ShinkiKaiEntryEntity ){
            return $this->shinkiKaiEntry( $paramEntity );}
/*
        elseif ( $paramEntity instanceof ShinkiUriEntryEntity ){
            return $this->shinkiUriEntry( $paramEntity );}
        elseif ( $paramEntity instanceof HensaiEntryEntity ){
            return $this->hensaiEntry( $paramEntity );}
*/
        elseif ( $paramEntity instanceof TPositionEntity ){
            return $this->entityFactory( $paramEntity );}
        elseif ( $paramEntity instanceof LoginEntity ){
            $entity = $this->httpLogin( $paramEntity );
            return new TPositionEntity($entity->getArray());
        } else {
            exit( 0 );
        }
    }

    /**
     * httpLogin: この関数ではログインするのみ
     *            渡された引数をそのまま返す
     * @param: EntityIF: パラメータ
     * @return: TPositionEntity:
     *    GETパラメータから作成したイベントテーブルのフィールド
     */
    public function httpLogin( $paramEntity )
    {
        //Http_ClientがNULLでなく、かつログインが有効の場合
        if ( $this->client != NULL && $this->client->isValidLoggedIn()){

            //引数をそのまま返す
            return $paramEntity;
        }

        //ログインクラス用引数を作成
        $httpConfig = array(
          'client'=>array(
            'adapter'     =>ZendConstants::DEFAULT_ADAPTER,
            'keepalibe'   =>'true',
            'ssltransport'=>'ssl',
            'useragent'   =>ZendConstants::USER_AGENT_IE7),
          'account'=>array(
            'username'    =>$this->httpConfig->account->loginuser,
            'password'    =>$this->httpConfig->account->loginpass));

        //ログインエンティティを作成する
        $loginEntity = new LoginEntity($httpConfig);

        //ログインクラスを作成
        $login = new Login( $loginEntity );

        //ログイン
        $this->client = $login->login();

        //引数の発注時パラメータはそのまま返す
        return $paramEntity;
    }

    //_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
    //_/
    //_/ 新規買い
    //_/

    /**
     * entityFactory: 呼び出し時のパラメータから実体を作成する
     * @param: TPositionEntity: 呼び出し時実体
     * @return: 新規買い / 新規売り / 返済入力実体
     */
    public function entityFactory($paramEntity )
    {
        $paramArray = $paramEntity->getArray();
        $rootArr = array();
        $rtn = NULL;

        //取引区分が新規か返済か判定
        if ($paramArray['entry_exit']==CodesConstants::ORD_ENTEXT_HENSAI){
            $rootArr['HensaiEntryEntity']=$paramArray;
            $rtn = new HensaiEntryEntity( $rootArr );
        }else{
            //売買区分が新規買いか判定
            if ($paramArray['ls']==CodesConstants::ORD_LS_SHINKI_KAI){
                $rootArr['ShinkiKaiEntryEntity']=$paramArray;
                $rtn = new ShinkiKaiEntryEntity( $rootArr );
            }
            //売買区分が新規売りか判定
            elseif ($paramArray['ls']==CodesConstants::ORD_LS_SHINKI_URI){
                $rootArr['ShinkiUriEntryEntity']=$paramArray;
                $rtn = new ShinkiUriEntryEntity( $rootArr );
            }
        }

        //作成した実体を返す
        return $rtn;
    }

    /**
     * shinkiKaiEntry: 新規買いのエントリを行う
     * @param: paramEntity: ShinkiKaiEntryEntity: 新規買い入力実体
     * @return: ShinkiKaiConfirmEntity: 新規買い確認実体
     */
    public function shinkiKaiEntry($paramEntity )
    {
        //実体からコレクションを取得
        $rootArr = $paramEntity->getArray();

        //コレクションから配列を取得
        $paramArray = $rootArr[get_class($paramEntity)];

        //HTML本体を取得
        $body =
            $this->getBody(SBIConstants::SHINKI_KAI_ENTRY.$paramArray['code']);

        //hiddenパラメータ取得
        $hidden = $this->getHiddenAttribution( $body );

        //新規買いパラメータ設定処理の呼出
        $rtnArray = $this->setShinkiKai($hidden, $paramArray);

        //新規買い確認実体名をキーに、新規買い確認実体パラメータを設定
        $rootArr['ShinkiKaiConfirmEntity'] = $rtnArray;

        //新規買い確認実体を作成して返す
        return new ShinkiKaiConfirmEntity( $rootArr );
    }

    /**
     * shinkiKaiConfirm: 新規買いの確認を行う
     * @param: ShinkiKaiConfirmEntity: 新規買い確認実体
     * @return: ShinkiKaiExEntity: 新規買い注文発注実体
     */
    private function shinkiKaiConfirm($paramEntity )
    {
        //実体からコレクションを取得
        $rootArr = $paramEntity->getArray();

        //コレクションから配列を取得
        $paramArray = $rootArr[get_class($paramEntity)];

        //URIを設定
        $this->client->setUri(SBIConstants::SHINKI_KAI_CONFIRM);

        //POSTデータを設定
        $this->client->setParameterPost($paramArray);

        //新規買い確認ページにPOST
        $response=$this->client->request(Zend_Http_Client::POST);

        //HTML本体を取得
        $body = $response->getBody();

        //エラーが発生していた場合、この関数に再入する
        if ( !($this->bypassError( $body, $paramEntity))){

            //修正したパラメータでこの関数に再入する
            return $paramEntity;
        }

        //hiddenパラメータ取得
        $hidden = $this->getHiddenAttribution( $body );

        //新規買いパラメータ設定処理の呼出
        $rtnArray = $this->setShinkiKai($hidden, $paramArray);

        //新規買い注文発注実体名をキーに新規買いパラメータを設定
        $rootArr['ShinkiKaiExEntity'] = $rtnArray;

        //新規買い注文発注実体を作成して返す
        return new ShinkiKaiExEntity($rootArr);
    }

    /**
     * shinkiKaiEx: 新規買い注文の発注
     * @param: paramEntity: ShinkiKaiExEntity: 新規買い注文発注実体
     * @return: InsertTOrderEntity: 注文テーブル実体
     */
    private function shinkiKaiEx( $paramEntity )
    {
        //実体からコレクションを取得
        $rootArr = $paramEntity->getArray();

        //コレクションから配列を取得
        $paramArray = $rootArr[get_class($paramEntity)];

        //URIを設定
        $this->client->setUri(SBIConstants::SHINKI_KAI_EX);

        //POSTデータを設定
        $this->client->setParameterPost($paramArray);

        //新規買い注文発注ページにPOST
        $response=$this->client->request(Zend_Http_Client::POST);

        //HTML本体を取得
        $body = $response->getBody();

        //hiddenパラメータ取得
        $hidden = $this->getHiddenAttribution( $body );

        //注文テーブル登録実体名をキーに注文テーブル登録パラメータを設定
        $rootArr['InsertTOrderEntity'] = $hidden;

        //注文テーブル登録実体を作成して返す
        return new InsertTOrderEntity($rootArr);
    }

    //_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
    //_/
    //_/ 新規売り
    //_/

    //_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
    //_/
    //_/ 返済
    //_/

    //_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
    //_/
    //_/ DB登録
    //_/

    /**
     * insertTOrder: 注文テーブルの登録
     * @param: paramEntity: InsertTOrderEntity: 注文テーブル実体
     * @return: UpdateTPositionEntity: 注文テーブル登録実体
     */
    private function insertTOrder($paramEntity )
    {
        $rootArr = $paramEntity->getArray();
        if (array_key_exists('ShinkiKaiExEntity', $rootArr)){
            $paramArr = $rootArr['ShinkiKaiExEntity'];
        }
        if (array_key_exists('ShinkiUriExEntity', $rootArr)){
            $paramArr = $rootArr['ShinkiUriExEntity'];
        }
        if (array_key_exists('HensaiExEntity', $rootArr)){
            $paramArr = $rootArr['HensaiExEntity'];
        }
        $tposiArr   = $rootArr['TPositionEntity'];
        $rtnOrderNo = $rootArr['InsertTOrderEntity'];

        //登録するデータ
        $persistency = array();
        $persistency['order_brno']='1';                  //2.注文枝番号
        $persistency['gid']=$tposiArr['gid'];            //3.グループＩＤ
        $persistency['code']=$tposiArr['code'];          //4.銘柄コード
        $persistency['ymd']=date('Ymd');                 //5.発注年月日
        $persistency['his']=date('his');                 //6.発注時分秒
        $persistency['condition']=$paramArr['condition'];//7.執行条件
        $persistency['ls']=$tposiArr['ls'];              //8.売買区分
        $persistency['entry_exit']=$tposiArr['entry_exit'];//9.取引区分
        $persistency['price']=$paramArr['price'];        //10.イベント価格
        $persistency['qty']=$paramArr['quantity'];       //11.イベント数量
        $persistency['return_order_no']=$rtnOrderNo['orderNum'];

        //DB接続処理の呼出
        $this->getDb();

        //model の作成
        $t_order = new T_order( get_class($this));

        //populate T_ORDER table
        $t_order->insert( $persistency );

        //最後のシーケンス番号を取得する
        $lastOrderNo = $this->db->lastSequenceId('t_order_order_no_seq');

        //UpdateTPositionArrayの作成
        $rtnArray = array();
        $rtnArray['order_no'] = $lastOrderNo;
        $rtnArray['order_brno'] = 1;
        $rtnArray['order_flg'] = 'true';

        //コレクションに作成した配列を詰める
        $rootArr['UpdateTPositionEntity'] = $rtnArray;

        //ポジション管理テーブル更新実体を作成して返す
        return new UpdateTPositionEntity( $rootArr );
    }

    /**
     * updateTPosition: イベントテーブルの更新
     * @param: paramEntity: UpdateTPositionEntity: イベントテーブル実体
     * @return: ExecEntity: 約定確認実体
     */
    private function updateTPosition($paramEntity )
    {
        $rootArr = $paramEntity->getArray();
        $paramArray = $rootArr[get_class($paramEntity)];

        //DB接続処理の呼出
        $this->getDb();

        //更新するデータ
        $persistency = array();
        $persistency['order_flg'] = 'true';

        //modelの作成
        $t_position = new T_position( get_class($this));

        //PIDを取得
        if ( array_key_exists('ShinkiKaiEntryEntity', $rootArr)){
            $pid = $rootArr['ShinkiKaiEntryEntity']['pid'];
        }elseif ( array_key_exists('ShinkiUriEntryEntity', $rootArr)){
            $pid = $rootArr['ShinkiUriEntryEntity']['pid'];
        }elseif ( array_key_exists('HensaiEntryEntity', $rootArr)){
            $pid = $rootArr['HensaiEntryEntity']['pid'];
        }

        //条件句の設定
        $where = $t_position->getAdapter()->quote('pid = ?', $pid);

        //テーブル更新
        $t_position->update( $persistency, $where );

        //注文テーブル登録実体を作成して返す
        return new TExecutionEntity( $paramEntity );
    }

    //_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
    //_/
    //_/ クラス内部共通関数
    //_/

    /**
     * setShinkiKai: 新規買いパラメータ設定処理
     *
     * @param $hidden: hidden属性INPUTパラメータ連想配列
     *        $tposition: プログラム起動時引数で指定されたパラメータ
     * @return: POST用連想配列
     */
    private function setShinkiKai($hidden, $src)
    {
        $rtnArray = array();
        $rtnArray['regist_id']    = $hidden['regist_id'];
        $rtnArray['brand_cd']     = array_key_exists('code', $src) ?
                                    $src['code'] : $src['brand_cd'];
        $rtnArray['ipm_product_code']=array_key_exists('code', $src) ?
                                    $src['code'] : $src['ipm_product_code'];
        $rtnArray['cayen.isStopOrder']= 'false';
        $rtnArray['quantity']     = array_key_exists('qty', $src) ?
                                    $src['qty'] : $src['quantity'];
        $rtnArray['sasinari_kbn'] = ' ';
        $rtnArray['price']        = $src['price'];
        $rtnArray['caLiKbn']      = 'today';
        $rtnArray['limit']        = '';
        $rtnArray['payment_limit']= '6';
        $rtnArray['tradeRule']    = 'true';
        $rtnArray['password']     = $this->httpConfig->account->tradepass;
        return $rtnArray;
    }

    /**
     * setShinkiUri: 新規売りパラメータ設定処理
     * ※新規買いとの違いは、tradeRuleがないこと
     *
     * @param $hidden: hidden属性INPUTパラメータ連想配列
     *        $tposition: プログラム起動時引数で指定されたパラメータ
     * @return: POST用連想配列
     */
    private function setShinkiUri($hidden, $tposition)
    {
        $rtnArray = array();
        $rtnArray['regist_id']    = $hidden['regist_id'];
        $rtnArray['brand_cd']     = array_key_exists('code', $src) ?
                                    $src['code'] : $src['brand_cd'];
        $rtnArray['ipm_product_code']=array_key_exists('code', $src) ?
                                    $src['code'] : $src['ipm_product_code'];
        $rtnArray['cayen.isStopOrder']= 'false';
        $rtnArray['quantity']     = array_key_exists('qty', $src) ?
                                    $src['qty'] : $src['quantity'];
        $rtnArray['sasinari_kbn'] = ' ';
        $rtnArray['price']        = $tposition['price'];
        $rtnArray['caLiKbn']      = 'today';
        $rtnArray['limit']        = '';
        $rtnArray['payment_limit']= '6';
        $rtnArray['password']     = $this->httpConfig->account->tradepass;
    }

    /**
     * bypassError: エラーを迂回する
     * @param: $body: HTML本体
     *         &$paramEntity: 実体引数参照渡し
     * @return: true: エラーなし
     *          false: エラー発生
     */
    private function bypassError( $body, &$paramEntity ){
        $rtn = false;
        $errMess = $this->getErrorMessage( $body );
//        if ( strcmp($errMess,SBIConstants::ERR_MESS_CRITICAL_01)==0){
            $tmpArr = $paramEntity->getThisClassArray();
            $tmpArr['price'] = $this->getSeigenNehaba($body);
            $paramEntity->setThisClassArray( $tmpArr );
//        }
//        if ( strcmp($errMess,SBIConstants::ERR_MESS_CRITICAL_02)==0){
            $tmpArr = $paramEntity->getThisClassArray();
            $tmpArr['quantity'] = $this->getBaibaiTanni($body);
            $paramEntity->setThisClassArray( $tmpArr );
//        }
//        if ( strcmp($errMess,SBIConstants::ERR_MESS_CRITICAL_03)==0){
            $tmpArr = $paramEntity->getThisClassArray();
            $tmpArr['sasinari_kbn'] = CodesConstants::MUJOUKEN_SASHI;
            $paramEntity->setThisClassArray( $tmpArr );
//        }
        return $rtn;
    }

    private function getSeigenNehaba($body){
        return 320;
    }

    private function getBaibaiTanni($body){
        return 100;
    }

    /**
     * getBody: HTML本体を取得する
     * @param: $url: HTML本体取得対象URL
     * @return: HTML本体
     */
    private function getBody( $url ){
        $this->client->setUri( $url );
        $response = $this->client->request(Zend_Http_Client::GET);
        return $response->getBody();
    }

    /**
     * getDb: DB接続処理
     */
    private function getDb()
    {
        if ($this->db == NULL ){
            $this->db = Zend_Db::factory($this->dbConfig->adapter,
              array(
                'host'     => $this->dbConfig->host,
                'username' => $this->dbConfig->username,
                'password' => $this->dbConfig->password,
                'dbname'   => $this->dbConfig->dbname,));
            Zend_Db_Table_Abstract::setDefaultAdapter($this->db);
        }
    }

    /**
     * getHiddenAttribution: hidden属性のINPUTパラメータを取得する
     * @param: $body: HTML本体
     * @return: hidden連想配列
     */
    private function getHiddenAttribution( $body )
    {
        $rtn = array();

        //正規表現を定義
        $re = '|<input type="hidden" name="([^"]*)" value="([^"]*)">|';

        //HTML本体からhidden属性のINPUTパラメータを抽出
        preg_match_all( $re, $body, $matches);

        //hidden属性のINPUTパラメータを連想配列に詰め替える
        foreach ( $matches[1] as $k => $v){
            $key       = $matches[1][$k];
            $value     = $matches[2][$k];
            $rtn[$key] = $value;
        }

        //連想配列を返す
        return $rtn;
    }

    /**
     * getErrorMessage: エラーメッセージを取得する
     * @param: $body: HTML本体
     * @return: エラーメッセージ
     */
    private function getErrorMessage( $body )
    {
        $rtn = array();

        //正規表現を定義
        $re= '|<font color="#FF0000">(.*)</font><br>|';

        //HTML本体からエラーメッセージを抽出
        preg_match_all($re, $body, $matches);

        //エラーメッセージを配列に詰め替える
        foreach ( $matches[1] as $k => $v) {
            $rtn[] = $matches[1][$k];
        }

        //配列を返す
        return $rtn;
    }
}
?>
