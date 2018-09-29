<?php
/****************************************************************************
 * TradeAgent.php
 *     Name: Trade Agent
 *     Date: 2008/12/16
 * @author makoto.warashina@gmail.com
 * @version $Id$:
 * @copyright &copy; 2008-2009 finatech inc.
 ****************************************************************************/
require_once 'Zend/Config/Xml.php';
require_once 'Login.php';
require_once 'Entities.php';
require_once 'ExecEntities.php';
require_once 'OrderEntitiesVisitor.php';
require_once 'T_position.php';
include_once 'SBIConstants.php';
include_once 'ZendConstants.php';
include_once 'CodesConstants.php';

/**
 * Launch TradeAgent: トレードエージェントの起動
 * @author  makoto.warashina@gmail.com
 * @access  public
 */
{
    //自身のインスタンスを作成
    $self = new TradeAgent();

    //XML設定ファイルから読み込み
    $httpConfig = new Zend_Config_Xml('./tradeagent.xml', 'sbibackup');

    //XML設定ファイルから読み込み
    $dbConfig = new Zend_Config_Xml('./tradeagent.xml', 'database');

    //リクエスト解析処理
    $entity = $self->parseRequest();

    //visitorの作成
    $visitor = new OrderEntitiesVisitor( $httpConfig, $dbConfig);

    //ビジネスロジックを遂行
    $entity = $self->orderMainLoop( $entity, $visitor );

    //約定登録処理の呼び出し
    $execVisitor = new ExecutionEntitiesVisitor( $httpConfig, $dbConfig );

    //ビジネスロジックを遂行
    $self->execMainLoop( $entity, $execVisitor);
}

/**
 * TradeAgent: Webページをスクレイピングしながら、発注を行う。
 *             リエントラント（再入可能）なループを持つ。
 * @author  makoto.warashina@gmail.com
 * @access  public
 * @package Agent
 */
class TradeAgent
{
    private $entity = NULL;
    private $visitor = NULL;

    /**
     * parseRequest: リクエスト解析処理 トークンを配列に移送
     * @param: $request: リクエスト
     */
    public function parseRequest()
    {
        $rtnArray = array();
        $rtnArray[CodesConstants::T_POSITION_PID]=//ポジションID: PRIMARY KEY
            $_GET[CodesConstants::T_POSITION_PID];
        $rtnArray[CodesConstants::T_POSITION_CODE]=      //銘柄コード
            $_GET[CodesConstants::T_POSITION_CODE];
        $rtnArray[CodesConstants::T_POSITION_LS]=        //売買区分
            $_GET[CodesConstants::T_POSITION_LS];
        $rtnArray[CodesConstants::T_POSITION_ENTRY_EXIT]=//取引区分
            $_GET[CodesConstants::T_POSITION_ENTRY_EXIT];
        $rtnArray[CodesConstants::T_POSITION_PRICE]=     //価格
            $_GET[CodesConstants::T_POSITION_PRICE];
        $rtnArray[CodesConstants::T_POSITION_QTY]=       //数量
            $_GET[CodesConstants::T_POSITION_QTY];
        return new LoginEntity( $rtnArray );
    }

    /**
     * orderMainLoop: ビジネスロジック遂行処理
     * @param: $entity: トークン配列（解析済みリクエスト）
     *         $visitor: visitorパターンのvisitor役
     */
    public function orderMainLoop( $entity, $visitor )
    {
        // 発注画面が終わり、
        // T_POSITION と T_ORDER の両方に登録するまでループ
        while (!($entity instanceof TExecutionEntity)){

            //再帰的にaccept()関数を呼び出す
            $entity = $entity->accept( $visitor );
        }
        return $entity;
    }

    /**
     * execMainLoop: ビジネスロジック遂行処理
     * @param: $entity: 発注情報
     *         $visitor: visitorパターンのvisitor役
     */
    public function execMainLoop( $paramEntity, $paramVisitor)
    {
        $this->entity = $paramEntity;
        $this->visitor = $paramVisitor;

        //ticks宣言ブロック
        declare(ticks = 1){

            //SIGALRMコールバックを設定
            pcntl_signal(SIGALRM, array(get_class($this),'sig_handler'));

            //SIGALRMシグナルをライズする
            posix_kill(posix_getpid(),SIGALRM);

            //シグナルがライズするまでプロセスは本質的にはsleep状態
            //※子プロセスが動いている
            while (!($this->entity instanceof TerminalEntity));
        }
    }

    /**
     * sig_handler: シグナルハンドラ
     * @param: $signo: シグナル番号
     */
    function sig_handler($signo)
    {
        //TerminalEntityの場合
        if ($this->entity instanceof  TerminalEntity){

            //以降の処理を中断する
            return;
        }

        //SIGALRMの登録
        pcntl_alarm( 10 );

        //プロセスをforkする
        $pid = pcntl_fork();

        //プロセス生成に失敗した場合
        if ($pid == -1){

            //$entity =new ErrorEntity(SBIMessage::ERROR_MES_FATAL_01);
            //$entity->accept( $this->visitor );
            die '-------- ABNORMAL END. --------';

        }//親プロセスの場合
        else if ($pid){

            //子プロセスの終了を待ち合わせる
            pcntl_wait( $status );

        }//子プロセスの場合
        else{

            //再帰的にaccept()関数を呼び出す
            $this->entity = $this->$entity->accept( $this->visitor );
        }
    }
}
?>
