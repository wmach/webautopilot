<?php
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Db.php';
require_once 'Zend/Db/Adapter/Pdo/Pgsql.php';
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Db/Table/Abstract.php';
require_once 'T_order.php';
require_once 'CodesConstants.php';
include_once 'ZendConstants.php';

//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
//_/
//_/ Entry Point
//_/
//_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
{
    $self = new Test();
    //$self->insertTOrder();
    //$self->perl_regular_expression();
    $self->isEqual();
}

class Test
{
    public function isEqual()
    {
        $a = 'これはＡです。';
        $a2 = 'これはＡです。';
        $b = 'これはＡの比較対象です。';
        if ( strcmp($a,$a2) == 0){
            echo "\$a == \$a2\n";
        }
        $aa = 'this is a';
        $aa2 = 'this is a';
        if ( strcmp($aa, $aa2)==0 ){
            echo "\$aa == \$aa2\n";
        }
    }
    public function perl_regular_expression()
    {
        $ml = new MultiLine();
        $body = $ml->getMultiLine();
        $re = '|<font color="#FF0000">(.*)</font><br>|';
        preg_match_all( $re, $body, $matches);
        foreach ( $matches[1] as $k => $val ) {
            $rtn[] = $matches[1][$k];
        }
print_r( $rtn );
echo "\n------------------------------\n";
print_r( $matches );
    }

    public function insertTOrder()
    {
        $dbConfig   = new Zend_Config_Xml('tradeagent.xml', 'database');
        $persistants['order_brno'] = 1;          //2.注文枝番号
        $persistants['gid']        = '404';      //3.グループＩＤ
        $persistants['code']       = '9448';     //4.銘柄コード
        $persistants['ymd']        = '20090103'; //5.発注年月日
        $persistants['his']        = '232300';   //6.発注時分秒
        $persistants['condition']  = '1';        //7.執行条件
        $persistants['ls']         = 1;          //8.売買区分
        $persistants['entry_exit'] = 1;          //9.取引区分
        $persistants['price']      = 323;        //10.イベント価格
        $persistants['qty']        = 1;          //11.イベント数量
        $sql = <<<__SQL__
INSERT
  INTO T_ORDER (
--       ORDER_NO
     , ORDER_BRNO
     , GID
     , CODE
     , YMD
     , HIS
     , CONDITION
     , LS
     , ENTRY_EXIT
     , PRICE
     , QTY
     , CREATE_DATE
     , CREATOR
     , UPDATE_DATE
     , UPDATER
     )
VALUES (
--       nextval('t_order_order_no_seq')
     , 1
     , 404
     , 9448
     , 20090103
     , 232300
     , 1
     , 1
     , 1
     , 323
     , 1
     , CURRENT_TIMESTAMP
     , 'OrderEntitiesVisitor'
     , CURRENT_TIMESTAMP
     , 'OrderEntitiesVisitor'
     )
;
__SQL__;

        try {
            $db = Zend_Db::factory($dbConfig->adapter, array(
                     'host'     => $dbConfig->host,
                     'username' => $dbConfig->username,
                     'password' => $dbConfig->password,
                     'dbname'   => $dbConfig->dbname,));

            Zend_Db_Table_Abstract::setDefaultAdapter($db);

            $t_order = new T_order( get_class($this)); //model の作成

            $t_order->insert( $persistants ); //populate T_ORDER table

//            $result = $this->db->query( $sql );

            //最後のシーケンス番号を取得する
//            $lastOrderNo = $this->db->lastSequenceId('order_no');

        } catch (Zend_Db_Adapter_Exception $ignore){//DB Error etc.
            print_r( $ignore );
        } catch (Zend_Exception $ignore){//no Adapter etc.
            print_r( $ignore );
        }
    }

    public function selectTPosition()
    {
        $dbConfig   = new Zend_Config_Xml('tradeagent.xml', 'database');

        $countAster          = 'COUNT(*)';
        $cnt                 = 'CNT';
        $nowDate             = 'CURRENT_DATE';
        $asNowDate           = 'curdate';
        $nowTime             = 'CURRENT_TIME';
        $asNowTime           = 'curtime';
        $tableName           = 'T_POSITION';
        $params              = array();
        $params['countAster']= 'COUNT(*)';
        $params['cnt']       = 'CNT';
        $params['nowDate']   = 'CURRENT_DATE';
        $params['asNowDate'] = 'curdate';
        $params['nowTime']   = 'CURRENT_TIME';
        $params['asNowTime'] = 'curtime';
        $params['tableName'] = 'T_POSITION';
        $sql                 =
<<<________SQL________
SELECT $countAster AS $cnt
     , $nowDate    AS $asNowDate
     , $nowTime    AS $asNowTime
  FROM $tableName
________SQL________;

//        $sql2                =
//<<<________SQL2________
//SELECT $params['countAster'] AS $params['cnt']
//     , $params['nowDate']    AS $params['asNowDate']
//     , $params['nowTime']    AS $params['asNowTime']
//  FROM $params['tableName']
//________SQL2________;

        $result = NULL;
        try {
            $db = Zend_Db::factory($dbConfig->adapter, array(
                     'host'     => $dbConfig->host,
                     'username' => $dbConfig->username,
                     'password' => $dbConfig->password,
                     'dbname'   => $dbConfig->dbname,));
            $db->getConnection();
            $db->setFetchMode( Zend_Db::FETCH_ASSOC );
            $result = $db->fetchAssoc( $sql );
        } catch (Zend_Db_Adapter_Exception $ignore){//DB Error etc.
            print_r( $ignore );
        } catch (Zend_Exception $ignore){//no Adapter etc.
            print_r( $ignore );
        }
        print_r( $result );
    }

    public function howtoUseConfig()
    {
        $accountConfig = new Zend_Config_Xml('tradeagent.xml', 'sbibackup');
        $loginuser=$config->sbibackup->account->loginuser;
        echo $loginuser;
        echo $accountConfig->sbibackup->account->loginuser;
        echo $accountConfig->sbibackup->account->loginpass;
        echo $accountConfig->sbibackup->account->tradepass;

        $rtn = createPostData($accountConfig);
        print_r($rtn['client']);
        print_r($rtn['account']);
    }
}

function createPostData($config){
    return array('client'=>array('adapter'=>ZendConstants::DEFAULT_ADAPTER,
                 'keepalibe'=>'true',
                 'ssltransport'=>'ssl',
                 'useragent'=>ZendConstants::USER_AGENT_IE7),
                 'account'=>array('username'=>$config->account->loginuser,
                 'pasword'=>$config->account->loginpass));
}

class T_TEST extends Zend_Db_Table_Abstract
{
    protected $_name = 'T_TEST';
    protected $_sequence = 'TEST_ID';

    public function insert(array $data)
    {
        //タイムスタンプの追加
        if (empty($data['create_date'])) {
            $data['created_date'] = time();
        }
        return parent::insert($data);
    }

    public function update(array $data, $where)
    {
        //タイムスタンプの追加
        if (empty($data['updated_date'])) {
            $data['updated_date'] = time();
        }
        return parent::update($data, $where);
    }
}

class MultiLine
{
    public function getMultiLine(){
        $rtn = <<<___TEXT___


<!--tom31-->














<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<link href="/bsite/css/cayen.css" rel="stylesheet" type="text/css">

<title>注文入力（信用新規買）&nbsp;|&nbsp;SBI証券</title>

<script language="javascript">
function openJmsg(url){
	leftPos = 0;
	if(screen)
	{
		leftPos = screen.width-640
	}
	
	  		w = window.open(url,"jmsg","status=no,toolbar=no,resizable=yes,scrollbars=yes,height=666,width=629,left="+leftPos+",top=0");
	
//  return false;
}
</script>
</head>
<body>
<table border="0" cellspacing="0" cellpadding="0" width="400" align="center">
	<tr>

		<td>
			


<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:7px;">
	<tr valign="top">
		
		<td>
			
				<img src="/bsite/img/header_logo_backupsite.gif" width="211" height="19" border="0" alt="SBI証券　バックアップサイト">
			
		</td>
		<td align="right">
			
			<a href="/bsite/member/menu.do">トップ</a>&nbsp;<b><font color="#999999">│</font></b>&nbsp;<a href="/bsite/member/logout.do">ログアウト</a>

			
		</td>
	</tr>
</table>

			



	<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:10px;">
		<tr align="center"> 
			
					<td bgcolor="#7E7ECC">
						
							<table border="0" cellspacing="1" cellpadding="0" width="100%">
								<tr>
									<td bgcolor="#eceaf9" align="center">

										<a href="/bsite/price/search.do" class="wlink"><div style="margin:2 0 1 0;">取引／株価照会</div></a>
									</td>
								</tr>
							</table>
						
					</td>
					
						<td width="1%"><img src="/bsite/img/trans.gif" width="1" height="1" border="0"></td>
					
			
					<td bgcolor="#eeeeee">
						
							<a href="/bsite/member/portfolio/registeredStockList.do" class="wlink"><div style="margin:2 0 1 0;">登録銘柄</div></a>

						
					</td>
					
						<td width="1%"><img src="/bsite/img/trans.gif" width="1" height="1" border="0"></td>
					
			
					<td bgcolor="#eeeeee">
						
							<a href="/bsite/member/acc/menu.do" class="wlink"><div style="margin:2 0 1 0;">口座管理</div></a>
						
					</td>
					
						<td width="1%"><img src="/bsite/img/trans.gif" width="1" height="1" border="0"></td>
					
			
					<td bgcolor="#eeeeee">
						
							<a href="/bsite/market/menu.do" class="wlink"><div style="margin:2 0 1 0;">マーケット情報</div></a>

						
					</td>
					
			
		</tr>
	</table>


			<div class="titletext">注文入力（信用新規買）</div>
			<br>
			<table width="100%" cellspacing="0" cellpadding="0">
				<tr>

					<td>
						建余力&nbsp;：&nbsp;924,048<br>
						維持率&nbsp;：&nbsp;0.00%<br>
						<br>
						
						
						<a href="/bsite/info/tradeAttentionDetail.do?ipm_product_code=9448">!取引注意情報あり</a><br>

						<br>
						<font color="#FF0000">取引パスワードが違います</font><br>
						
						<form action="/bsite/member/margin/buyOrderEntryConfirm.do" method="POST">
							<input type="hidden" name="regist_id" value="1231315449185">
							<input type="hidden" name="brand_cd" value="9448">
							<input type="hidden" name="ipm_product_code" value="9448">
							<input type="hidden" name="cayen.isStopOrder" value="false">
							<table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:10px;">

								<tr>
									<td><b>9448&nbsp;インボイス</b></td>
								</tr>
								<tr>
									<td align="right">東証*&nbsp;&nbsp;<input type="submit" name="update" value="更新"></td>
								</tr>
							</table>

							<font class="status01">信用</font>&nbsp;<font class="status02">一般</font><br>
							現在値&nbsp;：&nbsp;<font class="ltext">401</font>&nbsp;C&nbsp;（01/07&nbsp;15:00）<br>
							<table cellspacing="0" cellpadding="2" border="0" width="100%" style="margin-top:10px;">
								
								<tr bgcolor="#E7E4F8">
									<td width="20%" nowrap>取引&nbsp;：&nbsp;</td>

									<td width="80%">信用新規買</td>
								</tr>
								<tr bgcolor="#f3f3f3">
									<td nowrap>株数&nbsp;：&nbsp;</td>
									<td>
										<input type="text" size="10" name="quantity" value="1" maxlength="8" istyle="4">&nbsp;株&nbsp;（1株単位）
									</td>

								</tr>
								<tr bgcolor="#E7E4F8">
									
									
										<td nowrap>価格&nbsp;：&nbsp;</td>
										<td>
											<select name="sasinari_kbn">
<option value="DEFAULT_SELECT_VALUE" >選択して下さい</option>
<option value=" " selected>指値:無条件</option>
<option value="Z" >指値:寄指</option>

<option value="I" >指値:引指</option>
<option value="F" >指値:不成</option>
<option value="N" >成行:無条件</option>
<option value="Y" >成行:寄成</option>
<option value="H" >成行:引成</option>
</select><br>
											<input type="text" size="10" name="price" value="321" maxlength="10" istyle="4">&nbsp;円&nbsp;（321～481円）<br>
											
												<div align="right"><a href="/bsite/member/margin/buyOrderEntry.do?ipm_product_code=9448&cayen.isStopOrder=true">逆指値注文はこちら</a></div>

											
										</td>
									
									
								</tr>
								<tr bgcolor="#f3f3f3">
									<td nowrap>期間&nbsp;：&nbsp;</td>
									<td>
										<input type="radio" name="caLiKbn" value="today" checked>&nbsp;当日中&nbsp;<input type="radio" name="caLiKbn" value="limit" >&nbsp;期間指定&nbsp;<select name="limit">
<option value="20090109" selected>2009/01/09</option>

<option value="20090113" >2009/01/13</option>
<option value="20090114" >2009/01/14</option>
<option value="20090115" >2009/01/15</option>
<option value="20090116" >2009/01/16</option>
<option value="20090119" >2009/01/19</option>
</select>

									</td>
								</tr>
								<tr bgcolor="#E7E4F8">

									<td nowrap>預り区分&nbsp;：&nbsp;</td>
									<td>一般預り</td>
								</tr>
								
									<tr bgcolor="#f3f3f3">
										<td nowrap>制度/一般信用&nbsp;：&nbsp;</td>
										<td><select name="payment_limit">

<option value="6" selected>制度信用(6ヶ月)</option>
<option value="9" >一般信用(無期限)</option>
</select>
</td>
									</tr>
									<tr bgcolor="#f3f3f3">
										<td>&nbsp;</td>
										<td><input type="checkbox" name="tradeRule" value="true"><a href="/bsite/info/attentionDetail.do?attention_id=margin">一般信用取引ルール</a>に同意する</td>
									</tr>

								
							</table>
							<div align="center" style="margin-top:5px;">
								<a href="/bsite/info/policyList.do?list=attention">お取引注意事項</a><br>
							</div>
							<br>
							
							<div align="left">
								
								
									<font style="background-color:#f0f0f0;padding:3;">
										取引パスワード&nbsp;：&nbsp;<input type="password" size="14" name="password" maxlength="30" istyle="3">

									</font>
									&nbsp;&nbsp;
								
								
								
									<input type="submit" value="確認">
								
							</div>
						</form>
					</td>
				</tr>
			</table>
			<br>

			



	<div align="right">
		
		<a href="/bsite/member/menu.do">トップ</a>&nbsp;<b><font color="#999999">│</font></b>&nbsp;<a href="/bsite/member/logout.do">ログアウト</a>
		
	</div>


<div style="font-size: 90%;text-align: center;margin:5 0 0 0;padding:5 0 5 0;border-top: 1px dotted #999999;"><a href="/bsite/info/policyDetail.do?list=attention&policy_info_id=salesLaw&text_no=1">金融商品取引法に係る表示</a></div>
<div style="font-size: 90%;color:#666666;text-align: center;" class="mtext-gray">&copy; SBI SECURITIES Co., Ltd. All Rights Reserved.</div>

		</td>
	</tr>
</table>
</body>
</html>

___TEXT___;
        return $rtn;
    }
}
?>
