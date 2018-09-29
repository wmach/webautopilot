<?php
/*****************************************************************************
 * Login.php
 *     Name: Login
 *     Date: 2008/12/16
 * @author makoto.warashina@gmail.com
 * @version $Id$:
 * @copyright &copy; 2008 finatech inc.
 *****************************************************************************/
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Socket.php';
require_once 'Zend/Http/Client/Adapter/Exception.php';
include_once 'SBIConstants.php';
include_once 'ZendConstants.php';

/**
 * Login: ログインするクラス
 * @author  makoto.warashina@gmail.com
 * @access  public
 * @package Libs
 */
class Login
{
    /**
     * 変数宣言
     */
    private $loginEntity = NULL;

    /**
     * __construct: コンストラクタ
     * @param: $paramConfig: Zend_Http_Clientに設定するパラメータ
     * @param: $paramPostData: ログイン情報
     */
    public function __construct( $paramLoginEntity )
    {
        $this->loginEntity = $paramLoginEntity;
    }

    /**
     * login: ログイン処理
     */
    public function login()
    {
        //クライアントを作成
        $client = new Zend_Http_Client(
            SBIConstants::LOGIN_CHECK,
            $this->loginEntity->getValue('client'));

        //クッキージャーを設定する
        $client->setCookieJar();

        //URLを設定
        $client->setUri(SBIConstants::LOGIN_CHECK);

        //ログイン情報を設定
        $client->setParameterPost($this->loginEntity->getValue('account'));

        //ログインページにPOST
        $response=$client->request(Zend_Http_Client::POST);

        //クッキーをセッションに退避
        $this->saveCookieToSession($client);

        //パラメータをリセット
        $client->resetParameters();

        //ログイン済のZend_Http_Clientを返す
        return $client;
    }

    /**
     * isLoggedIn: ログイン判定処理
     * @return: true: ログイン済, false: ログイン未済
     */
    public function isLoggedIn()
    {
        //戻り値を設定
        $rtn = False;

        //セッション変数にクッキーが保存されているか判定
        if (isset($_SESSION['cookiejar']) &&
            $_SESSION['cookiejar'] instanceof Zend_Http_CookieJar){

            $rtn = True;
        }

        //戻り値を返す
        return $rtn;
    }

    /**
     * isValidLogin: ログイン検査処理
     * @return: true: ログイン有効, false: ログイン無効
     */
    public function isValidLoggedIn( $client )
    {
        //ログインしていない場合は、falseを返す
        if ( !isLoggedIn()){ return false; }

        //ログインが有効かページに問い合わせる
        $body = OrderEntitiesVisitor::getBody(SBIConstants::TOP, $client);

        preg_match_all(
            '@<title>@セッションタイムアウト.*@</title>@',
            $body, $matches);

        $rtnArray['title']= $matches[2][0];

        return $rtn;
    }

    /**
     * setPostData: ログイン情報設定処理
     * @param: $paramPostData: ログイン情報
     */
    public function setPostData($paramPostData)
    {
        $this->postData = $paramPostData;
    }

    /**
     * setConfig: クライアント情報設定処理
     * @param: $paramConfig: クライアント情報
     */
    public function setConfig($paramConfig)
    {
        $this->config = $paramConfig;
    }

    /**
     * saveCookieToSession: クッキー退避処理
     * @param: $client: Zend_Http_Client
     */
    public function saveCookieToSession($client)
    {
        //セッション変数にクッキーを設定
        $_SESSION['cookiejar'] = $client->getCookieJar();
    }

    /**
     * restoreCookieFromSession: クッキー復元処理
     * @param: $client: Zend_Http_Client
     */
    public function restoreCookieFromSession($client)
    {
        //セッション変数に保存されているクッキーをクライアントに設定
        $client->setCookieJar($_SESSION['cookiejar']);
    }
}
?>
